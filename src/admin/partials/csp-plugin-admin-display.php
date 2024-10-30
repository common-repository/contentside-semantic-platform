<?php

/**
 * Provide an admin area view for the plugin
 *
 * This file is used to mark up the admin-facing aspects of the plugin.
 *
 * @link       https://github.com/tschaeller
 * @since      1.0.0
 *
 * @package    Csp_Plugin
 * @subpackage Csp_Plugin/admin/partials
 */

use admin\CspPluginSettingsDisplayManager;

$nbPosts = wp_count_posts()->publish;
$options = get_option( CspPluginConstants::CSP_PLUGIN_OPTIONS_KEY );
if ( ! is_array( $options ) ) {
	$options = [];
}
$nbPostsSynced = 0;
if ( isset( $options[ CspPluginConstants::CSP_PLUGIN_RELATED_POSTS_SYNC_COUNT ] ) ) {
	$nbPostsSynced = $options[ CspPluginConstants::CSP_PLUGIN_RELATED_POSTS_SYNC_COUNT ];
}
?>

<div class="wrap">
    <h1><?php esc_attr_e('CSP', 'csp-plugin') ?></h1>

    <form
            action="<?php echo esc_url( admin_url( 'options.php' ) ) ?>"
            method="post"
    >
		<?php
		settings_fields( CspPluginConstants::CSP_PLUGIN_OPTIONS_KEY );
		global $wp_settings_sections, $wp_settings_fields;

		if ( ! isset( $wp_settings_sections[ $this->plugin_name ] ) ) {
			return;
		}

		echo "<div class='csp-plugin-settings-container'>";
		foreach ( (array) $wp_settings_sections[ $this->plugin_name ] as $section ) {
			echo "<div class='csp-plugin-settings-section-container'>";
                if ( '' !== $section['before_section'] ) {
                    if ( '' !== $section['section_class'] ) {
                        echo wp_kses_post( sprintf( $section['before_section'], esc_attr( $section['section_class'] ) ) );
                    } else {
                        echo wp_kses_post( $section['before_section'] );
                    }
                }

                if ( $section['title'] ) {
                    echo "<h2>{$section['title']}</h2>\n";
                }

                if ( $section['callback'] ) {
                    call_user_func( $section['callback'], $section );
                }

                if ( ! isset( $wp_settings_fields ) || ! isset( $wp_settings_fields[ $this->plugin_name ] ) || ! isset( $wp_settings_fields[ $this->plugin_name ][ $section['id'] ] ) ) {
                    continue;
                }
                echo '<table class="form-table" role="presentation">';
			    CspPluginSettingsDisplayManager::do_settings_fields( $this->plugin_name, $section['id'] );
                echo '</table>';

                if ( '' !== $section['after_section'] ) {
                    echo wp_kses_post( $section['after_section'] );
                }
			echo "</div>";
		}
		echo "</div>";
		?>

        <input name="submit" class="button button-primary" type="submit"
               value="<?php esc_attr_e( 'Save changes', 'csp-plugin' ); ?>" style="margin-top: 2em;"/>
    </form>
</div>