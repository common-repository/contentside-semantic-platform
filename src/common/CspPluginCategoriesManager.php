<?php

class CspPluginCategoriesManager {
	/**
	 * Saves the CSPCategories for the post
	 *
	 * @param WP_Post $post
	 * @param array $CSPCategories
	 *
	 * @return void
	 */
	public function saveCSPCategories( $post, $CSPCategories ) {
		update_post_meta( $post->ID, CspPluginConstants::CSP_PLUGIN_CATEGORIES_META_KEY, $CSPCategories );
	}

	/**
	 * Returns the CSPCategories currently saved for the post
	 *
	 * @param $post
	 *
	 * @return mixed
	 */
	public function getCategories( $post ) {
		return get_post_meta( $post->ID, CspPluginConstants::CSP_PLUGIN_CATEGORIES_META_KEY, true );
	}

	/**
	 * Check if the term exists by searching by ID first, then by slug
	 *
	 * @param $taxonomy
	 * @param $cspCategoryId
	 * @param $cspCategoryLabel
	 *
	 * @return WP_Term|false|object|WP_Error
	 */
	public function getTermFromCspIdAndLabel( $taxonomy, $cspCategoryId, $cspCategoryLabel ) {
		$term = get_term( $cspCategoryId, $taxonomy );
		if ( ! $term ) {
			$term = get_term_by( 'slug', sanitize_title( $cspCategoryLabel ), $taxonomy );
		}

		return $term;
	}

	/**
	 * Returns the CSPCategories currently selected for the post
	 *
	 * @param WP_Post $post
	 *
	 * @return array|mixed
	 */
	public function getSelectedCategories( $post ) {
		$CSPCategories = $this->getCategories( $post );
		if ( empty( $CSPCategories ) ) {
			return [];
		}

		$postCategories = $post->post_category;

		return array_filter( $CSPCategories, function ( $elem ) use ( $postCategories ) {
			return in_array( sanitize_title( $elem['id'] ), $postCategories );
		} );
	}

	public function isCategorySelected( $post, $categoryId, $selectedCategories = [] ) {
		if ( empty( $selectedCategories ) ) {
			$selectedCategories = $this->getSelectedCategories( $post );
		}

		return count( array_filter( $selectedCategories, function ( $elem ) use ( $categoryId ) {
				return $elem['id'] == sanitize_title( $categoryId );
			} ) ) > 0;
	}
}