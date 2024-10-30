<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly // Silence is golden

/**
 * @link       https://github.com/tschaeller
 * @since      1.4.0
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

$selectedValue = null;
if ( isset( $options[ $shortKey ] ) ) {
	$selectedValue = $options[ $shortKey ];
}

$taxonomies = get_taxonomies( [ 'public' => true ] );
?>

<select name='<?php echo esc_attr( $this->plugin_name ) ?>_options[<?php echo esc_attr( $shortKey ) ?>]'
        id='<?php echo esc_attr( $idKey ) ?>'
        style="min-width: 90%;"
>
	<?php foreach ( $taxonomies as $taxonomy ) { ?>
        <option
                value="<?php echo esc_attr( $taxonomy ); ?>"
			<?php if ( $taxonomy === $selectedValue )
				echo "selected" ?>
        >
			<?php echo esc_html( $taxonomy ); ?>
        </option>
	<?php } ?>
</select>