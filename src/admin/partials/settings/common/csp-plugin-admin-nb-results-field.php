<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly // Silence is golden

/**
 * @link       https://github.com/tschaeller
 * @since      1.0.0
 *
 * @package    Csp_Plugin
 * @subpackage Csp_Plugin/admin/partials
 */
// TODO mutualise this with csp-plugin/src/admin/partials/settings/common/csp-plugin-admin-threshold-field.php
$options = get_option( CspPluginConstants::CSP_PLUGIN_OPTIONS_KEY );
if ( ! isset( $idKey ) ) {
	throw new Exception( 'idKey is not defined' );
}

if ( ! isset( $shortKey ) ) {
	throw new Exception( 'shortKey is not defined' );
}


if ( ! isset( $default ) ) {
	$default = 3;
}

if ( ! isset( $options[ $shortKey ] ) ) {
	$options[ $shortKey ] = $default;
}
?>

<input
        id='<?php echo esc_attr($idKey) ?>'
        name='<?php echo esc_attr($this->plugin_name) ?>_options[<?php echo esc_attr($shortKey) ?>]'
        type='number'
        min="1"
        step="1"
        value='<?php echo esc_attr( $options[ $shortKey ] ); ?>'
        style="min-width: 90%;"
        class="csp-plugin-margin-bottom-2"
/>