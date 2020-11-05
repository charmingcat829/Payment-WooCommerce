<?php

/**
 * View Token Details
 */

defined('ABSPATH') || exit;

?>
<h3>ARE MY DETAILS SAFE?</h3>
<p>
    TradeSafe forces HTTPS for all services using TLS (SSL) including our public website and the Application.
    All bank account details are encrypted with AES-256. Decryption keys are stored on separate machines from the
    application.
    In English, your details are encrypted with the highest industry-specific standards (which can be found in most
    banks), making your information confidential, secure and safe.
</p>

<h4>User Details</h4>

<div><strong>Name:</strong> <?php echo esc_attr($tokenData['user']['givenName']) . ' ' . esc_attr($tokenData['user']['familyName']) ?></div>
<div><strong>Email:</strong> <?php echo esc_attr($tokenData['user']['email']) ?></div>
<div><strong>Mobile:</strong> <?php echo esc_attr($tokenData['user']['mobile']) ?></div>
<div><strong>ID Number:</strong> <?php echo esc_attr($tokenData['user']['idNumber']) ?></div>

<?php if(isset($tokenData['organization']) && !is_null($tokenData['organization'])): ?>
<br>
<h4>Organization Details</h4>

<div><strong>Name:</strong> <?php echo esc_attr($tokenData['organization']['name']) ?></div>
<div><strong>Trade Name:</strong> <?php echo esc_attr($tokenData['organization']['tradeName']) ?></div>
<div><strong>Type:</strong> <?php echo esc_attr($tokenData['organization']['type']) ?></div>
<div><strong>Registration Number:</strong> <?php echo esc_attr($tokenData['organization']['registration']) ?></div>
<div><strong>VAT Number:</strong> <?php echo esc_attr($tokenData['organization']['taxNumber']) ?></div>
<?php endif; ?>

<br>

<p><a href="<?php print get_site_url(); ?>?tradesafe=unlink"
      class="button"><?php esc_html_e( 'Delete Data', 'woocommerce-tradesafe-gateway' ); ?></a></p>
