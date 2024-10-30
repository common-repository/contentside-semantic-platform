<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * The public-facing functionality of the plugin.
 *
 * @package    Csp_Plugin
 * @subpackage Csp_Plugin/public
 * @author     Thibault Schaeller <thibault.schaeller@contentside.com>
 */
class CspPluginPublic {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $plugin_name The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $version The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @param string $plugin_name The name of the plugin.
	 * @param string $version The version of this plugin.
	 *
	 * @since    1.0.0
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;

	}

	/**
	 * Registers the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		wp_enqueue_style( $this->plugin_name,
		                  plugin_dir_url( __FILE__ ) . 'css/csp-plugin-public.css',
		                  array(),
		                  $this->version,
		                  'all' );
	}

	/**
	 * Registers the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( $this->plugin_name,
		                   plugin_dir_url( __FILE__ ) . 'js/csp-plugin-public.js',
		                   array( 'jquery' ),
		                   $this->version,
		                   false );
	}

	/**
	 * Registers the plugin shortcuts
	 *
	 * @return void
	 *
	 * @since   1.0.0
	 */
	public function register_shortcodes() {
		add_shortcode(
			"csp_related_posts",
			[ $this, 'display_related_posts_shortcode' ]
		);
	}

	/**
	 * Renders the shortcode
	 *
	 * @return string
	 *
	 * @since   1.0.0
	 */
	public function display_related_posts_shortcode( $atts ) {
		ob_start();

		$atts = shortcode_atts( array(
			                        'id' => get_the_ID(),
		                        ),
		                        $atts );

		$post = get_post($atts['id']);

		include 'partials/shortcode/shortcode-related-posts.php';

		return ob_get_clean();
	}
}
