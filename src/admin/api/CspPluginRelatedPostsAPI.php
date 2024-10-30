<?php

/**
 * The API endpoints for the related posts feature
 *
 * This is used to register all API endpoints used by the plugin for the related posts feature.
 *
 *
 * @since      1.0.0
 * @package    Csp_Plugin
 * @subpackage Csp_Plugin/admin/api
 * @author     Thibault Schaeller <thibault.schaeller@contentside.com>
 */
class CspPluginRelatedPostsAPI {
	/**
	 * The MoreLikeThisService
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
	 * The cspPluginAdmin
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      CspPluginAdmin $cspPluginAdmin
	 */
	private $cspPluginAdmin;

	/**
	 * @since    1.0.0
	 * @access   private
	 * @var      $loader
	 */
	private $loader;

	/**
	 * @param $moreLikeThisService
	 * @param $relatedPostManager
	 * @param $plugin_admin
	 * @param $loader
	 */
	public function __construct( $moreLikeThisService, $relatedPostManager, $plugin_admin, $loader ) {
		$this->moreLikeThisService = $moreLikeThisService;
		$this->relatedPostManager  = $relatedPostManager;
		$this->cspPluginAdmin      = $plugin_admin;
		$this->loader              = $loader;
	}

	public function run() {
		// Adds the start_synchronization ajax handler
		$this->loader->add_action( 'wp_ajax_start_synchronization', $this, 'start_synchronization' );
		// Adds the remove_related_post ajax handler
		$this->loader->add_action( 'wp_ajax_remove_related_post', $this, 'remove_related_post' );
		// Endpoint for posts look alike discovery
		$this->loader->add_action( 'wp_ajax_discover_post', $this, 'discover_post_look_alike' );
		// Endpoint for posts look alike removal
		$this->loader->add_action( 'wp_ajax_remove_all_related_posts', $this, 'remove_all_related_posts' );
		// Endpoint for posts paragraph look alike discovery
		$this->loader->add_action( 'wp_ajax_discover_for_paragraph',
		                           $this,
		                           'discover_paragraph_related_posts' );
		// On Post upsert
		$this->loader->add_action( 'wp_insert_post', $this, 'on_insert_post', 10, 3 );
	}

	/**
	 * Will start the posts synchronization process in the background
	 *
	 * @return void
	 *
	 * @since   1.0.0
	 */
	public function start_synchronization() {
		$user = wp_get_current_user();
		if (
			! isset( $_REQUEST['nonce'] ) ||
			! wp_verify_nonce( $_REQUEST['nonce'], 'csp-plugin_start_synchronization' ) ||
			! in_array( 'administrator', $user->roles )
		) {
			wp_send_json_error( "Permission denied.", 403 );

			return;
		}

		// We mark the last sync date
		$options                                                                = get_option( CspPluginConstants::CSP_PLUGIN_OPTIONS_KEY );
		$options[ CspPluginConstants::CSP_PLUGIN_SETTINGS_RELATED_POSTS_LAST_SYNC_DATE_SHORT_KEY ] = ( new DateTime() )->format( 'Y-m-d H:i:s' );
		update_option( CspPluginConstants::CSP_PLUGIN_OPTIONS_KEY, $options );

		// Start the synchronization as a background process
		$synchId = as_enqueue_async_action( 'csp_plugin_synchronize_all_posts' );

		wp_send_json_success( [ 'synchId' => $synchId ] );
	}

	/**
	 * Will discover the posts look alike results from the CSP
	 *
	 * @return void
	 *
	 * @throws Exception
	 * @since   1.0.0
	 */
	public function discover_post_look_alike() {
		if (
			! isset( $_REQUEST['nonce'] ) ||
			! wp_verify_nonce( $_REQUEST['nonce'], 'csp-plugin_discover_post_look_alike' ) ||
			! current_user_can( CspPluginCapabilities::CSP_PLUGIN_CAPABILITY_LOOKALIKE_DISCOVER )
		) {
			wp_send_json_error( "Permission denied.", 403 );

			return;
		}

		if ( ! isset( $_REQUEST['postId'] ) || null === ( $postId = sanitize_title( $_REQUEST['postId'] ) ) ) {
			wp_send_json_error( "Missing parameter : postId", 400 );

			return;
		}

		$post = get_post( $postId );
		if ( ! $post ) {
			wp_send_json_error( "Post $postId not found.", 404 );

			return;
		}

		$posts = $this->moreLikeThisService->discover_post_look_alike( $post );

		ob_start();
		$this->cspPluginAdmin->display_related_posts_meta_box_content( $post );
		$metaBoxContent = ob_get_clean();

		wp_send_json_success( [ 'posts' => $posts, 'metaBoxContent' => $metaBoxContent ] );
	}

	/**
	 * Will remove the given post from the related posts list
	 *
	 * @return void
	 *
	 * @throws Exception
	 * @since   1.0.0
	 */
	public function remove_related_post() {
		if (
			! isset( $_REQUEST['nonce'] ) ||
			! wp_verify_nonce( $_REQUEST['nonce'], 'csp-plugin_remove_related_post' ) ||
			! current_user_can( CspPluginCapabilities::CSP_PLUGIN_CAPABILITY_LOOKALIKE_DISCOVER )
		) {
			wp_send_json_error( "Permission denied.", 403 );

			return;
		}

		if ( ! isset( $_REQUEST['postId'] ) || null === ( $postId = sanitize_title( $_REQUEST['postId'] ) ) ) {
			wp_send_json_error( "Missing parameter : postId", 400 );

			return;
		}

		if ( ! isset( $_REQUEST['relatedPost'] ) || null === ( $relatedPost = sanitize_title( $_REQUEST['relatedPost'] ) ) ) {
			wp_send_json_error( "Missing parameter : relatedPost", 400 );

			return;
		}

		$post = get_post( $postId );
		if ( ! $post ) {
			wp_send_json_error( "Post {$postId} not found.", 404 );

			return;
		}

		$posts = $this->relatedPostManager->removeRelatedPost( $post, $relatedPost );

		ob_start();
		$this->cspPluginAdmin->display_related_posts_meta_box_content( $post );
		$metaBoxContent = ob_get_clean();

		wp_send_json_success( [ 'posts' => $posts, 'metaBoxContent' => $metaBoxContent ] );
	}

	/**
	 * Will remove all related posts from the given post
	 *
	 * @return void
	 *
	 * @throws Exception
	 * @since   1.0.0
	 */
	public function remove_all_related_posts() {
		if (
			! isset( $_REQUEST['nonce'] ) ||
			! wp_verify_nonce( $_REQUEST['nonce'], 'csp-plugin_remove_all_related_posts' ) ||
			! current_user_can( CspPluginCapabilities::CSP_PLUGIN_CAPABILITY_LOOKALIKE_DISCOVER )
		) {
			wp_send_json_error( "Permission denied.", 403 );

			return;
		}

		if ( ! isset( $_REQUEST['postId'] ) || null === ( $postId = sanitize_title( $_REQUEST['postId'] ) ) ) {
			wp_send_json_error( "Missing parameter : postId", 400 );

			return;
		}

		$post = get_post( $postId );
		if ( ! $post ) {
			wp_send_json_error( "Post {$postId} not found.", 404 );

			return;
		}

		$posts = $this->relatedPostManager->removeAllRelatedPost( $post );

		ob_start();
		$this->cspPluginAdmin->display_related_posts_meta_box_content( $post );
		$metaBoxContent = ob_get_clean();

		wp_send_json_success( [ 'posts' => $posts, 'metaBoxContent' => $metaBoxContent ] );
	}

	/**
	 * Will remove the given post from the related posts list
	 *
	 * @return void
	 *
	 * @throws Exception
	 * @since   1.0.0
	 */
	public function discover_paragraph_related_posts() {
		if ( ! current_user_can( CspPluginCapabilities::CSP_PLUGIN_CAPABILITY_LOOKALIKE_INSERT ) ) {
			wp_send_json_error( "Permission denied.", 403 );

			return;
		}

		if ( ! isset( $_REQUEST['postId'] ) || null === ( $postId = sanitize_title( $_REQUEST['postId'] ) ) ) {
			wp_send_json_error( "Missing parameter : postId", 400 );

			return;
		}

		if ( ! isset( $_REQUEST['paragraphText'] ) || null === ( $paragraphText = sanitize_title( $_REQUEST['paragraphText'] ) ) ) {
			wp_send_json_error( "Missing parameter : paragraphText", 400 );

			return;
		}

		$post = get_post( $postId );
		if ( ! $post ) {
			wp_send_json_error( "Post {$postId} not found.", 404 );

			return;
		}

		$ids = $this->moreLikeThisService->discover_look_alike_for_content_title_and_intro(
			$post->ID,
			$paragraphText,
			$post->post_title,
			$post->post_excerpt ?: $post->post_title
		);

		$posts = [];
		if ( ! empty( $ids ) ) {
			$posts = get_posts( [ "include" => $ids, "suppress_filters" => false ] );
		}

		wp_send_json_success( [ 'posts' => $posts ] );
	}

	/**
	 * Hook on post upsert to synchronize with the CSP
	 *
	 * @return void
	 * @throws Exception
	 * @var WP_Post $post
	 * @var bool $update
	 *
	 * @var int $post_id
	 * @since   1.0.0
	 */
	public function on_insert_post( $post_id, $post, $update ) {
		if ( wp_is_post_revision( $post_id ) ) {
			return;
		}

		// We want to synchronize only posts that are published
		if ( $post->post_status !== 'publish' ) {
			return;
		}

		// If the post just got created, we increment the sync count
		if ( ! $update ) {
			$options = get_option( CspPluginConstants::CSP_PLUGIN_OPTIONS_KEY );
			if ( ! isset( $options[ CspPluginConstants::CSP_PLUGIN_RELATED_POSTS_SYNC_COUNT ] ) ) {
				$options[ CspPluginConstants::CSP_PLUGIN_RELATED_POSTS_SYNC_COUNT ] = 0;
			}
			$options[ CspPluginConstants::CSP_PLUGIN_RELATED_POSTS_SYNC_COUNT ] += 1;
			update_option( CspPluginConstants::CSP_PLUGIN_OPTIONS_KEY, $options );
		}

		try {
			$this->moreLikeThisService->save_post( $post );
		} catch ( Exception $exception ) {
			$exceptionMessage = $exception->getMessage();
			error_log( "An exception occurred while synchronizing the post with the CSP : $exceptionMessage" );
		}
	}
}