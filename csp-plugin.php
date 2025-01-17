<?php

/**
 * @link              https://www.contentside.com/
 * @since             1.0.0
 * @package           Csp_Plugin
 *
 * @wordpress-plugin
 * Plugin Name:       ContentSide Semantic Platform
 * Plugin URI:        https://www.contentside.com/solutions/
 * Description:       Enrichissez vos articles avec Semantic Platform
 * Version:           1.5.0
 * Requires at least: 5.4.1
 * Requires PHP:      5.6
 * Author:            ContentSide
 * Author URI:        https://www.contentside.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       csp-plugin
 * Domain Path:       /src/languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Current plugin version.
 */
define( 'CSP_PLUGIN_VERSION', '1.0.0' );

// Plugin base file
if ( ! defined( 'CSP_PLUGIN_FILE' ) ) {
	define( 'CSP_PLUGIN_FILE', __FILE__ );
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-csp-plugin-activator.php
 */
function activate_csp_plugin() {
	require_once plugin_dir_path( __FILE__ ) . 'src/includes/CspPluginActivator.php';
	CspPluginActivator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-csp-plugin-deactivator.php
 */
function deactivate_csp_plugin() {
	require_once plugin_dir_path( __FILE__ ) . 'src/includes/CspPluginDeactivator.php';
	CspPluginDeactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_csp_plugin' );
register_deactivation_hook( __FILE__, 'deactivate_csp_plugin' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'src/includes/CspPlugin.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_csp_plugin() {

	$plugin = new CspPlugin();
	$plugin->run();
}

run_csp_plugin();
