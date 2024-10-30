<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 * For now the creation of new capabilities specific to the plugin.
 *
 * @since      1.0.0
 * @package    Csp_Plugin
 * @subpackage Csp_Plugin/includes
 * @author     Thibault Schaeller <thibault.schaeller@contentside.com>
 */
class CspPluginActivator {

	/**
	 * @since    1.0.0
	 */
	public static function activate() {
		require_once plugin_dir_path( __FILE__ ) . '../common/CspPluginCapabilities.php';
		CspPluginCapabilities::add_capabilities_to_default_roles();
	}

}
