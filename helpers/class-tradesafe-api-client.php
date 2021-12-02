<?php

namespace TradeSafe\Helpers;


use GraphQL\Client;
use GraphQL\Mutation;
use GraphQL\Query;
use GraphQL\RawObject;
use GraphQL\Variable;

class TradeSafeApiClient
{
    private string $clientId;
    private string $clientSecret;
    private string $clientRedirectUri;

    private string $apiDomain;
    private string $authDomain;

    private string $token;

    public function __construct()
    {
        // Domains are in a separate file to help with internal development and testing.
        require __DIR__ . '/../config.php';

        $settings = get_option('woocommerce_tradesafe_settings', []);

        $this->clientId = $settings['client_id'];
        $this->clientSecret = $settings['client_secret'];
        $this->clientRedirectUri = site_url('/tradesafe/oauth/callback/');

        $this->authDomain = $auth_domain;
        $this->apiDomain = $api_domains['sit'];

        $this->token = '';
        $this->generateToken();

        $authorizationHeaders = [
            'Authorization' => 'Bearer ' . $this->token
        ];

        $httpOptions = [];
        if (true === WP_DEBUG) {
            $httpOptions['verify'] = false;
        }

        $this->client = new Client(
            sprintf('https://%s/graphql', $this->apiDomain),
            $authorizationHeaders,
            $httpOptions,
        );

    }

    public function ping()
    {
        $api_status = false;
        $api_status_reason = null;

        $auth_status = false;
        $auth_status_reason = null;

        try {
            $client = new \GuzzleHttp\Client(
                array(
                    'base_uri' => sprintf('https://%s/', $this->apiDomain),
                    'timeout' => 2.0,
                )
            );

            $response = $client->request('GET', 'api/ping');

            if ($response->getStatusCode() === 200 && $response->getBody()->getContents() === 'pong') {
                $api_status = true;
            } else {
                $auth_status_reason = sprintf('[%s]: %s', $response->getStatusCode(), $response->getBody()->getContents());
            }
        } catch (\Exception $e) {
            $api_status_reason = $e->getMessage();
        }

        try {
            $client = new \GuzzleHttp\Client(
                array(
                    'base_uri' => sprintf('https://%s/', $this->authDomain),
                    'timeout' => 2.0,
                )
            );

            $response = $client->request('GET', 'ping');

            if ($response->getStatusCode() === 200 && $response->getBody()->getContents() === 'pong') {
                $auth_status = true;
            } else {
                $auth_status_reason = sprintf('[%s]: %s', $response->getStatusCode(), $response->getBody()->getContents());
            }
        } catch (\Exception $e) {
            $auth_status_reason = $e->getMessage();
        }

        return [
            'api' => [
                'domain' => $this->apiDomain,
                'status' => $api_status,
                'reason' => $api_status_reason,
            ],
            'auth' => [
                'domain' => $this->authDomain,
                'status' => $auth_status,
                'reason' => $auth_status_reason,
            ],
        ];
    }

    public function production()
    {
        $info = $this->clientInfo();

        return $info['production'];
    }

    public function generateToken()
    {
        if (get_transient('tradesafe_client_token')) {
            $this->token = get_transient('tradesafe_client_token');
            return;
        }

        try {
            $provider = new \League\OAuth2\Client\Provider\GenericProvider([
                'clientId' => $this->clientId,
                'clientSecret' => $this->clientSecret,
                'redirectUri' => $this->clientRedirectUri,
                'urlAuthorize' => 'https://' . $this->authDomain . '/oauth/authorize',
                'urlAccessToken' => 'https://' . $this->authDomain . '/oauth/token',
                'urlResourceOwnerDetails' => 'https://' . $this->authDomain . '/oauth/resource',
            ]);

            $access_token = $provider->getAccessToken('client_credentials');

            $this->token = $access_token->getToken();

            $expires = $access_token->getExpires() - time() - 30;
            set_transient('tradesafe_client_token', $this->token, $expires);
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
        }
    }

    public function getEnum(string $name)
    {
        $cache_name = 'tradesafe_enum_' . $name;

        if (get_transient($cache_name)) {
            return get_transient($cache_name);
        }

        $gql = new Query('__type(name: "' . $name . '")');

        $gql->setSelectionSet([
            (new Query('enumValues'))
                ->setSelectionSet([
                    'name',
                    'description'
                ])
        ]);

        $response = $this->client->runQuery($gql, true);
        $data = $response->getData();

        $options = [];
        foreach ($data['__type']['enumValues'] as $enum) {
            $options[$enum['name']] = $enum['description'];
        }

        set_transient($cache_name, $options, 24 * 60 * 60);

        return $options;
    }

    public function profile()
    {
        try {
            $gql = (new Query('apiProfile'));

            $gql->setSelectionSet([
                'token'
            ]);

            $response = $this->client->runQuery($gql, true);
            $result = $response->getData();

            return $this->getToken($result['apiProfile']['token']);
        } catch (\Exception $e) {
            return [
                'error' => $e->getMessage()
            ];
        }
    }

    public function clientInfo()
    {
        if (get_transient('tradesafe_client_info')) {
            return get_transient('tradesafe_client_info');
        }

        $gql = (new Query('clientInfo'));

        $gql->setSelectionSet([
            'id',
            'name',
            'callback',
            'organizationId',
            'production',
        ]);

        $response = $this->client->runQuery($gql, true);
        $result = $response->getData();

        set_transient('tradesafe_client_info', $result['clientInfo'], 600);

        return $result['clientInfo'];
    }

    public function getToken($id)
    {
        $gql = (new Query('token'));

        $gql->setArguments(['id' => $id]);

        $gql->setSelectionSet([
            'id',
            'name',
            'reference',
            (new Query('user'))
                ->setSelectionSet([
                    'givenName',
                    'familyName',
                    'email',
                    'mobile',
                    'idNumber'
                ]),
            (new Query('organization'))
                ->setSelectionSet([
                    'name',
                    'tradeName',
                    'type',
                    'registration',
                    'taxNumber'
                ]),
            (new Query('bankAccount'))
                ->setSelectionSet([
                    'accountNumber',
                    'accountType',
                    'bank',
                    'branchCode',
                    'bankName'
                ]),
        ]);

        $response = $this->client->runQuery($gql, true);
        $result = $response->getData();

        return $result['token'];
    }

    public function createToken($user, $organization = null, $bankAccount = null)
    {
        $gql = (new Mutation('tokenCreate'));

        if (isset($user['idNumber'])) {
            $input = sprintf('user: {
                givenName: "%s"
                familyName: "%s"
                email: "%s"
                mobile: "%s"
                idNumber: "%s"
                idType: %s
                idCountry: %s
            }', $user['givenName'], $user['familyName'], $user['email'], $user['mobile'], $user['idNumber'], $user['idType'], $user['idCountry']);
        } else {
            $input = sprintf('user: {
                givenName: "%s"
                familyName: "%s"
                email: "%s"
                mobile: "%s"
            }', $user['givenName'], $user['familyName'], $user['email'], $user['mobile']);
        }

        if (isset($organization)) {
            $input .= sprintf('organization: {
                name: "%s"
                tradeName: "%s"
                type: %s
                registrationNumber: "%s"
                taxNumber: "%s"
            }', $organization['name'], $organization['tradeName'], $organization['type'], $organization['registrationNumber'], $organization['taxNumber']);
        }

        if (isset($bankAccount)) {
            $input .= sprintf('bankAccount: {
                bank: %s
                accountNumber: "%s"
                accountType: %s
            }', $bankAccount['bank'], $bankAccount['accountNumber'], $bankAccount['accountType']);
        }

        $input = '{' . $input . '}';

        $gql->setArguments(['input' => new RawObject($input)]);

        $gql->setSelectionSet([
            'id',
            'name',
            'reference',
            (new Query('user'))
                ->setSelectionSet([
                    'givenName',
                    'familyName',
                    'email',
                    'mobile',
                    'idNumber'
                ]),
            (new Query('organization'))
                ->setSelectionSet([
                    'name',
                    'tradeName',
                    'type',
                    'registration',
                    'taxNumber'
                ]),
            (new Query('bankAccount'))
                ->setSelectionSet([
                    'accountNumber',
                    'accountType',
                    'bank',
                    'branchCode',
                    'bankName'
                ]),
        ]);

        $response = $this->client->runQuery($gql, true);
        $result = $response->getData();

        return $result['tokenCreate'];
    }

    public function updateToken($tokenId, $user, $organization = null, $bankAccount = null)
    {
        $gql = (new Mutation('tokenUpdate'));

        $input = sprintf('user: {
            givenName: "%s"
            familyName: "%s"
            email: "%s"
            mobile: "%s"
            idNumber: "%s"
            idType: %s
            idCountry: %s
        }', $user['givenName'], $user['familyName'], $user['email'], $user['mobile'], $user['idNumber'], $user['idType'], $user['idCountry']);

        if (isset($organization)) {
            $input .= sprintf('organization: {
                name: "%s"
                tradeName: "%s"
                type: %s
                registrationNumber: "%s"
                taxNumber: "%s"
            }', $organization['name'], $organization['tradeName'], $organization['type'], $organization['registrationNumber'], $organization['taxNumber']);
        }

        if (isset($bankAccount)) {
            $input .= sprintf('bankAccount: {
                bank: %s
                accountNumber: "%s"
                accountType: %s
            }', $bankAccount['bank'], $bankAccount['accountNumber'], $bankAccount['accountType']);
        }

        $input = '{' . $input . '}';

        $gql->setArguments(['id' => $tokenId, 'input' => new RawObject($input)]);

        $gql->setSelectionSet([
            'id',
            'name',
            'reference',
            (new Query('user'))
                ->setSelectionSet([
                    'givenName',
                    'familyName',
                    'email',
                    'mobile',
                    'idNumber'
                ]),
            (new Query('organization'))
                ->setSelectionSet([
                    'name',
                    'tradeName',
                    'type',
                    'registration',
                    'taxNumber'
                ]),
            (new Query('bankAccount'))
                ->setSelectionSet([
                    'accountNumber',
                    'accountType',
                    'bank',
                    'branchCode',
                    'bankName'
                ]),
        ]);

        $response = $this->client->runQuery($gql, true);
        $result = $response->getData();

        return $result['tokenUpdate'];
    }

    public function getTransaction($id)
    {
        $gql = (new Query('transaction'));

        $gql->setArguments(['id' => $id]);

        $gql->setSelectionSet([
            'id',
            (new Query('allocations'))
                ->setSelectionSet([
                    'id'
                ]),
        ]);

        $response = $this->client->runQuery($gql, true);
        $result = $response->getData();

        return $result['transaction'];
    }

    public function createTransaction($transactionData, $allocationData, $partyData)
    {
        $gql = (new Mutation('transactionCreate'));

        $allocationInput = '';
        $partyInput = '';

        foreach ($allocationData as $allocation) {
            if (isset($allocation['units'])
                && isset($allocation['unitCost'])) {
                $allocationInput .= sprintf('{
                    title: "%s",
                    description: "%s",
                    units: %s,
                    unitCost: %s,
                    daysToDeliver: %s,
                    daysToInspect: %s
                }', $allocation['title'],
                    $allocation['description'],
                    $allocation['units'] ?? 0,
                    $allocation['unitCost'] ?? 0,
                    $allocation['daysToDeliver'] ?? 14,
                    $allocation['daysToInspect'] ?? 7,
                );
            } else {
                $allocationInput .= sprintf('{
                    title: "%s",
                    description: "%s",
                    value: %s,
                    daysToDeliver: %s,
                    daysToInspect: %s
                }', $allocation['title'],
                    $allocation['description'],
                    $allocation['value'] ?? 0,
                    $allocation['daysToDeliver'] ?? 14,
                    $allocation['daysToInspect'] ?? 7,
                );
            }
        }

        foreach ($partyData as $party) {
            $partyInput .= sprintf('{
                token: "%s",
                email: "%s",
                role: %s,
                fee: %s,
                feeType: %s,
                feeAllocation: %s
            }', $party['token'] ?? null,
                $party['email'] ?? null,
                $party['role'] ?? null,
                $party['fee'] ?? 0,
                $party['feeType'] ?? 'PERCENT',
                $party['feeAllocation'] ?? 'SELLER',
            );
        }

        $input = sprintf('{
            title: "%s",
            description: "%s",
            industry: %s,
            currency: ZAR,
            feeAllocation: %s,
            workflow: %s,
            reference: "%s",
            privacy: %s,
            allocations: {
                create: [
                    %s
                ]
            },
            parties: {
                create: [
                    %s
                ]
            }
        }', $transactionData['title'],
            $transactionData['description'],
            $transactionData['industry'] ?? 'GENERAL_GOODS_SERVICES',
            $transactionData['feeAllocation'] ?? 'SELLER',
            $transactionData['workflow'] ?? 'STANDARD',
            $transactionData['reference'] ?? '',
            $transactionData['privacy'] ?? 'NONE',
            $allocationInput,
            $partyInput,
        );

        $gql->setArguments(['input' => new RawObject($input)]);

        $gql->setSelectionSet([
            'id',
            'title',
            'description',
            'state',
            'industry',
            'feeAllocation',
            (new Query('parties'))
                ->setSelectionSet([
                    'id',
                    'name',
                    'role',
                    (new Query('details'))
                        ->setSelectionSet([
                            (new Query('user'))
                                ->setSelectionSet([
                                    'givenName',
                                    'familyName',
                                    'email',
                                ]),
                            (new Query('organization'))
                                ->setSelectionSet([
                                    'name',
                                    'tradeName',
                                    'type',
                                    'registration',
                                    'taxNumber',
                                ]),
                        ]),
                    (new Query('calculation'))
                        ->setSelectionSet([
                            'payout',
                            'totalFee',
                        ]),
                    'fee',
                    'feeType',
                    'feeAllocation'
                ]),
            (new Query('allocations'))
                ->setSelectionSet([
                    'id',
                    'title',
                    'description',
                    'value',
                    (new Query('amendments'))
                        ->setSelectionSet([
                            'id',
                            'value'
                        ]),
                ]),
            (new Query('deposits'))
                ->setSelectionSet([
                    'id',
                    'value',
                    'method',
                    'processed',
                    'paymentLink'
                ]),
            (new Query('calculation'))
                ->setSelectionSet([
                    'baseValue',
                    'totalValue',
                    'totalDeposits',
                    'processingFeePercentage',
                    'processingFeeValue',
                    'processingFeeVat',
                    'processingFeeTotal',
                    (new Query('gatewayProcessingFees'))
                        ->setSelectionSet([
                            (new Query('manualEft'))
                                ->setSelectionSet([
                                    'processingFee',
                                    'totalValue'
                                ]),
                            (new Query('ecentric'))
                                ->setSelectionSet([
                                    'processingFee',
                                    'totalValue'
                                ]),
                            (new Query('ozow'))
                                ->setSelectionSet([
                                    'processingFee',
                                    'totalValue'
                                ]),
                            (new Query('snapscan'))
                                ->setSelectionSet([
                                    'processingFee',
                                    'totalValue'
                                ]),
                        ]),
                    (new Query('parties'))
                        ->setSelectionSet([
                            'role',
                            'deposit',
                            'payout',
                            'commission',
                            'processingFee',
                            'agentFee',
                            'beneficiaryFee',
                            'totalFee'
                        ]),
                    (new Query('allocations'))
                        ->setSelectionSet([
                            'value',
                            'units',
                            'unitCost',
                            'refund',
                            'payout',
                            'fee',
                            'processingFee',
                        ]),
                ]),
            'createdAt',
            'updatedAt',
        ]);

        $response = $this->client->runQuery($gql, true);
        $result = $response->getData();

        return $result['transactionCreate'];
    }

    public function getTransactionDepositLink($id)
    {
        $gql = (new Query('transactionDepositLink'));

        $gql->setVariables([
            new Variable('id', 'ID', true),
        ]);

        $variables = [
            'id' => $id,
        ];

        $gql->setArguments(['id' => '$id']);

        $response = $this->client->runQuery($gql, true,  $variables);
        $result = $response->getData();

        return $result['transactionDepositLink'];
    }

    public function allocationStartDelivery($id)
    {
        $gql = (new Mutation('allocationStartDelivery'));

        $gql->setArguments(['id' => $id]);

        $gql->setSelectionSet([
            'id',
            'state'
        ]);

        $response = $this->client->runQuery($gql, true);
        $result = $response->getData();

        return $result['allocationStartDelivery'];
    }
}