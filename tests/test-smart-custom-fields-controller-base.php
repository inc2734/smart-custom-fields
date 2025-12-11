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
	 * @var string
	 */
	protected $related_posts_test_field_name;

	/**
	 * @var array|string|null
	 */
	protected $related_posts_test_post_type;

	/**
	 * Set up.
	 */
	public function set_up() {
		parent::set_up();

		// The post for custom fields
		$this->post_id = $this->factory->post->create(
			array(
				'post_type'   => 'post',
				'post_status' => 'publish',
			)
		);

		// The auto draft post for custom fields
		$this->new_post_id = $this->factory->post->create(
			array(
				'post_type'   => 'post',
				'post_status' => 'auto-draft',
			)
		);

		add_filter( 'smart-cf-register-fields', array( $this, '_register' ), 10, 4 );

		require_once plugin_dir_path( __FILE__ ) . '../classes/controller/class.controller-base.php';
		$this->Controller = new Smart_Custom_Fields_Controller_Base();

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
	 * @group get_multiple_data_field_value
	 */
	public function test_get_multiple_data_field_value() {
		// When $index is null
		$object = get_post( $this->post_id );
		$Field  = SCF::get_field( $object, 'checkbox-has-default' );
		$this->assertSame( array( 'a' ), $this->Controller->get_multiple_data_field_value( $object, $Field, null ) );
		$Field = SCF::get_field( $object, 'checkbox' );
		$this->assertSame( array(), $this->Controller->get_multiple_data_field_value( $object, $Field, null ) );

		// When isn't saved meta data. At that time ,$index is ignored.
		$object = get_post( $this->new_post_id );
		$Field  = SCF::get_field( $object, 'checkbox-has-default' );
		$this->assertSame( array( 'a' ), $this->Controller->get_multiple_data_field_value( $object, $Field, 0 ) );
		$Field = SCF::get_field( $object, 'checkbox' );
		$this->assertSame( array(), $this->Controller->get_multiple_data_field_value( $object, $Field, 0 ) );
	}

	/**
	 * @group get_multiple_data_field_value
	 */
	public function test_get_multiple_data_field_value__saved() {
		$object = get_post( $this->post_id );
		$Meta   = new Smart_Custom_Fields_Meta( $object );
		$Field  = SCF::get_field( $object, 'checkbox-has-default' );
		$Meta->add( 'checkbox-has-default', 'a' );
		$Meta->add( 'checkbox-has-default', 'b' );
		$this->assertSame( array( 'a', 'b' ), $this->Controller->get_multiple_data_field_value( $object, $Field, 0 ) );
		$Field = SCF::get_field( $object, 'checkbox' );
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
		$Field = SCF::get_field( $object, 'text' );
		$this->assertSame( '', $this->Controller->get_single_data_field_value( $object, $Field, null ) );

		// When isn't saved meta data. At that time ,$index is ignored.
		$object = get_post( $this->new_post_id );
		$Field  = SCF::get_field( $object, 'text-has-default' );
		$this->assertSame( 'a', $this->Controller->get_single_data_field_value( $object, $Field, 0 ) );
		$Field = SCF::get_field( $object, 'text' );
		$this->assertSame( '', $this->Controller->get_single_data_field_value( $object, $Field, 0 ) );
	}

	/**
	 * @group get_single_data_field_value
	 */
	public function test_get_single_data_field_value__saved() {
		$object = get_post( $this->post_id );
		$Meta   = new Smart_Custom_Fields_Meta( $object );
		$Field  = SCF::get_field( $object, 'text-has-default' );
		$Meta->add( 'text-has-default', 'b' );
		$Meta->add( 'text-has-default', 'c' );
		$this->assertSame( 'b', $this->Controller->get_single_data_field_value( $object, $Field, 0 ) );
		$this->assertSame( 'c', $this->Controller->get_single_data_field_value( $object, $Field, 1 ) );

		$Field = SCF::get_field( $object, 'text' );
		$Meta->add( 'text', 'b' );
		$Meta->add( 'text', 'c' );
		$this->assertSame( 'b', $this->Controller->get_single_data_field_value( $object, $Field, 0 ) );
		$this->assertSame( 'c', $this->Controller->get_single_data_field_value( $object, $Field, 1 ) );
	}

	/**
	 * @group get_field
	 * @group issue-110
	 * Test for issue #110: Fatal error when post-type is not specified for related posts field
	 */
	public function test_get_field__related_posts_without_post_type() {
		add_filter( 'smart-cf-register-fields', array( $this, '_register_related_posts_for_issue_110' ), 10, 4 );
		$this->related_posts_test_field_name = 'relation-without-post-type';
		$this->related_posts_test_post_type  = null;

		$Cache = Smart_Custom_Fields_Cache::get_instance();
		$Cache->flush();

		$object = get_post( $this->post_id );
		$Field  = SCF::get_field( $object, 'relation-without-post-type' );

		$this->assertNotNull( $Field );
		$result = $Field->get_field( 0, array() );
		$this->assertIsString( $result );
	}

	/**
	 * @group get_field
	 * @group issue-110
	 * Test for issue #110: Related posts field with post-type as array (should pass)
	 */
	public function test_get_field__related_posts_with_post_type_array() {
		add_filter( 'smart-cf-register-fields', array( $this, '_register_related_posts_for_issue_110' ), 10, 4 );
		$this->related_posts_test_field_name = 'relation-with-post-type-array';
		$this->related_posts_test_post_type  = array( 'post' );

		$Cache = Smart_Custom_Fields_Cache::get_instance();
		$Cache->flush();

		$object = get_post( $this->post_id );
		$Field  = SCF::get_field( $object, 'relation-with-post-type-array' );

		$this->assertNotNull( $Field );
		$result = $Field->get_field( 0, array() );
		$this->assertIsString( $result );
	}

	/**
	 * @group get_field
	 * @group issue-110
	 * Test for issue #110: Fatal error when post-type is string for related posts field
	 */
	public function test_get_field__related_posts_with_post_type_string() {
		add_filter( 'smart-cf-register-fields', array( $this, '_register_related_posts_for_issue_110' ), 10, 4 );
		$this->related_posts_test_field_name = 'relation-with-post-type-string';
		$this->related_posts_test_post_type  = 'post';

		$Cache = Smart_Custom_Fields_Cache::get_instance();
		$Cache->flush();

		$object = get_post( $this->post_id );
		$Field  = SCF::get_field( $object, 'relation-with-post-type-string' );

		$this->assertNotNull( $Field );
		$result = $Field->get_field( 0, array() );
		$this->assertIsString( $result );
	}

	/**
	 * @group get_field
	 * @group issue-110
	 * Test for issue #110: Fatal error when post-type is empty string for related posts field
	 */
	public function test_get_field__related_posts_with_post_type_empty_string() {
		add_filter( 'smart-cf-register-fields', array( $this, '_register_related_posts_for_issue_110' ), 10, 4 );
		$this->related_posts_test_field_name = 'relation-with-post-type-empty-string';
		$this->related_posts_test_post_type  = '';

		$Cache = Smart_Custom_Fields_Cache::get_instance();
		$Cache->flush();

		$object = get_post( $this->post_id );
		$Field  = SCF::get_field( $object, 'relation-with-post-type-empty-string' );

		$this->assertNotNull( $Field );
		$result = $Field->get_field( 0, array() );
		$this->assertIsString( $result );
	}

	/**
	 * Register custom fields with related posts field for issue #110 test
	 *
	 * @param array  $settings  Array of Smart_Custom_Fields_Setting object.
	 * @param string $type      Post type or Role.
	 * @param int    $id        Post ID or User ID.
	 * @param string $meta_type post or user.
	 */
	public function _register_related_posts_for_issue_110( $settings, $type, $id, $meta_type ) {
		if (
			( 'post' === $type && $id === $this->post_id ) ||
			( 'post' === $type && $id === $this->new_post_id )
		) {
			$field_name = $this->related_posts_test_field_name;
			$post_type  = $this->related_posts_test_post_type;

			$Setting      = SCF::add_setting( 'id-' . $field_name, 'Related Posts Test for Issue #110' );
			$field_config = array(
				'name'    => $field_name,
				'label'   => 'Related Posts Test',
				'type'    => 'relation',
				'default' => 'a',
			);

			if ( null !== $post_type ) {
				$field_config['post-type'] = $post_type;
			}

			$Setting->add_group( $field_name, false, array( $field_config ) );
			$settings[ $Setting->get_id() ] = $Setting;
		}
		return $settings;
	}

	/**
	 * Register custom fields using filter hook
	 *
	 * @param array  $settings  Array of Smart_Custom_Fields_Setting object.
	 * @param string $type      Post type or Role.
	 * @param int    $id        Post ID or User ID.
	 * @param string $meta_type post or user.
	 */
	public function _register( $settings, $type, $id, $meta_type ) {
		if (
			( 'post' === $type && $id === $this->post_id ) ||
			( 'post' === $type && $id === $this->new_post_id ) ||
			( 'editor' === $type ) ||
			( 'category' === $type ) ||
			( 'option' === $meta_type && 'menu-slug' === $id )
		) {
			$Setting = SCF::add_setting( 'id-1', 'Register Test' );
			$Setting->add_group(
				0,
				false,
				array(
					array(
						'name'  => 'text',
						'label' => 'text',
						'type'  => 'text',
					),
				)
			);
			$Setting->add_group(
				'text-has-default',
				false,
				array(
					array(
						'name'    => 'text-has-default',
						'label'   => 'text has default',
						'type'    => 'text',
						'default' => 'a',
					),
				)
			);
			$Setting->add_group(
				'checkbox',
				false,
				array(
					array(
						'name'    => 'checkbox',
						'label'   => 'checkbox field',
						'type'    => 'check',
						'choices' => array( 'a', 'b', 'c' ),
					),
				)
			);
			$Setting->add_group(
				'checkbox-has-default',
				false,
				array(
					array(
						'name'    => 'checkbox-has-default',
						'label'   => 'checkbox has default',
						'type'    => 'check',
						'choices' => array( 'a', 'b', 'c' ),
						'default' => array( 'a' ),
					),
				)
			);
			$Setting->add_group(
				'checkbox-key-value',
				false,
				array(
					array(
						'name'    => 'checkbox-key-value',
						'label'   => 'checkbox key value',
						'type'    => 'check',
						'choices' => array(
							'a' => 'apple',
							'b' => 'banana',
							'c' => 'carrot',
						),
						'default' => array( 'a' ),
					),
				)
			);
			$Setting->add_group(
				'group',
				true,
				array(
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
				)
			);
			$settings[ $Setting->get_id() ] = $Setting;
		}
		return $settings;
	}
}
