<?php
/**
 * Smart_Custom_Fields_Cache
 * Version    : 1.0.0
 * Author     : inc2734
 * Created    : Mau 31, 2016
 * Modified   :
 * License    : GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
class Smart_Custom_Fields_Cache {

	/**
	 * Singleton instance
	 * @var Smart_Custom_Fields_Cache
	 */
	private static $instance;

	/**
	 * Getting data proccesses is heavy. So saved getted data to $cache.
	 * Using post_id as key.
	 * @var array
	 */
	protected $cache = array();

	/**
	 * Getting data proccesses is heavy. So saved getted data to $settings_posts_cache.
	 * Using post_type as key.
	 * @var array
	 */
	protected $settings_posts_cache = array();

	/**
	 * Getting data proccesses is heavy. So saved getted data to $settings_cache.
	 * Using post_type as key.
	 * @var array
	 */
	public $settings_cache = array();

	/**
	 * Getting data proccesses is heavy. So saved getted data to $repeat_multiple_data_cache.
	 * Using post_id as key.
	 * @var array
	 */
	protected $repeat_multiple_data_cache = array();

	private function __construct() {}

	public static function getInstance() {
		if ( !self::$instance ) {
			self::$instance = new Smart_Custom_Fields_Cache();
		}
		return self::$instance;
	}

	/**
	 * Clear all caches.
	 */
	public function clear_all_cache() {
		$this->clear_cache();
		$this->clear_settings_posts_cache();
		$this->clear_settings_cache();
		$this->clear_repeat_multiple_data_cache();
	}

	/**
	 * Saving to cache
	 *
	 * @param WP_Post|WP_User|WP_Term|stdClass $object
	 * @param string $name
	 * @param mixed $data
	 */
	public function save_cache( $object, $name, $data ) {
		$Meta      = new Smart_Custom_Fields_Meta( $object );
		$id        = $Meta->get_id();
		$type      = $Meta->get_type();
		$meta_type = $Meta->get_meta_type();
		if ( !empty( $id ) && !empty( $type ) && !empty( $meta_type ) ) {
			$this->cache[$meta_type . '_' . $type . '_' . $id][$name] = $data;
		}
	}

	/**
	 * Getting the cache
	 *
	 * @param WP_Post|WP_User|WP_Term|stdClass $object
	 * @param string $name
	 * @return mixed
	 */
	public function get_cache( $object, $name = null ) {
		$Meta      = new Smart_Custom_Fields_Meta( $object );
		$id        = $Meta->get_id();
		$type      = $Meta->get_type();
		$meta_type = $Meta->get_meta_type();
		if ( !empty( $id ) && !empty( $type ) && !empty( $meta_type ) ) {
			if ( is_null( $name ) ) {
				if ( isset( $this->cache[$meta_type . '_' . $type . '_' . $id] ) ) {
					return $this->cache[$meta_type . '_' . $type . '_' . $id];
				}
			} else {
				if ( isset( $this->cache[$meta_type . '_' . $type . '_' . $id][$name] ) ) {
					return $this->cache[$meta_type . '_' . $type . '_' . $id][$name];
				}
			}
		}
	}

	/**
	 * Clear caches
	 */
	public function clear_cache() {
		$this->cache = array();
	}

	/**
	 * Saving to cache that enabled custom field settings in the post type or the role or the term.
	 *
	 * @param WP_Post|WP_User|WP_Term|stdClass $object
	 * @param array $settings_posts
	 */
	public function save_settings_posts_cache( $object, $settings_posts ) {
		$Meta      = new Smart_Custom_Fields_Meta( $object );
		$type      = $Meta->get_type( false );
		$meta_type = $Meta->get_meta_type();
		$this->settings_posts_cache[$meta_type . '_' . $type] = $settings_posts;
	}

	/**
	 * Getting cache that enabled custom field settings in the post type or the role or the term.
	 *
	 * @param WP_Post|WP_User|WP_Term|stdClass $object
	 * @return array|null
	 */
	public function get_settings_posts_cache( $object ) {
		$Meta      = new Smart_Custom_Fields_Meta( $object );
		$type      = $Meta->get_type( false );
		$meta_type = $Meta->get_meta_type();
		if ( isset( $this->settings_posts_cache[$meta_type . '_' . $type] ) ) {
			return $this->settings_posts_cache[$meta_type . '_' . $type];
		}
	}

	/**
	 * Clear the $settings_posts_cache
	 */
	public function clear_settings_posts_cache() {
		$this->settings_posts_cache = array();
	}

	/**
	 * Saving the Setting object to cache
	 *
	 * @param int $settings_post_id
	 * @param WP_Post|WP_User|WP_Term|stdClass $object
	 * @param Smart_Custom_Fields_Setting $Setting
	 */
	public function save_settings_cache( $settings_post_id, $Setting, $object = null ) {
		if ( !is_null( $object ) ) {
			$Meta      = new Smart_Custom_Fields_Meta( $object );
			$id        = $Meta->get_id();
			$meta_type = $Meta->get_meta_type();
		}
		if ( !empty( $meta_type ) && !empty( $id ) ) {
			$this->settings_cache[$settings_post_id][$meta_type . '_' . $id] = $Setting;
		} else {
			$this->settings_cache[$settings_post_id][0] = $Setting;
		}
	}

	/**
	 * Getting the Setting object cache
	 * If there isn't the custom field settings ... null
	 * If there is custom field settings
	 *     If there is no data for the specified $ meta_type + $id
	 *         There is a thing of the General ... Smart_Custom_Fields_Setting
	 *         There isn't a thing of the General ... false
	 *     If there the data for the specified $meta_type + $id ... Smart_Custom_Fields_Setting
	 *
	 * @param int $settings_post_id
	 * @param WP_Post|WP_User|WP_Term|stdClass $object
	 * @return Smart_Custom_Fields_Setting|false|null
	 */
	public function get_settings_cache( $settings_post_id, $object = null ) {
		if ( !is_null( $object ) ) {
			$Meta      = new Smart_Custom_Fields_Meta( $object );
			$id        = $Meta->get_id();
			$meta_type = $Meta->get_meta_type();
		}

		if ( isset( $this->settings_cache[$settings_post_id] ) ) {
			$settings_cache = $this->settings_cache[$settings_post_id];
			if ( !empty( $id ) && !empty( $meta_type ) && isset( $settings_cache[$meta_type . '_' . $id] ) ) {
				return $settings_cache[$meta_type . '_' . $id];
			}
			if ( isset( $settings_cache[0] ) ) {
				return $settings_cache[0];
			}
			return false;
		}
	}

	/**
	 * Clear the $settings_cache
	 */
	public function clear_settings_cache() {
		$this->settings_cache = array();
	}

	/**
	 * Saving the delimited identification data of the repeated multi-value items to cache
	 *
	 * @param WP_Post|WP_User|WP_Term|stdClass $object
	 * @param mixed $repeat_multiple_data
	 */
	public function save_repeat_multiple_data_cache( $object, $repeat_multiple_data ) {
		$Meta      = new Smart_Custom_Fields_Meta( $object );
		$id        = $Meta->get_id();
		$type      = $Meta->get_type();
		$meta_type = $Meta->get_meta_type();
		if ( !empty( $id ) && !empty( $type ) && !empty( $meta_type ) ) {
			$this->repeat_multiple_data_cache[$meta_type . '_' . $type . '_' . $id] = $repeat_multiple_data;
		}
	}

	/**
	 * Getting delimited identification data of the repeated multi-value items from cache
	 *
	 * @param WP_Post|WP_User|WP_Term|stdClass $object
	 * @return mixed
	 */
	public function get_repeat_multiple_data_cache( $object ) {
		$Meta      = new Smart_Custom_Fields_Meta( $object );
		$id        = $Meta->get_id();
		$type      = $Meta->get_type();
		$meta_type = $Meta->get_meta_type();
		if ( !empty( $id ) && !empty( $type ) ) {
			if ( isset( $this->repeat_multiple_data_cache[$meta_type . '_' . $type . '_' . $id] ) ) {
				return $this->repeat_multiple_data_cache[$meta_type . '_' . $type . '_' . $id];
			}
		}
	}

	/**
	 * Clear delimited identification data of the repeated multi-value items cache
	 */
	public function clear_repeat_multiple_data_cache() {
		$this->repeat_multiple_data_cache = array();
	}
}
