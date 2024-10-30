<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Displays the related articles
 *
 *
 * @link       https://github.com/tschaeller
 * @since      1.0.0
 *
 * @package    Csp_Plugin
 * @subpackage Csp_Plugin/admin/partials/shortcode
 */

$relatedPosts = csp_plugin_get_related_posts( $post );
?>

<?php if ( count( $relatedPosts ) === 0 ) {
	return;
} ?>
<div class="csp-plugin-related-posts-container">
    <h3><?php _e( 'short_code_title', 'csp-plugin' ); ?></h3>

    <ul>
		<?php
		$countPosts = count( $relatedPosts );
		/** @var WP_Post $related_post */
		foreach ( $relatedPosts as $index => $related_post ) {
			$shouldDisplaySeparator = ( $countPosts > 0 && $index !== $countPosts - 1 );
			$relatedPostUrl         = get_post_permalink( $related_post );
			echo "<a href='" . esc_url( $relatedPostUrl ) . "'>";
			echo "<li>";
			// The link card content
			echo "
                <div class='csp-plugin-related-post-content'>
                    " . esc_html( $related_post->post_title ) . "
                </div>
            ";
			echo "</li>";
			echo "</a>";
		}
		?>
    </ul>
</div>
