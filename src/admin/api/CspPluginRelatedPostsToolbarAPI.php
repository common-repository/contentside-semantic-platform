<?php

/**
 * The API endpoints for the related posts toolbar actions
 *
 * This is used to register all API endpoints used by the plugin for the related posts toolbar actions.
 *
 * @since      1.0.0
 * @package    Csp_Plugin
 * @subpackage Csp_Plugin/admin/api
 * @author     Thibault Schaeller <thibault.schaeller@contentside.com>
 */
class CspPluginRelatedPostsToolbarAPI {
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
		add_action( 'rest_api_init', [ $this, 'register_related_posts_actions' ] );
	}

	public function register_related_posts_actions() {
		register_rest_route(
			$this->namespace,
			'/related-posts',
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'get_related_posts_list' ),
				'permission_callback' => function () {
					return current_user_can( 'edit_posts' );
				},
			]
		);

		register_rest_route(
			$this->namespace,
			'/related-posts/nb-already-synced',
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_nb_synced_posts' ),
				'permission_callback' => function () {
					return current_user_can( 'edit_posts' );
				},
			]
		);
	}

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return WP_Error|WP_HTTP_Response|WP_REST_Response
	 * @since   1.0.0
	 * @access  public
	 */
	public function get_related_posts_list( $request ) {
		$data = json_decode( $request->get_body(), true );

		$dataKeys = [ "postId", "title", "introduction", "content" ];
		foreach ( $dataKeys as $data_key ) {
			if ( ! array_key_exists( $data_key, $data ) || ! $data[ $data_key ] ) {
				return rest_ensure_response( new WP_Error( 400, "Invalid format : $data_key is missing or empty" ) );
			}
		}

		$moreLikeThisService = new CspPluginMoreLikeThisService();
		try {
			$ids = $moreLikeThisService->discover_look_alike_for_content_title_and_intro( $data["postId"],
			                                                                              $data["content"],
			                                                                              $data["title"],
			                                                                              $data["introduction"] );
		} catch ( Exception $exception ) {
			return rest_ensure_response( new WP_Error( 500, "An error occurred." ) );
		}

		$posts = [];
		if ( count( $ids ) > 0 ) {
			$posts = get_posts( [ "include" => $ids, "suppress_filters" => false ] );
		}

		$permalinks = [];
		/** @var WP_Post $post */
		foreach ( $posts as $post ) {
			$permalinks[ $post->ID ] = get_permalink( $post );
		}

		return rest_ensure_response( [ "posts" => $posts, "permalinks" => $permalinks ] );
	}

	/**
	 * @return WP_Error|WP_HTTP_Response|WP_REST_Response
	 * @since   1.0.0
	 * @access  public
	 */
	public function get_nb_synced_posts() {
		$options       = get_option( CspPluginConstants::CSP_PLUGIN_OPTIONS_KEY );
		$nbPostsSynced = 0;
		if ( isset( $options[ CspPluginConstants::CSP_PLUGIN_RELATED_POSTS_SYNC_COUNT ] ) ) {
			$nbPostsSynced = $options[ CspPluginConstants::CSP_PLUGIN_RELATED_POSTS_SYNC_COUNT ];
		}

		return rest_ensure_response( [ "nbPostSynced" => $nbPostsSynced ] );
	}
}