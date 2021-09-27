<?php
/**
 * @package smart-custom-fields
 * @author inc2734
 * @license GPL-2.0+
 */

/**
 * Smart_Custom_Fields_Rest_API class.
 */
class Smart_Custom_Fields_Rest_API {

	/**
	 * Post Type
	 *
	 * @var array
	 */
	protected $post_type = array( 'post', 'page' );

	/**
	 * __construct
	 */
	public function __construct() {
		add_action( 'rest_api_init', array( $this, 'register_rest_api_routes' ) );
	}

	/**
	 * Register routes
	 */
	public function register_rest_api_routes() {
		register_rest_route(
			SCF_Config::PREFIX . 'api/v2',
			'/search/posts',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_all_posts' ),
				'permission_callback' => function() {
					return current_user_can( 'edit_posts' );
				},
			)
		);
	}

	/**
	 * Get all posts and pages
	 */
	public function get_all_posts() {
		$all_posts = get_posts(
			array(
				'post_type'      => $this->get_post_type(),
				'post_status'    => 'publish',
				'orderby'        => 'date',
				'order'          => 'ASC',
				'posts_per_page' => -1, // all posts
			)
		);

		if ( $all_posts ) {
			$source = array();

			foreach ( $all_posts as $k => $post ) {
				$source[ $k ]['id']   = $post->ID;
				$source[ $k ]['text'] = $post->ID . ' - ' . $post->post_title;
			}
		}

		return $source;
	}

	/**
	 * Get posts type.
	 */
	public function get_post_type() {
		$post_type = $this->post_type;
		return apply_filters( SCF_Config::PREFIX . 'rest_api_post_type', $post_type );
	}
}
