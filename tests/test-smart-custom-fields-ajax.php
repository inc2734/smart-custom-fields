<?php
class Smart_Custom_Fields_Ajax_Test extends WP_UnitTestCase {

	/**
	 * @var Smart_Custom_Fields_Ajax
	 */
	protected $Ajax;

	/**
	 * setUp
	 */
	public function setUp() {
		parent::setUp();
		$this->Ajax = new Smart_Custom_Fields_Ajax();

		$Cache = Smart_Custom_Fields_Cache::getInstance();
		$Cache->flush();
	}

	/**
	 * tearDown
	 */
	public function tearDown() {
		parent::tearDown();
		$Cache = Smart_Custom_Fields_Cache::getInstance();
		$Cache->flush();
	}

	/**
	 * @group delete_term
	 */
	public function test_delete_term() {
		$taxonomy = 'category';
		$term_id  = $this->factory->term->create( array( 'taxonomy' => $taxonomy ) );
		$term     = get_term( $term_id, $taxonomy );
		$Meta = new Smart_Custom_Fields_Meta( $term );

		if ( !_get_meta_table( $Meta->get_meta_type() ) ) {
			$Meta->add( 'text', 'text' );
			$this->Ajax->delete_term( $term_id, '', $taxonomy, $term );
			$this->assertSame( array(), $Meta->get( 'text' ) );
		}
	}

	/**
	 * Register custom fields using filter hook
	 */
	public function _register( $settings, $type, $id, $meta_type ) {
		if ( type === 'category' ) {
			$Setting = SCF::add_setting( 'id-1', 'Register Test' );
			$Setting->add_group( 0, false, array(
				array(
					'name'  => 'text',
					'label' => 'text field',
					'type'  => 'text',
				),
			) );
			$settings['id-1'] = $Setting;
		}
		return $settings;
	}
}
