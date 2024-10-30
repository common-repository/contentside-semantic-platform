<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly // Silence is golden

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
if (
	! CspPluginUtils::isApiKeyValid()
	|| ! CspPluginUtils::isFeatureAllowed( CspPluginConstants::CSP_PLUGIN_API_RELATED_POSTS_FEATURE_NAME )
	|| ! current_user_can( CspPluginCapabilities::CSP_PLUGIN_CAPABILITY_LOOKALIKE_DISCOVER )
) {
	return;
}
?>

<div class="wrap">
	<?php if ( ! empty( $relatedPosts ) ) { ?>
        <button
                class="button button-primary csp-plugin-meta-box-action-button csp-plugin-related-posts-meta-box-action-button csp-plugin-button-alert"
                data-action="remove_all_related_posts"
                data-postId="<?php echo esc_attr( $post->ID ) ?>"
                data-nonce="<?php echo wp_create_nonce( 'csp-plugin_remove_all_related_posts' ) ?>"
                data-ajaxurl="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ) ?>"
        >
			<?php esc_attr_e( 'Remove all related posts', 'csp-plugin' ) ?>
        </button>
	<?php } ?>

    <table class='wp-list-table widefat fixed pages sortable csp-plugin-related-posts-table'>
        <thead>
        <tr>
            <th scope='col' id='relatedPost'><?php esc_attr_e( 'Related post', 'csp-plugin' ) ?></th>
            <th scope='col' id='select'><?php esc_attr_e( 'Select', 'csp-plugin' ) ?></th>
        <tr>
        </thead>
        <tbody>
		<?php
		if ( empty( $relatedPosts ) ) {
			echo "<tr>";
			echo "<td colspan='2'>";
			esc_attr_e( 'No related post found', 'csp-plugin' );
			echo "</td>";
			echo "</tr>";
		}
		/** @var WP_Post $related_post */
		foreach ( $relatedPosts as $related_post ) {
			$postAdminUrl = get_admin_url() . "post.php?post={$related_post->ID}&amp;action=edit";
			echo "<tr>";

			echo "<td>";
			echo "<strong><a href='" . esc_url( $postAdminUrl ) . "' target='_blank'>" . esc_html( $related_post->post_title ) . "</a></strong>";
			echo "</td>";

			echo "<td>";
			echo "<span
                    id='csp-plugin-remove-related-post-button-" . esc_attr( $related_post->ID ) . "'
                    class='dashicons dashicons-dismiss csp-plugin-remove-related-post-button csp-plugin-tooltip-parent'
                    data-action='remove_related_post'
                    data-postId='" . esc_attr( $post->ID ) . "'
                    data-relatedPost='" . esc_attr( $related_post->ID ) . "'
                    data-nonce='" . wp_create_nonce( 'csp-plugin_remove_related_post' ) . "'
                    data-ajaxurl='" . esc_url( admin_url( 'admin-ajax.php' ) ) . "'
                    >
                        <span class='csp-plugin-tooltip-text'>" . esc_attr__( 'Remove from related posts list',
			                                                                  'csp-plugin' ) . "</span>
                    </span>
                ";
			echo "</td>";

			echo "</tr>";
		}
		?>
        </tbody>
    </table>
</div>