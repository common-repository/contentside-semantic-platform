<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly // Silence is golden

/**
 * @link       https://github.com/tschaeller
 * @since      1.0.0
 *
 * @package    Csp_Plugin
 * @subpackage Csp_Plugin/admin/partials
 */

function render() {
	ob_start();
	echo "<h3 class='csp-plugin-no-margin'>";
	esc_attr_e( 'Initialization', 'csp-plugin' );
	echo "</h3>";
	return ob_get_clean();
}
