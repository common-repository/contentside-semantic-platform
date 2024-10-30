<?php

class CspPluginCategorizeService extends CspPluginCSPService {
	/**
	 * @var CspPluginCategoriesManager $categoriesManager
	 */
	private $categoriesManager;

	public function __construct() {
		parent::__construct();

		$this->categoriesManager = new CspPluginCategoriesManager();
	}

	/**
	 * Gets all the taxonomies available to the current Context
	 *
	 * @return string[] The list of taxonomies
	 * @throws Exception
	 * @since    1.0.0
	 * @access   public
	 */
	public function get_csp_taxonomy_list() {
		if ( false === ( $taxonomies = wp_cache_get( 'csp_plugin_taxonomies', 'csp-plugin' ) ) ) {
			$taxonomyEndpoint = $this->cspBaseUrl . '/api/beta/taxonomy';
			$taxonomies       = $this->do_request( $taxonomyEndpoint, "GET", [] );

			wp_cache_set( 'csp_plugin_taxonomies', $taxonomies, 'csp-plugin', HOUR_IN_SECONDS );
		}

		return $taxonomies;
	}

	/**
	 * Discovers the Posts that look alike in CSP content repository
	 *
	 * @throws Exception
	 * @var      WP_Post $post The post to save.
	 * @since    1.0.0
	 * @access   public
	 */
	public function categorize_post( $post ) {
		$categories = $this->categorize_for_content_title_and_intro( $post->post_content,
		                                                             $post->post_title,
		                                                             $post->post_excerpt ?: $post->post_title
		);

		$this->categoriesManager->saveCSPCategories( $post, $categories );

		return $categories;
	}

	/**
	 * Returns the inferred categories for the given content
	 *
	 * @param $content
	 * @param $title
	 * @param $introduction
	 * @param bool $asTags
	 *
	 * @return array
	 * @throws Exception
	 */
	public function categorize_for_content_title_and_intro( $content, $title, $introduction, $asTags = false ) {
		$options = get_option( CspPluginConstants::CSP_PLUGIN_OPTIONS_KEY );

		$limit     = $this->get_attribute_from_options_or_default( $options,
		                                                           $this->getLimitOptionKey( $asTags ),
		                                                           3 );
		$threshold = ( $this->get_attribute_from_options_or_default( $options,
		                                                             $this->getThresholdOptionKey( $asTags ),
		                                                             90 ) / 100 );
		$cspTaxonomy  = $this->get_attribute_from_options_or_default( $options,
		                                                           $this->getCspTaxonomyOptionKey( $asTags ),
		                                                           "" );
		$wpTaxonomy  = $this->get_attribute_from_options_or_default( $options,
		                                                           $this->getWpTaxonomyOptionKey( $asTags ),
		                                                           "" );
		if ( $cspTaxonomy == "" ) {
			throw new Exception( "No CSP taxonomy selected for categorization." );
		}

		if ( $wpTaxonomy == "" ) {
			throw new Exception( "No WP taxonomy selected for categorization." );
		}

		$categorizeEndpoint = $this->cspBaseUrl . "/api/beta/article/categorize?limit=$limit&threshold=$threshold";

		$postAsArray = [
			"article"  => [
				"title"        => $title,
				"introduction" => $introduction,
				"text"         => strip_tags( $content ),
			],
			"taxonomy" => [
				"name" => $cspTaxonomy,
			],
		];

		$responseData  = $this->do_request( $categorizeEndpoint, "POST", $postAsArray );
		$cspCategories = $responseData["result"];

		// We have to fetch them one by one to get the full category object
		//  as we search first by id then by slug
		$hydratedTerms = [];

		foreach ( $cspCategories as $cspCategory ) {
			$termObject = $this->categoriesManager->getTermFromCspIdAndLabel( $wpTaxonomy,
			                                                                  $cspCategory['id'],
			                                                                  $cspCategory['label'] );
			if ( $termObject ) {
				$hydratedTerms[] = [
					"id"     => $termObject->term_id,
					"label"  => $termObject->name,
					"parent" => $termObject->parent,
				];
			}
		}

		if ( empty( $hydratedTerms ) ) {
			return [];
		}

		return $hydratedTerms;
	}

	private function getLimitOptionKey( $asTags ) {
		return $asTags ? CspPluginConstants::CSP_PLUGIN_SETTINGS_TAGGING_NB_RESULTS_SHORT_KEY : CspPluginConstants::CSP_PLUGIN_SETTINGS_CATEGORIZE_NB_RESULTS_SHORT_KEY;
	}

	private function getThresholdOptionKey( $asTags ) {
		return $asTags ? CspPluginConstants::CSP_PLUGIN_SETTINGS_TAGGING_THRESHOLD_SHORT_KEY : CspPluginConstants::CSP_PLUGIN_SETTINGS_CATEGORIZE_THRESHOLD_SHORT_KEY;
	}

	private function getCspTaxonomyOptionKey( $asTags ) {
		return $asTags ? CspPluginConstants::CSP_PLUGIN_SETTINGS_TAGGING_TAXONOMY_SHORT_KEY : CspPluginConstants::CSP_PLUGIN_SETTINGS_CATEGORIZE_TAXONOMY_SHORT_KEY;
	}

	private function getWpTaxonomyOptionKey( $asTags ) {
		return $asTags ? CspPluginConstants::CSP_PLUGIN_SETTINGS_TAGGING_WP_TAXONOMY_SHORT_KEY : CspPluginConstants::CSP_PLUGIN_SETTINGS_CATEGORIZE_WP_TAXONOMY_SHORT_KEY;
	}
}