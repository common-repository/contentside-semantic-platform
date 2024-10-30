<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    Csp_Plugin
 * @subpackage Csp_Plugin/includes
 * @author     Thibault Schaeller <thibault.schaeller@contentside.com>
 */
class CspPluginDeactivator {

	/**
	 * @since    1.0.0
	 */
	public static function deactivate() {
		require_once plugin_dir_path( __FILE__ ) . '../common/CspPluginCapabilities.php';
		CspPluginCapabilities::remove_capabilities_from_all_editable_roles();
	}

}
