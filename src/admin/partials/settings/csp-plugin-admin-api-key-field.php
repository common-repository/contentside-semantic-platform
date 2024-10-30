<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

/**
 * @link       https://github.com/tschaeller
 * @since      1.0.0
 *
 * @package    Csp_Plugin
 * @subpackage Csp_Plugin/admin/partials
 */
$options = get_option( CspPluginConstants::CSP_PLUGIN_OPTIONS_KEY );
if ( ! is_array( $options ) ) {
	$options = [];
}

if ( ! isset( $options[ CspPluginConstants::CSP_PLUGIN_SETTINGS_API_KEY_KEY ] ) ) {
	$options[ CspPluginConstants::CSP_PLUGIN_SETTINGS_API_KEY_KEY ] = '';
}
if ( ! isset( $options[ CspPluginConstants::CSP_PLUGIN_RELATED_POSTS_SYNC_COUNT ] ) ) {
	$options[ CspPluginConstants::CSP_PLUGIN_RELATED_POSTS_SYNC_COUNT ] = 0;
}

$is_api_key_valid = false;
if ( isset( $options[ CspPluginConstants::CSP_PLUGIN_SETTINGS_API_KEY_VALID_KEY ] ) ) {
	$is_api_key_valid = boolval( $options[ CspPluginConstants::CSP_PLUGIN_SETTINGS_API_KEY_VALID_KEY ] );
}
?>

    <input
            id='<?php echo esc_attr( $this->plugin_name ) ?>_setting_api_key'
            name='<?php echo esc_attr( $this->plugin_name ) ?>_options[<?php echo esc_attr( CspPluginConstants::CSP_PLUGIN_SETTINGS_API_KEY_KEY ) ?>]'
            type='text'
            value='<?php echo esc_attr( $options[ CspPluginConstants::CSP_PLUGIN_SETTINGS_API_KEY_KEY ] ); ?>'
            style="min-width: 90%;"
    />

<?php
if ( ! $is_api_key_valid && $options[ CspPluginConstants::CSP_PLUGIN_SETTINGS_API_KEY_KEY ] !== '' ) {
	?>
    <p style="color: red;">
		<?php
		echo esc_html( __( 'The API key is invalid. Please check the key and try again.', 'csp-plugin' ) );
		?>
    </p>
	<?php
}
?>