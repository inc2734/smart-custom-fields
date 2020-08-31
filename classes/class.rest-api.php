<?php
/**
 * Smart_Custom_Fields_Rest_API
 * Version    : 1.0.1
 * Author     : robssanches
 * Created    : July 14, 2018
 * Modified   : July 22, 2020
 * License    : GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
class Smart_Custom_Fields_Rest_API {

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
				'methods'  => 'GET',
				'callback' => array( $this, 'get_all_posts' ),
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
				'post_type'      => array( 'post', 'page' ),
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
}
