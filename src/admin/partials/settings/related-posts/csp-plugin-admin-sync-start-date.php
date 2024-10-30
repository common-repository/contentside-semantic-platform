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

$nbPostsSynced = 0;
if ( isset( $options[ CspPluginConstants::CSP_PLUGIN_RELATED_POSTS_SYNC_COUNT ] ) ) {
	$nbPostsSynced = $options[ CspPluginConstants::CSP_PLUGIN_RELATED_POSTS_SYNC_COUNT ];
}
$nbPosts      = wp_count_posts()->publish;
?>

<input
        type="hidden"
        readonly
        id="csp-plugin_sync_progress"
        name="csp-plugin_sync_progress"
        value="<?php echo esc_attr( $nbPostsSynced ) ?>"
>

<input
        type="hidden"
        readonly
        id="csp-plugin_sync_total"
        name="csp-plugin_sync_total"
        value="<?php echo esc_attr( $nbPosts ) ?>"
>

<input
        id='<?php echo esc_attr( $idKey ) ?>'
        name='<?php echo esc_attr( $this->plugin_name ) ?>_options[<?php echo esc_attr( $shortKey ) ?>]'
        type='date'
        value='<?php echo esc_attr( $options[ $shortKey ] ); ?>'
        max='<?php if ( ! empty( $max ) ) {
			echo esc_attr( $max );
		} ?>'
/>