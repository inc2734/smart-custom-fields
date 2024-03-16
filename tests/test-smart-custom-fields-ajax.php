<?php
class Smart_Custom_Fields_Ajax_Test extends WP_UnitTestCase {

	/**
	 * @var Smart_Custom_Fields_Ajax
	 */
	protected $Ajax;

	/**
	 * Set up.
	 */
	public function set_up() {
		parent::set_up();
		$this->Ajax = new Smart_Custom_Fields_Ajax();

		$Cache = Smart_Custom_Fields_Cache::get_instance();
		$Cache->flush();
	}

	/**
	 * Tear down.
	 */
	public function tear_down() {
		parent::tear_down();
		$Cache = Smart_Custom_Fields_Cache::get_instance();
		$Cache->flush();
	}

	/**
	 * @group delete_term
	 */
	public function test_delete_term() {
		$taxonomy = 'category';
		$term_id  = $this->factory->term->create( array( 'taxonomy' => $taxonomy ) );
		$term     = get_term( $term_id, $taxonomy );
		$Meta     = new Smart_Custom_Fields_Meta( $term );

		$Meta->add( 'text', 'text' );
		$this->Ajax->delete_term( $term_id, '', $taxonomy, $term );
		$this->assertSame( array(), $Meta->get( 'text' ) );
	}

	/**
	 * Register custom fields using filter hook.
	 *
	 * @param array  $settings  Array of Smart_Custom_Fields_Setting object.
	 * @param string $type      Post type or Role.
	 * @param int    $id        Post ID or User ID.
	 * @param string $meta_type post or user.
	 */
	public function _register( $settings, $type, $id, $meta_type ) {
		if ( type === 'category' ) {
			$Setting = SCF::add_setting( 'id-1', 'Register Test' );
			$Setting->add_group(
				0,
				false,
				array(
					array(
						'name'  => 'text',
						'label' => 'text field',
						'type'  => 'text',
					),
				)
			);
			$settings[ $Setting->get_id() ] = $Setting;
		}
		return $settings;
	}
}
