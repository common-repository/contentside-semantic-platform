<?php

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Csp_Plugin
 * @subpackage Csp_Plugin/includes
 * @author     Thibault Schaeller <thibault.schaeller@contentside.com>
 */
class CspPlugin {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      CspPluginLoader $loader Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string $plugin_name The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string $version The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'CSP_PLUGIN_VERSION' ) ) {
			$this->version = CSP_PLUGIN_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'csp-plugin';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
		$this->expose_globals();
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Csp_Plugin_Loader. Orchestrates the hooks of the plugin.
	 * - Csp_Plugin_i18n. Defines internationalization functionality.
	 * - Csp_Plugin_Admin. Defines all hooks for the admin area.
	 * - Csp_Plugin_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/CspPluginLoader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/CspPluginI18n.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'common/CspPluginUtils.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/CspPluginAdminSettings.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/CspPluginAdmin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/CspPluginPublic.php';

		/**
		 * We require the action-scheduler library to synchronize the posts with the CSP content repository
		 * in the background
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . '../vendor/woocommerce/action-scheduler/action-scheduler.php';

		/**
		 * Requires the data model
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'common/model/CspPluginNamedEntity.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'common/model/CspPluginNerResponse.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'common/model/CspPluginContextMetaEntity.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'common/model/CspPluginKeyMetaEntity.php';

		/**
		 * Requires the services
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/CspPluginSettingsDisplayManager.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'common/CSP/CspPluginCSPService.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'common/CSP/CspPluginMoreLikeThisService.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'common/CSP/CspPluginCategorizeService.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'common/CSP/CspPluginNerService.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'common/CspPluginCapabilities.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'common/CspPluginConstants.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'common/CspPluginRelatedPostManager.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'common/CspPluginCategoriesManager.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/api/CspPluginRelatedPostsRestAPI.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/api/CspPluginCategorizeAPI.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/api/CspPluginRelatedPostsAjaxAPI.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/api/CspPluginNerAPI.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/api/CspPluginSettingsAPI.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/api/CspPluginCapabilitiesAPI.php';

		$this->loader = new CspPluginLoader();
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Csp_Plugin_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new CspPluginI18n( $this->plugin_name );

		$this->loader->add_action( 'init', $plugin_i18n, 'load_plugin_textdomain' );
	}

	/**
	 * Register all the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {
		$moreLikeThisService = new CspPluginMoreLikeThisService();
		$relatedPostsManager = new CspPluginRelatedPostManager();

		$plugin_admin = new CspPluginAdmin( $this->get_plugin_name(),
		                                    $this->get_version(),
		                                    $this->loader,
		                                    $moreLikeThisService,
		                                    $relatedPostsManager
		);

		$plugin_admin->load_ajax_endpoints();

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );


		// We register the pre_update/update/add option settings hooks
		$this->loader->add_action(
			'pre_update_option_' . CspPluginConstants::CSP_PLUGIN_OPTIONS_KEY,
			$plugin_admin,
			'on_option_update',
			10,
			3
		);

		// Register the menu entry
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'register_menu' );

		// Register the action links for the plugin page
		$this->loader->add_action( 'plugin_action_links',
		                           $plugin_admin,
		                           'customize_admin_action_links',
		                           10,
		                           2 );

		// Will register the setting fields for the plugin
		$this->loader->add_action( 'admin_init', $plugin_admin, 'register_admin_settings' );

		// Adds the custom meta box to display the look alike results
		$this->loader->add_action( 'add_meta_boxes', $plugin_admin, 'register_meta_boxes' );

		// Async actions
		$this->loader->add_action( 'csp_plugin_synchronize_all_posts', $moreLikeThisService, 'save_all_posts' );
		$this->loader->add_action( 'csp_plugin_synchronize_posts', $moreLikeThisService, 'save_posts' );
		$this->loader->add_action( 'csp_plugin_synchronize_post', $moreLikeThisService, 'save_post' );
	}

	/**
	 * Register all the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new CspPluginPublic( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

		// Registers shortcodes
		$this->loader->add_action( 'init', $plugin_public, 'register_shortcodes' );
	}

	/**
	 * Exposes global functions such as get_csp_related_posts
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function expose_globals() {
		include_once plugin_dir_path( dirname( __FILE__ ) ) . 'global/csp-plugin-globals.php';
	}

	/**
	 * Run the loader to execute all the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @return    string    The name of the plugin.
	 * @since     1.0.0
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @return    Csp_Plugin_Loader    Orchestrates the hooks of the plugin.
	 * @since     1.0.0
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @return    string    The version number of the plugin.
	 * @since     1.0.0
	 */
	public function get_version() {
		return $this->version;
	}
}
