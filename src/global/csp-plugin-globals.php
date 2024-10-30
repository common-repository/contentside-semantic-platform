<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Returns the related posts currently saved for the post passed as an argument.
 * The related posts won't be discovered through this method, it is only a retrieval
 * of already saved relations.
 *
 *
 * @param WP_Post $post
 *
 * @return array
 *
 * @since    1.0.0
 * @access   private
 */
function csp_plugin_get_related_posts( $post ) {
	$cspRelatedPostManager = new CspPluginRelatedPostManager();

	return $cspRelatedPostManager->getRelatedPostsObjects( $post );

}