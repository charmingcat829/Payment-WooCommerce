<?php
/**
 * Wrapper for TradeSafe settings page.
 *
 * @package WooCommerce TradeSafe Gateway
 */

?>
<div class="wrap">
	<h1>TradeSafe Settings</h1>

	<form method="POST" action="options.php">
		<?php
		settings_fields( 'tradesafe' );
		do_settings_sections( 'tradesafe' );
		submit_button();
		?>
	</form>
</div>
