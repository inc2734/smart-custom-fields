<?php
/**
 * @package smart-custom-fields
 * @author inc2734
 * @license GPL-2.0+
 */

/**
 * Smart_Custom_Fields_Controller_Editor class.
 */
class Smart_Custom_Fields_Controller_Editor extends Smart_Custom_Fields_Controller_Base {

	/**
	 * __construct
	 */
	public function __construct() {
		parent::__construct();
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ), 10, 2 );
		add_action( 'save_post', array( $this, 'save_post' ) );
	}

	/**
	 * Displaying custom fields in post edit page.
	 *
	 * @param string  $post_type Post type.
	 * @param WP_Post $post      WP_Post object.
	 */
	public function add_meta_boxes( $post_type, $post ) {
		$settings = SCF::get_settings( $post );
		foreach ( $settings as $setting ) {
			add_meta_box(
				SCF_Config::PREFIX . 'custom-field-' . $setting->get_id(),
				$setting->get_title(),
				array( $this, 'display_meta_box' ),
				$post_type,
				'normal',
				'default',
				$setting->get_groups()
			);
		}
	}

	/**
	 * Saving meta data from custom fields in post edit page.
	 *
	 * @param int $post_id Post id.
	 */
	public function save_post( $post_id ) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		if ( wp_is_post_autosave( $post_id ) || wp_is_post_revision( $post_id ) ) {
			return;
		}
		if ( ! isset( $_POST[ SCF_Config::NAME ] ) ) {
			return;
		}

		$this->save( $_POST, get_post( $post_id ) );
	}
}
