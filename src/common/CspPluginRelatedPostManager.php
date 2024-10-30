<?php

class CspPluginRelatedPostManager {

	/**
	 * @param WP_Post $post
	 * @param int[] $ids
	 *
	 * @return void
	 */
	public function saveRelatedPosts( $post, $ids ) {
		update_post_meta( $post->ID, CspPluginConstants::CSP_PLUGIN_RELATED_POSTS_META_KEY, json_encode( $ids ) );
	}

	/**
	 * @param WP_Post $post
	 *
	 * @return array
	 */
	public function getRelatedPostsObjects( $post ) {
		$idsInJSON = get_post_meta( $post->ID, CspPluginConstants::CSP_PLUGIN_RELATED_POSTS_META_KEY, true );

		$ids = json_decode( $idsInJSON, true );

		if ( empty( $ids ) ) {
			return [];
		}

		return get_posts( [ "include" => $ids, "suppress_filters" => false ] );
	}

	/**
	 * @param WP_Post $post
	 * @param int $relatedPost
	 *
	 * @return array
	 */
	public function removeRelatedPost( $post, $relatedPost ) {
		$idsInJSON = get_post_meta( $post->ID, CspPluginConstants::CSP_PLUGIN_RELATED_POSTS_META_KEY, true );

		$ids = json_decode( $idsInJSON, true );

		if ( empty( $ids ) ) {
			return [];
		}

		$ids = array_filter( $ids, function ( $elem ) use ( $relatedPost ) {
			return intval($elem) !== intval($relatedPost);
		} );

		$this->saveRelatedPosts( $post, $ids );

		if ( empty( $ids ) ) {
			return [];
		}

		return get_posts( [ "include" => $ids, "suppress_filters" => false ] );
	}

	/**
	 * @param WP_Post $post
	 *
	 * @return array
	 */
	public function removeAllRelatedPost( $post ) {
		$ids = [];

		$this->saveRelatedPosts( $post, $ids );

		return [];
	}
}