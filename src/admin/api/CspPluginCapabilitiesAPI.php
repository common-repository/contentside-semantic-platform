<?php

class CspPluginCapabilitiesAPI {
	/**
	 * The API version
	 *
	 * @since    1.3.0
	 * @access   private
	 * @var      string $version The API version.
	 */
	private $version;

	/**
	 * The name of the plugin
	 *
	 * @since    1.3.0
	 * @access   private
	 * @var      string $plugin_name The name of the plugin.
	 */
	private $plugin_name;

	/**
	 * The API namespace
	 *
	 * @since    1.3.0
	 * @access   private
	 * @var      string $namespace The API namespace.
	 */
	private $namespace;

	/**
	 * @param $plugin_name
	 */
	public function __construct( $plugin_name ) {
		$this->version     = '1';
		$this->plugin_name = $plugin_name;
		$this->namespace   = $plugin_name . '/v' . $this->version;
	}

	public function run() {
		add_action( 'rest_api_init', [ $this, 'register_ner_actions' ] );
	}

	public function register_ner_actions() {
		register_rest_route(
			$this->namespace,
			'/capabilities/current-user',
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_current_user_capabilities' ),
				'permission_callback' => function () {
					return current_user_can( 'edit_posts' );
				},
			]
		);
	}

	/**
	 * Returns the available dictionaries to select in settings
	 *
	 * @return WP_Error|WP_HTTP_Response|WP_REST_Response
	 * @throws Exception
	 * @since   1.3.0
	 */
	public function get_current_user_capabilities() {
		return rest_ensure_response( CspPluginCapabilities::get_current_user_csp_capabilities() );
	}
}