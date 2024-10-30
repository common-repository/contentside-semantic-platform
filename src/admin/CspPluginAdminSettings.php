<?php

/**
 * The admin-specific setting definition of the plugin.
 *
 * Defines the plugin settings, including the API key.
 *
 * @package    Csp_Plugin
 * @subpackage Csp_Plugin/admin
 * @author     Thibault Schaeller <thibault.schaeller@contentside.com>
 */
class CspPluginAdminSettings {

	/**
	 * The ID of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $plugin_name The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * @param string $plugin_name
	 */
	public function __construct( $plugin_name ) {
		$this->plugin_name = $plugin_name;
	}

	/**
	 * Will render the admin configuration page
	 *
	 * @return void
	 *
	 * @throws Exception
	 * @since   1.0.0
	 */
	public function register_admin_settings() {
		register_setting(
			CspPluginConstants::CSP_PLUGIN_OPTIONS_KEY,
			CspPluginConstants::CSP_PLUGIN_OPTIONS_KEY
		);

		add_settings_section(
			'api_settings',
			__( 'API Settings', "csp-plugin" ),
			[ $this, 'display_section_text' ],
			$this->plugin_name
		);

		add_settings_field(
			CspPluginConstants::CSP_PLUGIN_SETTINGS_API_KEY_KEY,
			__( 'API Key', "csp-plugin" ),
			[ $this, 'display_setting_api_key' ],
			$this->plugin_name,
			'api_settings'
		);

		// We register the settings for the different features only if the API key is set and validated
		if ( CspPluginUtils::isApiKeyValid() ) {
			$this->register_settings_if_allowed( get_option( CspPluginConstants::CSP_PLUGIN_API_ALLOWED_FEATURES_KEY ) );
		}
	}

	/**
	 * Will register the settings for the related posts feature
	 *
	 * @return void
	 * @since 1.0.0
	 */
	private function register_related_posts_settings() {
		register_setting(
			CspPluginConstants::CSP_PLUGIN_RELATED_POSTS_OPTIONS_KEY,
			CspPluginConstants::CSP_PLUGIN_RELATED_POSTS_OPTIONS_KEY
		);

		add_settings_section(
			'related_posts_settings',
			__( 'Related posts', "csp-plugin" ),
			function () {
				return "";
			},
			$this->plugin_name
		);

		add_settings_field(
			CspPluginConstants::CSP_PLUGIN_SETTINGS_RELATED_POSTS_NB_RESULTS_KEY,
			__( 'Number of related posts returned', "csp-plugin" ),
			[ $this, 'display_setting_nb_results' ],
			$this->plugin_name,
			'related_posts_settings',
			[
				'idKey'    => CspPluginConstants::CSP_PLUGIN_SETTINGS_RELATED_POSTS_NB_RESULTS_KEY,
				'shortKey' => CspPluginConstants::CSP_PLUGIN_SETTINGS_RELATED_POSTS_NB_RESULTS_SHORT_KEY,
			]
		);

		add_settings_field(
			CspPluginConstants::CSP_PLUGIN_SETTINGS_RELATED_POSTS_LAST_SYNC_DATE_KEY,
			"",
			[ $this, 'display_setting_hidden_field' ],
			$this->plugin_name,
			'related_posts_settings',
			[
				'idKey'    => CspPluginConstants::CSP_PLUGIN_SETTINGS_RELATED_POSTS_LAST_SYNC_DATE_KEY,
				'shortKey' => CspPluginConstants::CSP_PLUGIN_SETTINGS_RELATED_POSTS_LAST_SYNC_DATE_SHORT_KEY,
				'hidden'   => true,
			]
		);

		// We only need to register the start date option
		add_settings_field(
			CspPluginConstants::CSP_PLUGIN_SETTINGS_RELATED_POSTS_SYNC_START_DATE_KEY,
			__( 'Starting date for the synchronization', "csp-plugin" ),
			[ $this, 'display_setting_related_posts_sync_start_date' ],
			$this->plugin_name,
			'related_posts_settings',
			[
				'idKey'    => CspPluginConstants::CSP_PLUGIN_SETTINGS_RELATED_POSTS_SYNC_START_DATE_KEY,
				'shortKey' => CspPluginConstants::CSP_PLUGIN_SETTINGS_RELATED_POSTS_SYNC_START_DATE_SHORT_KEY,
				'max'      => ( new DateTime() )->format( 'Y-m-d' ),
				'before'   => [
					'class'   => 'csp-plugin-border-top csp-plugin-full-width',
					'content' => $this->display_setting_related_posts_sync_title(),
				],
				'after'    => [
					'content' => $this->display_setting_related_posts_sync_button(),
					'colspan' => '2',
				],
			]
		);

	}

	/**
	 * Will display the settings section for the categorize feature
	 *
	 * @return void
	 * @since 1.0.0
	 */
	private function register_categorize_settings() {
		register_setting(
			CspPluginConstants::CSP_PLUGIN_CATEGORIZE_OPTIONS_KEY,
			CspPluginConstants::CSP_PLUGIN_CATEGORIZE_OPTIONS_KEY
		);

		add_settings_section(
			'categorize_posts_settings',
			__( 'Semantic Platform: Recommended categories', "csp-plugin" ),
			function () {
				return "";
			},
			$this->plugin_name
		);

		add_settings_field(
			CspPluginConstants::CSP_PLUGIN_SETTINGS_CATEGORIZE_NB_RESULTS_KEY,
			__( 'Number of categories returned', "csp-plugin" ),
			[ $this, 'display_setting_nb_results' ],
			$this->plugin_name,
			'categorize_posts_settings',
			[
				'idKey'    => CspPluginConstants::CSP_PLUGIN_SETTINGS_CATEGORIZE_NB_RESULTS_KEY,
				'shortKey' => CspPluginConstants::CSP_PLUGIN_SETTINGS_CATEGORIZE_NB_RESULTS_SHORT_KEY,
			]
		);

		add_settings_field(
			CspPluginConstants::CSP_PLUGIN_SETTINGS_CATEGORIZE_THRESHOLD_KEY,
			__( 'Minimum score for the categories', "csp-plugin" ),
			[ $this, 'display_setting_threshold' ],
			$this->plugin_name,
			'categorize_posts_settings',
			[
				'idKey'    => CspPluginConstants::CSP_PLUGIN_SETTINGS_CATEGORIZE_THRESHOLD_KEY,
				'shortKey' => CspPluginConstants::CSP_PLUGIN_SETTINGS_CATEGORIZE_THRESHOLD_SHORT_KEY,
			]
		);

		add_settings_field(
			CspPluginConstants::CSP_PLUGIN_SETTINGS_CATEGORIZE_TAXONOMY_KEY,
			__( 'The name of the category tree', "csp-plugin" ),
			[ $this, 'display_setting_taxonomy' ],
			$this->plugin_name,
			'categorize_posts_settings',
			[
				'idKey'    => CspPluginConstants::CSP_PLUGIN_SETTINGS_CATEGORIZE_TAXONOMY_KEY,
				'shortKey' => CspPluginConstants::CSP_PLUGIN_SETTINGS_CATEGORIZE_TAXONOMY_SHORT_KEY,
			]
		);

		add_settings_field(
			CspPluginConstants::CSP_PLUGIN_SETTINGS_CATEGORIZE_WP_TAXONOMY_KEY,
			__( 'The name of the taxonomy to use', "csp-plugin" ),
			[ $this, 'display_setting_wp_taxonomy' ],
			$this->plugin_name,
			'categorize_posts_settings',
			[
				'idKey'    => CspPluginConstants::CSP_PLUGIN_SETTINGS_CATEGORIZE_WP_TAXONOMY_KEY,
				'shortKey' => CspPluginConstants::CSP_PLUGIN_SETTINGS_CATEGORIZE_WP_TAXONOMY_SHORT_KEY,
			]
		);
	}

	/**
	 * Will render the settings section for the tagging feature
	 *
	 * @return void
	 * @since 1.0.0
	 */
	private function register_tagging_settings() {
		register_setting(
			CspPluginConstants::CSP_PLUGIN_TAGGING_OPTIONS_KEY,
			CspPluginConstants::CSP_PLUGIN_TAGGING_OPTIONS_KEY
		);

		add_settings_section(
			'tagging_posts_settings',
			__( 'Semantic Platform: Recommended tags', "csp-plugin" ),
			function () {
				return "";
			},
			$this->plugin_name
		);

		add_settings_field(
			CspPluginConstants::CSP_PLUGIN_SETTINGS_TAGGING_NB_RESULTS_KEY,
			__( 'Number of tags returned', "csp-plugin" ),
			[ $this, 'display_setting_nb_results' ],
			$this->plugin_name,
			'tagging_posts_settings',
			[
				'idKey'    => CspPluginConstants::CSP_PLUGIN_SETTINGS_TAGGING_NB_RESULTS_KEY,
				'shortKey' => CspPluginConstants::CSP_PLUGIN_SETTINGS_TAGGING_NB_RESULTS_SHORT_KEY,
				'default'  => 8,
			]
		);

		add_settings_field(
			CspPluginConstants::CSP_PLUGIN_SETTINGS_TAGGING_THRESHOLD_KEY,
			__( 'Minimum score for the tags', "csp-plugin" ),
			[ $this, 'display_setting_threshold' ],
			$this->plugin_name,
			'tagging_posts_settings',
			[
				'idKey'    => CspPluginConstants::CSP_PLUGIN_SETTINGS_TAGGING_THRESHOLD_KEY,
				'shortKey' => CspPluginConstants::CSP_PLUGIN_SETTINGS_TAGGING_THRESHOLD_SHORT_KEY,
			]
		);

		add_settings_field(
			CspPluginConstants::CSP_PLUGIN_SETTINGS_TAGGING_TAXONOMY_KEY,
			__( 'The name of the tagging tree', "csp-plugin" ),
			[ $this, 'display_setting_taxonomy' ],
			$this->plugin_name,
			'tagging_posts_settings',
			[
				'idKey'    => CspPluginConstants::CSP_PLUGIN_SETTINGS_TAGGING_TAXONOMY_KEY,
				'shortKey' => CspPluginConstants::CSP_PLUGIN_SETTINGS_TAGGING_TAXONOMY_SHORT_KEY,
			]
		);

		add_settings_field(
			CspPluginConstants::CSP_PLUGIN_SETTINGS_TAGGING_WP_TAXONOMY_KEY,
			__( 'The name of the taxonomy to use', "csp-plugin" ),
			[ $this, 'display_setting_wp_taxonomy' ],
			$this->plugin_name,
			'tagging_posts_settings',
			[
				'idKey'    => CspPluginConstants::CSP_PLUGIN_SETTINGS_TAGGING_WP_TAXONOMY_KEY,
				'shortKey' => CspPluginConstants::CSP_PLUGIN_SETTINGS_TAGGING_WP_TAXONOMY_SHORT_KEY,
			]
		);
	}

	/**
	 * Will render the settings section for the NER feature
	 *
	 * @return void
	 * @since 1.0.0
	 */
	private function register_ner_settings() {
		register_setting(
			CspPluginConstants::CSP_PLUGIN_NER_OPTIONS_KEY,
			CspPluginConstants::CSP_PLUGIN_NER_OPTIONS_KEY
		);

		add_settings_section(
			'ner_posts_settings',
			__( 'Extract named entities', "csp-plugin" ),
			function () {
				return "";
			},
			$this->plugin_name
		);

		add_settings_field(
			CspPluginConstants::CSP_PLUGIN_SETTINGS_NER_DICTIONARY_KEY,
			__( 'The name of the dictionary', "csp-plugin" ),
			[ $this, 'display_setting_dictionary' ],
			$this->plugin_name,
			'ner_posts_settings',
			[
				'idKey'    => CspPluginConstants::CSP_PLUGIN_SETTINGS_NER_DICTIONARY_KEY,
				'shortKey' => CspPluginConstants::CSP_PLUGIN_SETTINGS_NER_DICTIONARY_SHORT_KEY,
			]
		);

		add_settings_field(
			CspPluginConstants::CSP_PLUGIN_SETTINGS_NER_THRESHOLD_KEY,
			__( 'Minimum score for the named entities', "csp-plugin" ),
			[ $this, 'display_setting_threshold' ],
			$this->plugin_name,
			'ner_posts_settings',
			[
				'idKey'    => CspPluginConstants::CSP_PLUGIN_SETTINGS_NER_THRESHOLD_KEY,
				'shortKey' => CspPluginConstants::CSP_PLUGIN_SETTINGS_NER_THRESHOLD_SHORT_KEY,
			]
		);

//		TODO: After https://contentside.atlassian.net/browse/API-721 is done, we can use the slug instead of the id and
//          let the user configure the way they want the URLs to be formatted
//		add_settings_field(
//			CspPluginConstants::CSP_PLUGIN_SETTINGS_NER_URL_FORMAT_KEY,
//			__( 'Format of the URLs for the named entities', "csp-plugin" ),
//			[ $this, 'display_setting_ner_url_format' ],
//			$this->plugin_name,
//			'ner_posts_settings',
//			[
//				'idKey'    => CspPluginConstants::CSP_PLUGIN_SETTINGS_NER_URL_FORMAT_KEY,
//				'shortKey' => CspPluginConstants::CSP_PLUGIN_SETTINGS_NER_URL_FORMAT_SHORT_KEY,
//			]
//		);

		add_settings_field(
			CspPluginConstants::CSP_PLUGIN_SETTINGS_NER_ONLY_ADD_AS_TAG_KEY,
			__( 'To only add the entities as tags and not insert them in the content', "csp-plugin" ),
			[ $this, 'display_setting_boolean' ],
			$this->plugin_name,
			'ner_posts_settings',
			[
				'idKey'    => CspPluginConstants::CSP_PLUGIN_SETTINGS_NER_ONLY_ADD_AS_TAG_KEY,
				'shortKey' => CspPluginConstants::CSP_PLUGIN_SETTINGS_NER_ONLY_ADD_AS_TAG_SHORT_KEY,
			]
		);

		add_settings_field(
			CspPluginConstants::CSP_PLUGIN_SETTINGS_NER_ONLY_ADD_THE_FIRST_OCCURRENCE_KEY,
			__( 'To only insert in the post content the first occurrence of each entity', "csp-plugin" ),
			[ $this, 'display_setting_boolean' ],
			$this->plugin_name,
			'ner_posts_settings',
			[
				'idKey'    => CspPluginConstants::CSP_PLUGIN_SETTINGS_NER_ONLY_ADD_THE_FIRST_OCCURRENCE_KEY,
				'shortKey' => CspPluginConstants::CSP_PLUGIN_SETTINGS_NER_ONLY_ADD_THE_FIRST_OCCURRENCE_SHORT_KEY,
			]
		);
	}

	/**
	 * Will render the admin api key field
	 *
	 * @return void
	 *
	 * @since   1.0.0
	 */
	public function display_setting_api_key() {
		include 'partials/settings/csp-plugin-admin-api-key-field.php';
	}

	/**
	 * Will render the admin NER URL format field
	 *
	 * @return void
	 *
	 * @since   1.0.0
	 */
	public function display_setting_ner_url_format( $args ) {
		$idKey    = ( isset( $args['idKey'] ) ? $args['idKey'] : null );
		$shortKey = ( isset( $args['shortKey'] ) ? $args['shortKey'] : null );

		include 'partials/settings/ner/csp-plugin-admin-ner-url-format-field.php';
	}

	/**
	 * Will render the admin nb results fields
	 *
	 * @return void
	 *
	 * @since   1.0.0
	 */
	public function display_setting_nb_results( $args ) {
		$idKey    = ( isset( $args['idKey'] ) ? $args['idKey'] : null );
		$shortKey = ( isset( $args['shortKey'] ) ? $args['shortKey'] : null );
		$default  = ( isset( $args['default'] ) ? $args['default'] : 3 );

		include 'partials/settings/common/csp-plugin-admin-nb-results-field.php';
	}

	/**
	 * Will render the admin hidden field
	 *
	 * @param $args
	 *
	 * @return void
	 */
	public function display_setting_hidden_field( $args ) {
		$idKey    = ( isset( $args['idKey'] ) ? $args['idKey'] : null );
		$shortKey = ( isset( $args['shortKey'] ) ? $args['shortKey'] : null );
		$default  = ( isset( $args['default'] ) ? $args['default'] : '' );

		include 'partials/settings/common/csp-plugin-admin-hidden-field.php';
	}

	/**
	 * Will render the admin date field for sync start date
	 *
	 * @return void
	 *
	 * @since   1.0.0
	 */
	public function display_setting_related_posts_sync_start_date( $args ) {
		$idKey    = ( isset( $args['idKey'] ) ? $args['idKey'] : null );
		$shortKey = ( isset( $args['shortKey'] ) ? $args['shortKey'] : null );
		$max      = ( isset( $args['max'] ) ? $args['max'] : null );

		include 'partials/settings/related-posts/csp-plugin-admin-sync-start-date.php';
	}

	/**
	 * Will render the admin title for related posts initial sync
	 *
	 * @return string
	 *
	 * @since   1.0.0
	 */
	public function display_setting_related_posts_sync_title() {

		include 'partials/settings/related-posts/csp-plugin-admin-sync-title.php';

		return render();
	}

	/**
	 * Will render the admin date fields
	 *
	 * @return string
	 *
	 * @since   1.0.0
	 */
	public function display_setting_related_posts_sync_button() {
		ob_start();

		include 'partials/settings/related-posts/csp-plugin-admin-sync-button.php';

		return ob_get_clean();
	}

	/**
	 * Will render an admin boolean field
	 *
	 * @return void
	 *
	 * @since   1.0.0
	 */
	public function display_setting_boolean( $args ) {
		$idKey    = ( isset( $args['idKey'] ) ? $args['idKey'] : null );
		$shortKey = ( isset( $args['shortKey'] ) ? $args['shortKey'] : null );

		include 'partials/settings/common/csp-plugin-admin-boolean.php';
	}

	/**
	 * Will render the admin csp taxonomy fields
	 *
	 * @return void
	 *
	 * @since   1.0.0
	 */
	public function display_setting_taxonomy( $args ) {
		$idKey    = ( isset( $args['idKey'] ) ? $args['idKey'] : null );
		$shortKey = ( isset( $args['shortKey'] ) ? $args['shortKey'] : null );

		include 'partials/settings/common/csp-plugin-admin-csp-taxonomy-field.php';
	}

	/**
	 * Will render the admin wp taxonomy fields
	 *
	 * @return void
	 *
	 * @since   1.0.0
	 */
	public function display_setting_wp_taxonomy( $args ) {
		$idKey    = ( isset( $args['idKey'] ) ? $args['idKey'] : null );
		$shortKey = ( isset( $args['shortKey'] ) ? $args['shortKey'] : null );

		include 'partials/settings/common/csp-plugin-admin-wp-taxonomy-field.php';
	}

	/**
	 * Will render the admin dictionary fields
	 *
	 * @return void
	 *
	 * @since   1.0.0
	 */
	public function display_setting_dictionary( $args ) {
		$idKey    = ( isset( $args['idKey'] ) ? $args['idKey'] : null );
		$shortKey = ( isset( $args['shortKey'] ) ? $args['shortKey'] : null );

		include 'partials/settings/common/csp-plugin-admin-dictionary-field.php';
	}

	/**
	 * Will render the admin threshold fields
	 *
	 * @return void
	 *
	 * @since   1.0.0
	 */
	public function display_setting_threshold( $args ) {
		$idKey    = ( isset( $args['idKey'] ) ? $args['idKey'] : null );
		$shortKey = ( isset( $args['shortKey'] ) ? $args['shortKey'] : null );

		include 'partials/settings/common/csp-plugin-admin-threshold-field.php';
	}

	/**
	 * Will render the admin api key field
	 *
	 * @return void
	 *
	 * @since   1.0.0
	 */
	public function display_section_text() {
		include 'partials/settings/csp-plugin-admin-settings-section-subtext.php';
	}

	/**
	 * @param $allowedFeatures string[]
	 *
	 * @return void
	 *
	 * @since 2.0.0
	 * @access private
	 */
	private function register_settings_if_allowed( $allowedFeatures ) {
		foreach ( $allowedFeatures as $allowedFeature ) {
			switch ( $allowedFeature ) {
				case CspPluginConstants::CSP_PLUGIN_API_RELATED_POSTS_FEATURE_NAME:
					$this->register_related_posts_settings();
					break;
				case CspPluginConstants::CSP_PLUGIN_API_CATEGORIZE_FEATURE_NAME:
					$this->register_categorize_settings();
					$this->register_tagging_settings();
					break;
				case CspPluginConstants::CSP_PLUGIN_API_NER_FEATURE_NAME:
					$this->register_ner_settings();
					break;
			}
		}
	}
}
