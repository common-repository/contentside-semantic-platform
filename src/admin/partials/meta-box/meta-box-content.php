<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Displays the related articles and allows to delete them if wanted
 *
 *
 * @link       https://github.com/tschaeller
 * @since      1.0.0
 *
 * @package    Csp_Plugin
 * @subpackage Csp_Plugin/admin/partials/meta-box
 */

$relatedPostManager = new CspPluginRelatedPostManager();
$relatedPosts       = $relatedPostManager->getRelatedPostsObjects( $post );
?>

<div class="wrap">
    <button
            class="button button-primary csp-plugin-discover"
            data-action="discover_post"
            data-postId="<?php echo esc_html( $post->ID ) ?>"
            data-nonce="<?php echo wp_create_nonce( 'csp-plugin_discover_post_look_alike' ) ?>"
            data-ajaxurl="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ) ?>"
    >
		<?php esc_attr_e( 'Discover posts', 'csp-plugin' ); ?>
    </button>

    <table class='wp-list-table widefat fixed pages sortable csp-plugin-related-posts-table'>
		<?php
		/** @var WP_Post $related_post */
		foreach ( $relatedPosts as $related_post ) {
			$postAdminUrl = get_admin_url() . "post.php?post={$related_post->ID}&amp;action=edit";
			echo "<tr>";
			echo "<td>";
			echo "<strong><a href='" . esc_url( $postAdminUrl ) . "' target='_blank'>" . esc_html( $related_post->post_title ) . "</a></strong>";
			echo "<span
                    id='csp-plugin-remove-related-post-button-" . esc_attr( $related_post->ID ) . "'
                    class='dashicons dashicons-dismiss csp-plugin-remove-related-post-button'
                    data-action='remove_related_post'
                    data-postId='" . esc_attr( $post->ID ) . "'
                    data-relatedPost='" . esc_attr( $related_post->ID ) . "'
                    data-nonce='" . wp_create_nonce( 'csp-plugin_remove_related_post' ) . "'
                    data-ajaxurl='" . esc_url( admin_url( 'admin-ajax.php' ) ) . "'
                    ></span>
                ";
			echo "</td>";
			echo "</tr>";
		}
		?>
    </table>
</div>