<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Csp_Plugin
 * @subpackage Csp_Plugin/admin
 * @author     Thibault Schaeller <thibault.schaeller@contentside.com>
 */
class CspPluginAdmin {

	/**
	 * The ID of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $plugin_name The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $version The current version of this plugin.
	 */
	private $version;

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      CspPluginLoader $loader Maintains and registers all hooks for the plugin.
	 */
	private $loader;

	/**
	 * The MoreLikeService
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      CspPluginMoreLikeThisService $moreLikeThisService
	 */
	private $moreLikeThisService;

	/**
	 * The RelatedPostManager
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      CspPluginRelatedPostManager $relatedPostsManager
	 */
	private $relatedPostManager;

	/**
	 * @param string $plugin_name
	 * @param string $version
	 * @param CspPluginLoader $loader
	 * @param CspPluginMoreLikeThisService $moreLikeThisService
	 * @param CspPluginRelatedPostManager $relatedPostManager
	 */
	public function __construct(
		$plugin_name,
		$version,
		$loader,
		$moreLikeThisService,
		$relatedPostManager
	) {
		$this->plugin_name         = $plugin_name;
		$this->version             = $version;
		$this->loader              = $loader;
		$this->moreLikeThisService = $moreLikeThisService;
		$this->relatedPostManager  = $relatedPostManager;
	}

	/**
	 * Initialize the class and set its properties.
	 *
	 * @param string $plugin_name The name of this plugin.
	 * @param string $version The version of this plugin.
	 *
	 * @since    1.0.0
	 */


	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		wp_enqueue_style(
			$this->plugin_name,
			plugin_dir_url( __FILE__ ) . 'css/csp-plugin-admin.css',
			array(),
			$this->version,
			'all'
		);

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		$scriptDependencies = require_once __DIR__ . '/../../build/index.asset.php';
		wp_enqueue_script(
			$this->plugin_name,
			plugin_dir_url( __FILE__ ) . '../../build/index.js',
			$scriptDependencies['dependencies'],
			$this->version,
			true
		);

		// We enable our own i18n tokens in the scripts
		wp_set_script_translations(
			$this->plugin_name,
			$this->plugin_name,
			realpath( plugin_dir_path( __FILE__ ) . '/../../languages' )
		);

		// Exposes WPURLS object to the scripts
		wp_localize_script( $this->plugin_name, 'WPURLS', [ 'adminUrl' => get_admin_url() ] );
	}

	/**
	 * Registers the plugin menu entry
	 *
	 * @return void
	 *
	 * @since   1.0.0
	 */
	public function register_menu() {
		add_management_page(
			'Semantic Platform',
			'Semantic Platform',
			'manage_options',
			$this->plugin_name,
			[ $this, 'display_admin_page' ]
		);
	}

	/**
	 * Registers the plugin action links
	 *
	 * @param $links
	 * @param $plugin_file
	 *
	 * @return string[]
	 * @since 1.2.0
	 */
	public function customize_admin_action_links( $links, $plugin_file ) {
		if ( plugin_basename( CSP_PLUGIN_FILE ) !== $plugin_file ) {
			return $links;
		}

		$configuration_link = add_query_arg( [ 'page' => $this->plugin_name ], admin_url( "tools.php" ) );
		$settings_link      = array(
			'<a href="' . esc_url( $configuration_link ) . '">' . esc_html__( 'Settings', 'csp-plugin' ) . '</a>',
		);

		return array_merge( $settings_link, $links );
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
		$cspPluginSettings = new CspPluginAdminSettings( $this->plugin_name );
		$cspPluginSettings->register_admin_settings();
	}

	/**
	 * Will render custom meta boxes
	 *
	 * @return void
	 *
	 * @throws Exception
	 * @since   1.0.0
	 */
	public function register_meta_boxes() {
		if (
			! CspPluginUtils::isApiKeyValid()
			|| ! CspPluginUtils::isFeatureAllowed( CspPluginConstants::CSP_PLUGIN_API_RELATED_POSTS_FEATURE_NAME )
			|| ! current_user_can( CspPluginCapabilities::CSP_PLUGIN_CAPABILITY_LOOKALIKE_DISCOVER )
		) {
			return;
		}
		add_meta_box(
			CspPluginConstants::CSP_PLUGIN_RELATED_POSTS_META_BOX_ID,
			__( "Related posts", "csp-plugin" ),
			[ $this, 'display_related_posts_meta_box_content' ],
			"post"
		);
	}

	/**
	 * @param $old_value
	 * @param $value
	 * @param $option
	 *
	 * @return array|mixed
	 * @throws Exception
	 * @since 2.0.0
	 */
	public function on_option_update( $value, $old_value, $option ) {
		// No new updates applied to the csp-plugin option
		if ( ! is_array( $value ) ) {
			return $old_value;
		}

		// If there's a sync date already saved, we don't want to overwrite it
		if ( isset( $old_value[ CspPluginConstants::CSP_PLUGIN_SETTINGS_RELATED_POSTS_LAST_SYNC_DATE_KEY ] ) ) {
			$value[ CspPluginConstants::CSP_PLUGIN_SETTINGS_RELATED_POSTS_LAST_SYNC_DATE_KEY ] = $old_value[ CspPluginConstants::CSP_PLUGIN_SETTINGS_RELATED_POSTS_LAST_SYNC_DATE_KEY ];
		}

		$newApiKey = $value[ CspPluginConstants::CSP_PLUGIN_SETTINGS_API_KEY_KEY ];
		$oldApiKey = $old_value[ CspPluginConstants::CSP_PLUGIN_SETTINGS_API_KEY_KEY ];
		if ( $newApiKey === $oldApiKey ) {
			$value[ CspPluginConstants::CSP_PLUGIN_SETTINGS_API_KEY_VALID_KEY ] = $old_value[ CspPluginConstants::CSP_PLUGIN_SETTINGS_API_KEY_VALID_KEY ];

			return $value;
		}

		// Checking all the sites for the same API key
		if ( is_multisite() ) {
			$sites      = get_sites();
			$allApiKeys = [];
			foreach ( $sites as $site ) {
				switch_to_blog( $site->blog_id );
				$options = get_option( CspPluginConstants::CSP_PLUGIN_OPTIONS_KEY );
				if ( is_array( $options ) && array_key_exists( CspPluginConstants::CSP_PLUGIN_SETTINGS_API_KEY_KEY,
				                                               $options ) ) {
					$allApiKeys[ $site->blog_id ] = $options[ CspPluginConstants::CSP_PLUGIN_SETTINGS_API_KEY_KEY ];
				}
				restore_current_blog();
			}

			if ( in_array( $newApiKey, $allApiKeys ) ) {
				$value[ CspPluginConstants::CSP_PLUGIN_SETTINGS_API_KEY_VALID_KEY ] = false;

				return $value;
			}
		}

		$allowed_features = [];
		try {
			$csp_service = new CspPluginCSPService();
			// get_meta will fail if the API key is invalid
			$meta = $csp_service->get_meta( $newApiKey );
			// Else it will contain the allowed features (among other infos)
			$allowed_features = $meta->get_context()->get_modules();
			$is_api_key_valid = $meta->isValid();
		} catch ( Exception $e ) {
			$is_api_key_valid = false;
			error_log( $e->getMessage() );
		}

		update_option( CspPluginConstants::CSP_PLUGIN_API_ALLOWED_FEATURES_KEY, $allowed_features );

		$value[ CspPluginConstants::CSP_PLUGIN_SETTINGS_API_KEY_VALID_KEY ] = $is_api_key_valid;

		return $value;
	}

	/**
	 * Loads all the endpoints (REST and Ajax)
	 *
	 *
	 * @return void
	 *
	 * @throws Exception
	 * @since   1.0.0
	 */
	public function load_ajax_endpoints() {
		$settingsAPI = new CspPluginSettingsAPI( $this->plugin_name );
		$settingsAPI->run();

		$capabilitiesAPI = new CspPluginCapabilitiesAPI( $this->plugin_name );
		$capabilitiesAPI->run();

		if ( ! CspPluginUtils::isApiKeyValid() ) {
			return;
		}

		if ( CspPluginUtils::isFeatureAllowed( CspPluginConstants::CSP_PLUGIN_API_RELATED_POSTS_FEATURE_NAME ) ) {

			$relatedPostApi = new CspPluginRelatedPostsAjaxAPI( $this->moreLikeThisService,
			                                                    $this->relatedPostManager,
			                                                    $this,
			                                                    $this->loader );

			$relatedPostApi->run();

			$toolbarAPI = new CspPluginRelatedPostsRestAPI(
				$this->plugin_name,
				$this->relatedPostManager,
				$this
			);
			$toolbarAPI->run();
		}

		if ( CspPluginUtils::isFeatureAllowed( CspPluginConstants::CSP_PLUGIN_API_NER_FEATURE_NAME ) ) {
			$nerApi = new CspPluginNerAPI( $this->plugin_name );
			$nerApi->run();
		}

		if ( CspPluginUtils::isFeatureAllowed( CspPluginConstants::CSP_PLUGIN_API_CATEGORIZE_FEATURE_NAME ) ) {
			$sidebarAPI = new CspPluginCategorizeAPI( $this->plugin_name );
			$sidebarAPI->run();
		}
	}

	/**
	 * Will render the admin configuration page
	 *
	 * @return void
	 *
	 * @since   1.0.0
	 */
	public function display_admin_page() {
		include 'partials/csp-plugin-admin-display.php';
	}

	/**
	 * Will render the related-posts custom meta box
	 *
	 * @return void
	 *
	 * @since   1.0.0
	 */
	public function display_related_posts_meta_box_content( $post ) {
		include 'partials/meta-box/related-posts/meta-box-content.php';
	}
}
