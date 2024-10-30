<?php

use model\CspPluginNamedEntity;
use model\CspPluginNerResponse;

/**
 * The CSP specific methods needed to get moreLikeThis results.
 *
 * Defines the cspBaseUrl depending on the app environment and
 * exposes multiple methods to use the CSP's moreLikeThis feature.
 *
 * @package    Csp_Plugin
 * @subpackage Csp_Plugin/commun/CSP
 * @author     Thibault Schaeller <thibault.schaeller@contentside.com>
 */
class CspPluginNerService extends CspPluginCSPService {

	private $baseNerEndpoint;

	public function __construct() {
		parent::__construct();

		$this->cspBaseUrl      .= '/api/beta';
		$this->baseNerEndpoint = $this->cspBaseUrl . '/article/name/extract';
	}

	/**
	 * Extracts named entities from a post and returns them.
	 *
	 * @throws Exception
	 * @var      WP_Post $post The post to save.
	 * @since    1.0.0
	 * @access   public
	 */
	public function extract_post_named_entities( $post ) {
		$entities = $this->get_ner_response_for_content( $post->post_content );

		if ( empty( $entities ) ) {
			return [];
		}

		return $entities;
	}

	/**
	 * Extracts named entities from a text, title and intro and returns them.
	 *
	 * @throws Exception
	 * @since    1.0.0
	 * @access   public
	 */
	public function get_ner_response_for_content( $content, $matcher = "csp_smart_matcher" ) {
		$options   = get_option( CspPluginConstants::CSP_PLUGIN_OPTIONS_KEY );
		$threshold = ( $this->get_attribute_from_options_or_default( $options,
		                                                             $this->getThresholdOptionKey(),
		                                                             90 ) / 100 );

		$dictionary = $this->get_attribute_from_options_or_default( $options,
		                                                            $this->getDictionaryOptionKey(),
		                                                            null );

		$onlyTagFirstOccurrence = $this->get_attribute_from_options_or_default( $options,
		                                                                        $this->getOnlyFirstOccurrenceOptionKey(),
		                                                                        false );

		$currentRootUrl = get_site_url();
		// TODO For now, it is replaced later by a link generated in WP
		//  will be updated after https://contentside.atlassian.net/browse/API-721 is done
		$urlFormat = $this->get_attribute_from_options_or_default( $options,
		                                                           $this->getUrlFormatOptionKey(),
		                                                           "$currentRootUrl/tag/{{id}}" );
		$inlineTag = "<a class='csp-plugin-injected-entity' data-entity-id='{{id}}' href='{$urlFormat}'>{{quote}}</a>";

		if ( ! $dictionary ) {
			throw new Exception( "No dictionary configured" );
		}

		$nerEndpoint = $this->baseNerEndpoint . "?threshold=$threshold";

		$requestBody = [
			"html_overlap_errors" => "output",
			"inline_tagging"      => true,
			"extraction_engine"   => $matcher,
			"dictionaries"        => [ $dictionary ],
			"inline_tag"          => $inlineTag,
			"article"             => [
				"title"        => "",
				"introduction" => "",
				"text"         => $content,
			],
		];

		if ( $onlyTagFirstOccurrence ) {
			$requestBody["inline_tag_only_first"] = true;
		}

		$responseData = $this->do_request( $nerEndpoint, "POST", $requestBody );

		$entities = $this->map_entities( $responseData["result"] );

		$overlap = [];
		if ( isset( $responseData["html_overlap_errors"] ) ) {
			$overlap = $this->map_entities( $responseData["html_overlap_errors"] );
		}

		// Remove overlap from entities
		$entities = array_filter( $entities, function ( $entity ) use ( $overlap ) {
			return ! in_array( $entity, $overlap );
		} );

		return new CspPluginNerResponse( $entities, $overlap, $responseData["article"] );
	}

	/**
	 * @param WP_Term $tag
	 * @param string $type
	 * @param string $dictionary
	 *
	 * @return void
	 * @throws Exception
	 */
	public function add_tag_to_dictionary( $tag, $type, $dictionary ) {
		$dictionaryEndpoint = $this->cspBaseUrl . '/dictionaries/' . $dictionary . '/entity/' . $tag->term_id;
		$requestBody        = [
			"type"  => $type,
			"label" => $tag->name,
		];

		return $this->do_request( $dictionaryEndpoint, "PUT", $requestBody );
	}

	/**
<<<<<<< Updated upstream
=======
	 * Adds multiple tags to the CSP dictionary
	 * The $tags array should be an array of arrays with the following structure:
	 * [
	 *    [
	 *        "id" => 1,
	 *        "name" => "tag1",
	 *        "type" => "person" (optional)
	 *    ],
	 *  ...
	 * ]
	 *
	 * @param array $tags
	 * @param string $dictionary
	 *
	 * @return void
	 * @throws Exception
	 */
	public function add_tags_to_dictionary( $tags, $dictionary ) {
		$tagsBody = [];
		foreach ( $tags as $tag ) {
			$tagsBody[] = [
				"id"       => $tag["id"],
				"label"    => $tag["name"],
				"type"     => isset( $tag["type"] ) ? $tag["type"] : "TAG",
				"variants" => [],
			];
		}

		$dictionaryEndpoint = $this->cspBaseUrl . '/dictionaries/' . $dictionary . '/entity?useLabelAsVariant=true';

		return $this->do_request( $dictionaryEndpoint, "PUT", $tagsBody );
	}

	/**
	 * Save all the tags in the CSP content repository
	 *
	 * @throws Exception
	 * @since    1.0.0
	 * @access   public
	 */
	public function save_all_tags() {
		try {
			// We reinitialize the sync counter
			$options                                                   = get_option( CspPluginConstants::CSP_PLUGIN_OPTIONS_KEY );
			$options[ CspPluginConstants::CSP_PLUGIN_TAGS_SYNC_COUNT ] = 0;
			update_option( CspPluginConstants::CSP_PLUGIN_OPTIONS_KEY, $options );

			// We first empty the dictionary
			$configuredDictionary = $this->get_attribute_from_options_or_default( get_option( CspPluginConstants::CSP_PLUGIN_OPTIONS_KEY ),
			                                                                      $this->getDictionaryOptionKey(),
			                                                                      null );

			if ( ! $configuredDictionary ) {
				error_log( "No dictionary configured, could not sync the tags" );
				throw new Exception( "No dictionary configured, could not sync the tags" );
			}

			$dictionaryEndpoint = $this->cspBaseUrl . '/dictionaries/' . $configuredDictionary . '/entity';
			$requestBody        = [];
			$this->do_request( $dictionaryEndpoint, "POST", $requestBody );

			$offset = 0;
			do {
				$tags = get_terms(
					[
						"taxonomy"   => "post_tag",
						"offset"     => $offset,
						"hide_empty" => false,
						"number"     => 150,
					]
				);

				$tagIds = [];
				/** @var $tag WP_Term */
				array_walk( $tags, function ( $tag ) use ( &$tagIds ) {
					$tagIds[] = $tag->term_id;
				} );

				if ( count( $tagIds ) > 0 ) {
					// Save those tags asynchronously, 1 message every 10 seconds
					as_enqueue_async_action( 'csp_plugin_synchronize_tags', [ $tagIds ] );
				}

				$offset += 150;
			} while ( ! empty( $tags ) );
		} catch ( Exception $e ) {
			error_log( $e->getMessage() );
			error_log( $e->getTraceAsString() );
			throw $e;
		}
	}

	/**
	 * Save the tags with the given ids in the CSP dictionary
	 *
	 * @throws Exception
	 * @var      int[] $tagIds The tags to save.
	 * @since    1.0.0
	 * @access   public
	 */
	public function save_tags( $tagIds ) {
		try {
			if ( count( $tagIds ) === 0 ) {
				return;
			}

			$options              = get_option( CspPluginConstants::CSP_PLUGIN_OPTIONS_KEY );
			$configuredDictionary = $this->get_attribute_from_options_or_default( $options,
			                                                                      $this->getDictionaryOptionKey(),
			                                                                      null );

			if ( ! $configuredDictionary ) {
				error_log( "No dictionary configured, could not sync the tags" );
				throw new Exception( "No dictionary configured, could not sync the tags" );
			}

			$tags = get_terms(
				[
					"taxonomy"   => "post_tag",
					"include"    => $tagIds,
					"hide_empty" => false,
					"number"     => 150,
				]
			);

			$tagsBody = [];
			foreach ( $tags as $tag ) {
				$tagsBody[] = [
					"id"   => $tag->term_id,
					"name" => $tag->name,
					"type" => "TAG",
				];
			}

			if ( count( $tagsBody ) === 0 ) {
				return;
			}

			$this->add_tags_to_dictionary( $tagsBody, $configuredDictionary );

			if ( ! isset( $options[ CspPluginConstants::CSP_PLUGIN_TAGS_SYNC_COUNT ] ) ) {
				$$options[ CspPluginConstants::CSP_PLUGIN_TAGS_SYNC_COUNT ] = 0;
			}
			$options[ CspPluginConstants::CSP_PLUGIN_TAGS_SYNC_COUNT ] = intval( $options[ CspPluginConstants::CSP_PLUGIN_TAGS_SYNC_COUNT ] )
			                                                             + count( $tagIds );
			update_option( CspPluginConstants::CSP_PLUGIN_OPTIONS_KEY, $options );
		} catch ( Exception $e ) {
			error_log( $e->getMessage() );
			error_log( $e->getTraceAsString() );
			throw $e;
		}
	}

	/**
>>>>>>> Stashed changes
	 * Gets all the dictionaries available to the current Context
	 *
	 * @return string[] The list of dictionaries
	 * @throws Exception
	 * @since    1.0.0
	 * @access   public
	 */
	public function get_csp_dictionary_list() {
		if ( false === ( $dictionaries = wp_cache_get( 'csp_plugin_dictionaries', 'csp-plugin' ) ) ) {
			$dictionaryEndpoint = $this->cspBaseUrl . '/dictionaries';
			$dictionaries       = $this->do_request( $dictionaryEndpoint, "GET", [] );

			wp_cache_set( 'csp_plugin_dictionaries', $dictionaries, 'csp-plugin', HOUR_IN_SECONDS );
		}

		return $dictionaries;
	}

	/**
	 * Generates the URL for the entity.
	 * By default, the URL points to the entity's tag page,
	 * but it can be overwritten by the user's configuration.
	 *
	 * @param CspPluginNamedEntity $entity
	 *
	 * @return string
	 * @throws Exception
	 * @since    1.0.0
	 * @access   public
	 */
	public function generate_entity_url( $entity ) {
		$entityAssociatedTags = get_terms(
			[
				'taxonomy'   => 'post_tag',
				'search'     => $entity->get_entity(),
				'hide_empty' => false,
			]
		);

		if ( empty( $entityAssociatedTags ) ) {
			// TODO Decide if we create the associated tag or not
			return null;
		}

		if ( count( $entityAssociatedTags ) > 1 ) {
			// TODO Decide what we do if we have multiple tags matching the entity (dictionary use ?)
			return null;
		}

		$entityTag = $entityAssociatedTags[0];
		$termLink  = get_term_link( $entityTag );
		if ( is_wp_error( $termLink ) ) {
			// TODO decide what do we do if we can't get the tag's link
			return null;
		}

		return $termLink;
	}

	/**
	 * @param $entitiesAsArray
	 *
	 * @return array
	 * @throws Exception
	 */
	private function map_entities( $entitiesAsArray ) {
		$entities = [];
		array_walk( $entitiesAsArray, function ( $entity ) use ( &$entities ) {
			$entities[] = CspPluginNamedEntity::from_array(
				[
					"entity"     => html_entity_decode( $entity["label"], ENT_NOQUOTES, "UTF-8" ),
					"type"       => $entity["type"],
					"score"      => $entity["score"],
					"start_char" => $entity["start_char"],
					"end_char"   => $entity["end_char"],
					"from"       => $entity["from"],
					"id"         => $entity["id"],
				]
			);
		} );

		return $entities;
	}

	private function getThresholdOptionKey() {
		return CspPluginConstants::CSP_PLUGIN_SETTINGS_NER_THRESHOLD_SHORT_KEY;
	}

	private function getDictionaryOptionKey() {
		return CspPluginConstants::CSP_PLUGIN_SETTINGS_NER_DICTIONARY_SHORT_KEY;
	}

	private function getOnlyFirstOccurrenceOptionKey() {
		return CspPluginConstants::CSP_PLUGIN_SETTINGS_NER_ONLY_ADD_THE_FIRST_OCCURRENCE_SHORT_KEY;
	}

	private function getUrlFormatOptionKey() {
		return CspPluginConstants::CSP_PLUGIN_SETTINGS_NER_URL_FORMAT_SHORT_KEY;
	}
}