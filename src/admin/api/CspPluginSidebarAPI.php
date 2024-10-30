<?php

/**
 * The API endpoints for the categorize sidebar actions
 *
 * This is used to register all API endpoints used by the plugin for the categorize sidebar actions.
 *
 * @since      1.0.0
 * @package    Csp_Plugin
 * @subpackage Csp_Plugin/admin/api
 * @author     Thibault Schaeller <thibault.schaeller@contentside.com>
 */
class CspPluginSidebarAPI {
	/**
	 * The API version
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $version The API version.
	 */
	private $version;

	/**
	 * The API namespace
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $namespace The API namespace.
	 */
	private $namespace;

	public function __construct( $plugin_name ) {
		$this->version   = '1';
		$this->namespace = $plugin_name . '/v' . $this->version;
	}

	public function run() {
		add_action( 'rest_api_init', [ $this, 'register_categorize_actions' ] );
	}

	public function register_categorize_actions() {
		register_rest_route(
			$this->namespace,
			'/categorize',
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ $this, 'get_categories_list' ],
				'args'                => [ 'asTags' => false ],
				'permission_callback' => function () {
					return current_user_can( 'edit_posts' );
				},
			]
		);

		register_rest_route(
			$this->namespace,
			'/tag',
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ $this, 'get_tags_list' ],
				'args'                => [ 'asTags' => true ],
				'permission_callback' => function () {
					return current_user_can( 'edit_posts' );
				},
			]
		);
	}

	/**
	 * @param $request
	 *
	 * @return WP_Error|WP_HTTP_Response|WP_REST_Response
	 * @since   1.2.0
	 */
	public function get_categories_list( $request ) {
		return $this->get_term_list( $request );
	}

	/**
	 * @param $request
	 *
	 * @return WP_Error|WP_HTTP_Response|WP_REST_Response
	 * @since   1.2.0
	 */
	public function get_tags_list( $request ) {
		return $this->get_term_list( $request, true );
	}

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return WP_Error|WP_HTTP_Response|WP_REST_Response
	 * @since   1.0.0
	 * @access  public
	 */
	private function get_term_list( $request, $asTags = false ) {
		$data = json_decode( $request->get_body(), true );

		$dataKeys = [ "postId", "title", "introduction", "content" ];
		foreach ( $dataKeys as $data_key ) {
			if ( ! array_key_exists( $data_key, $data ) || ! $data[ $data_key ] ) {
				return rest_ensure_response( new WP_Error( 400, "Invalid format : ${data_key} is missing or empty" ) );
			}
		}

		$categorizeService = new CspPluginCategorizeService();
		try {
			$taxonomyElements = $categorizeService->categorize_for_content_title_and_intro( $data["content"],
			                                                                                $data["title"],
			                                                                                $data["introduction"],
			                                                                                $asTags
			);
		} catch ( Exception $exception ) {
			return rest_ensure_response( new WP_Error( 500, "An error occurred." ) );
		}

		return rest_ensure_response( [ "taxonomyElements" => $taxonomyElements ] );
	}
}