<?php
class Smart_Custom_Fields_Controller_Base_Test extends WP_UnitTestCase {

	/**
	 * @var int
	 */
	protected $new_post_id;

	/**
	 * @var int
	 */
	protected $post_id;

	/**
	 * setUp
	 */
	public function setUp() {
		parent::setUp();

		// The post for custom fields
		$this->post_id = $this->factory->post->create( array(
			'post_type'   => 'post',
			'post_status' => 'publish',
		) );

		// The auto draft post for custom fields
		$this->new_post_id = $this->factory->post->create( array(
			'post_type'   => 'post',
			'post_status' => 'auto-draft',
		) );

		add_filter( 'smart-cf-register-fields', array( $this, '_register' ), 10, 4 );

		require_once plugin_dir_path( __FILE__ ) . '../classes/controller/class.controller-base.php';
		$this->Controller = new Smart_Custom_Fields_Controller_Base();

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
	 * @group get_multiple_data_field_value
	 */
	public function test_get_multiple_data_field_value() {
		// When $index is null
		$object = get_post( $this->post_id );
		$Field  = SCF::get_field( $object, 'checkbox-has-default' );
		$this->assertSame( array( 'a' ), $this->Controller->get_multiple_data_field_value( $object, $Field, null ) );
		$Field  = SCF::get_field( $object, 'checkbox' );
		$this->assertSame( array(), $this->Controller->get_multiple_data_field_value( $object, $Field, null ) );

		// When isn't saved meta data. At that time ,$index is ignored.
		$object = get_post( $this->new_post_id );
		$Field  = SCF::get_field( $object, 'checkbox-has-default' );
		$this->assertSame( array( 'a' ), $this->Controller->get_multiple_data_field_value( $object, $Field, 0 ) );
		$Field  = SCF::get_field( $object, 'checkbox' );
		$this->assertSame( array(), $this->Controller->get_multiple_data_field_value( $object, $Field, 0 ) );
	}

	/**
	 * @group get_multiple_data_field_value
	 */
	public function test_get_multiple_data_field_value__saved() {
		$object = get_post( $this->post_id );
		$Meta = new Smart_Custom_Fields_Meta( $object );
		$Field  = SCF::get_field( $object, 'checkbox-has-default' );
		$Meta->add( 'checkbox-has-default', 'a' );
		$Meta->add( 'checkbox-has-default', 'b' );
		$this->assertSame( array( 'a', 'b' ), $this->Controller->get_multiple_data_field_value( $object, $Field, 0 ) );
		$Field  = SCF::get_field( $object, 'checkbox' );
		$Meta->add( 'checkbox', 'a' );
		$Meta->add( 'checkbox', 'b' );
		$this->assertSame( array( 'a', 'b' ), $this->Controller->get_multiple_data_field_value( $object, $Field, 0 ) );
	}

	/**
	 * @group get_multiple_data_field_value
	 */
	public function test_get_multiple_data_field_value__saved_multi() {
		$object = get_post( $this->post_id );
		$Meta   = new Smart_Custom_Fields_Meta( $object );
		$Field  = SCF::get_field( $object, 'repeat-checkbox' );
		$POST   = array(
			SCF_Config::NAME => array(
				'repeat-checkbox' => array(
					array(),
					array( 'a', 'b' ),
					array( 'b', 'c' ),
				),
			),
		);
		$Meta->save( $POST );
		$this->assertSame( array(), $this->Controller->get_multiple_data_field_value( $object, $Field, 0 ) );
		$this->assertSame( array( 'a', 'b' ), $this->Controller->get_multiple_data_field_value( $object, $Field, 1 ) );
	}

	/**
	 * @group get_single_data_field_value
	 */
	public function test_get_single_data_field_value() {
		// When $index is null
		$object = get_post( $this->post_id );
		$Field  = SCF::get_field( $object, 'text-has-default' );
		$this->assertSame( 'a', $this->Controller->get_single_data_field_value( $object, $Field, null ) );
		$Field  = SCF::get_field( $object, 'text' );
		$this->assertSame( '', $this->Controller->get_single_data_field_value( $object, $Field, null ) );

		// When isn't saved meta data. At that time ,$index is ignored.
		$object = get_post( $this->new_post_id );
		$Field  = SCF::get_field( $object, 'text-has-default' );
		$this->assertSame( 'a', $this->Controller->get_single_data_field_value( $object, $Field, 0 ) );
		$Field  = SCF::get_field( $object, 'text' );
		$this->assertSame( '', $this->Controller->get_single_data_field_value( $object, $Field, 0 ) );
	}

	/**
	 * @group get_single_data_field_value
	 */
	public function test_get_single_data_field_value__saved() {
		$object = get_post( $this->post_id );
		$Meta = new Smart_Custom_Fields_Meta( $object );
		$Field  = SCF::get_field( $object, 'text-has-default' );
		$Meta->add( 'text-has-default', 'b' );
		$Meta->add( 'text-has-default', 'c' );
		$this->assertSame( 'b', $this->Controller->get_single_data_field_value( $object, $Field, 0 ) );
		$this->assertSame( 'c', $this->Controller->get_single_data_field_value( $object, $Field, 1 ) );

		$Field  = SCF::get_field( $object, 'text' );
		$Meta->add( 'text', 'b' );
		$Meta->add( 'text', 'c' );
		$this->assertSame( 'b', $this->Controller->get_single_data_field_value( $object, $Field, 0 ) );
		$this->assertSame( 'c', $this->Controller->get_single_data_field_value( $object, $Field, 1 ) );
	}

	/**
	 * Register custom fields using filter hook
	 */
	public function _register( $settings, $type, $id, $meta_type ) {
		if (
			( $type === 'post' && $id === $this->post_id ) ||
			( $type === 'post' && $id === $this->new_post_id ) ||
			( $type === 'editor' ) ||
			( $type === 'category' ) ||
			( $meta_type === 'option' && $id === 'menu-slug' )
		) {
			$Setting = SCF::add_setting( 'id-1', 'Register Test' );
			$Setting->add_group( 0, false, array(
				array(
					'name'  => 'text',
					'label' => 'text',
					'type'  => 'text',
				),
			) );
			$Setting->add_group( 'text-has-default', false, array(
				array(
					'name'    => 'text-has-default',
					'label'   => 'text has default',
					'type'    => 'text',
					'default' => 'a',
				),
			) );
			$Setting->add_group( 'checkbox', false, array(
				array(
					'name'    => 'checkbox',
					'label'   => 'checkbox field',
					'type'    => 'check',
					'choices' => array( 'a', 'b', 'c' ),
				),
			) );
			$Setting->add_group( 'checkbox-has-default', false, array(
				array(
					'name'    => 'checkbox-has-default',
					'label'   => 'checkbox has default',
					'type'    => 'check',
					'choices' => array( 'a', 'b', 'c' ),
					'default' => array( 'a' ),
				),
			) );
			$Setting->add_group( 'checkbox-key-value', false, array(
				array(
					'name'    => 'checkbox-key-value',
					'label'   => 'checkbox key value',
					'type'    => 'check',
					'choices' => array( 'a' => 'apple', 'b' => 'banana', 'c' => 'carrot' ),
					'default' => array( 'a' ),
				),
			) );
			$Setting->add_group( 'group', true, array(
				array(
					'name'  => 'repeat-text',
					'label' => 'repeat text',
					'type'  => 'text',
				),
				array(
					'name'    => 'repeat-checkbox',
					'label'   => 'repeat checkbox',
					'type'    => 'check',
					'choices' => array( 'a', 'b', 'c' ),
				),
			) );
			$settings[] = $Setting;
		}
		return $settings;
	}
}
