<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly // Silence is golden

/**
 * @link       https://github.com/tschaeller
 * @since      1.3.2
 *
 * @package    Csp_Plugin
 * @subpackage Csp_Plugin/admin/partials
 */
$options = get_option( CspPluginConstants::CSP_PLUGIN_OPTIONS_KEY );
if ( ! isset( $idKey ) ) {
	throw new Exception( 'idKey is not defined' );
}

if ( ! isset( $shortKey ) ) {
	throw new Exception( 'shortKey is not defined' );
}

if ( ! isset( $options[ $shortKey ] ) ) {
	$options[ $shortKey ] = '';
}
?>

<input
        id='<?php echo esc_attr( $idKey ) ?>'
        name='<?php echo esc_attr( $this->plugin_name ) ?>_options[<?php echo esc_attr( $shortKey ) ?>]'
        type='hidden'
        value="<?php echo esc_attr( $options[ $shortKey ] ) ?>"
/>