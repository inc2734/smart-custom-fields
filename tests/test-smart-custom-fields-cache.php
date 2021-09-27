<?php
class Smart_Custom_Fields_Cache_Test extends WP_UnitTestCase {

	/**
	 * @var Smart_Custom_Fields_Cache
	 */
	protected $Cache;


	/**
	 * @var int
	 */
	protected $settings_post_id;

	/**
	 * @var int
	 */
	protected $post_id;

	/**
	 * @var int
	 */
	protected $new_post_id;

	/**
	 * @var int
	 */
	protected $user_id;

	/**
	 * @var int
	 */
	protected $term_id;

	/**
	 * @var string
	 */
	protected $menu_slug;

	/**
	 * setUp
	 */
	public function setUp() {
		parent::setUp();

		// The post id of ettings page
		$this->settings_post_id = $this->factory->post->create( array(
			'post_type'   => SCF_Config::NAME,
			'post_status' => 'publish',
		) );

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

		// The draft post for custom fields
		$this->draft_post_id = $this->factory->post->create( array(
			'post_type'   => 'post',
			'post_status' => 'draft',
		) );

		// The user for custom fields
		$this->user_id = $this->factory->user->create( array( 'role' => 'editor' ) );

		// The term for custom fields
		$this->term_id = $this->factory->term->create( array( 'taxonomy' => 'category' ) );

		// The option page for custom fields
		$this->menu_slug = SCF::add_options_page( 'page title', 'menu title', 'manage_options', 'menu-slug' );

		add_filter( 'smart-cf-register-fields', array( $this, '_register' ), 10, 4 );

		$this->Cache = Smart_Custom_Fields_Cache::get_instance();
		$this->Cache->flush();
	}

	/**
	 * tearDown
	 */
	public function tearDown() {
		parent::tearDown();
		$this->Cache->flush();
	}

	/**
	 * @group save_settings_posts
	 */
	public function test_save_settings_posts() {
		$settings_post = get_post( $this->settings_post_id );
		$object = get_post( $this->post_id );
		$this->Cache->save_settings_posts( $object, array( $settings_post ) );
		$this->assertSame( array( $settings_post ), $this->Cache->get_settings_posts( $object ) );
	}

	/**
	 * @group get_settings_posts
	 */
	public function test_get_settings_posts() {
		$settings_post = get_post( $this->settings_post_id );

		// post
		$object = get_post( $this->post_id );
		$this->assertNull( $this->Cache->get_settings_posts( $object ) );
		$this->Cache->save_settings_posts( $object, array( $settings_post ) );
		$this->assertSame( array( $settings_post ), $this->Cache->get_settings_posts( $object ) );

		// user
		$object = get_userdata( $this->user_id );
		$this->assertNull( $this->Cache->get_settings_posts( $object ) );
		$this->Cache->save_settings_posts( $object, array( $settings_post ) );
		$this->assertSame( array( $settings_post ), $this->Cache->get_settings_posts( $object ) );

		// term
		$object = get_term( $this->term_id, 'category' );
		$this->assertNull( $this->Cache->get_settings_posts( $object ) );
		$this->Cache->save_settings_posts( $object, array( $settings_post ) );
		$this->assertSame( array( $settings_post ), $this->Cache->get_settings_posts( $object ) );

		// options page
		$object = SCF::generate_option_object( $this->menu_slug );
		$this->assertNull( $this->Cache->get_settings_posts( $object ) );
		$this->Cache->save_settings_posts( $object, array( $settings_post ) );
		$this->assertSame( array( $settings_post ), $this->Cache->get_settings_posts( $object ) );
	}

	/**
	 * @group clear_settings_posts
	 */
	public function test_clear_settings_posts() {
		$settings_post = get_post( $this->settings_post_id );
		$object = get_post( $this->post_id );
		$this->Cache->save_settings_posts( $object, array( $settings_post ) );
		$this->Cache->clear_settings_posts();
		$this->assertNull( $this->Cache->get_settings_posts( $object ) );
	}

	/**
	 * @group save_settings
	 */
	public function test_save_settings() {
		// post
		$settings_post_id = 1;
		$Setting = new Smart_Custom_Fields_Setting( $settings_post_id, 'dummy' );
		$object = get_post( $this->post_id );
		$this->Cache->save_settings( $settings_post_id, $Setting, $object );
		$this->assertEquals( $Setting, $this->Cache->get_settings( $settings_post_id, $object ) );

		// user
		$settings_post_id = 2;
		$Setting = new Smart_Custom_Fields_Setting( $settings_post_id, 'dummy' );
		$object = get_userdata( $this->user_id );
		$this->Cache->save_settings( $settings_post_id, $Setting, $object );
		$this->assertEquals( $Setting, $this->Cache->get_settings( $settings_post_id, $object ) );

		// user
		$settings_post_id = 3;
		$Setting = new Smart_Custom_Fields_Setting( $settings_post_id, 'dummy' );
		$object = get_term( $this->term_id, 'category' );
		$this->Cache->save_settings( $settings_post_id, $Setting, $object );
		$this->assertEquals( $Setting, $this->Cache->get_settings( $settings_post_id, $object ) );

		// options page
		$settings_post_id = 4;
		$Setting = new Smart_Custom_Fields_Setting( $settings_post_id, 'dummy' );
		$object = SCF::generate_option_object( $this->menu_slug );
		$this->Cache->save_settings( $settings_post_id, $Setting, $object );
		$this->assertEquals( $Setting, $this->Cache->get_settings( $settings_post_id, $object ) );
	}

	/**
	 * @group get_settings
	 */
	public function test_get_settings() {
		$settings_post_id = 1;
		$Setting = new Smart_Custom_Fields_Setting( $settings_post_id, 'dummy' );

		// When isn't existed
		$this->assertNull( $this->Cache->get_settings( $settings_post_id ) );

		// When existed
		$object = get_post( $this->post_id );
		$this->Cache->save_settings( $settings_post_id, $Setting, $object );
		$this->assertFalse( $this->Cache->get_settings( $settings_post_id ) );
		$this->assertSame( $Setting, $this->Cache->get_settings( $settings_post_id, $object ) );
	}

	/**
	 * @group clear_settings
	 */
	public function test_clear_settings() {
		$settings_post_id = 1;
		$Setting = new Smart_Custom_Fields_Setting( $settings_post_id, 'dummy' );
		$object = get_post( $this->post_id );
		$this->Cache->save_settings( $settings_post_id, $Setting, $object );
		$this->Cache->clear_settings();
		$this->assertNull( $this->Cache->get_settings( $settings_post_id, $object ) );
	}

	/**
	 * @group save_repeat_multiple_data
	 */
	public function test_save_repeat_multiple_data() {
		$object = get_post( $this->post_id );
		$repeat_multiple_data = array( 'dummy' );
		$this->Cache->save_repeat_multiple_data( $object, $repeat_multiple_data );
		$this->assertSame( array( 'dummy' ), $this->Cache->get_repeat_multiple_data( $object ) );
	}

	/**
	 * @group get_repeat_multiple_data
	 */
	public function test_get_repeat_multiple_data() {
		$object = get_post( null );
		$this->assertNull( $this->Cache->get_repeat_multiple_data( $object ) );

		$object = get_post( $this->post_id );
		$repeat_multiple_data = array( 'dummy' );
		$this->Cache->save_repeat_multiple_data( $object, $repeat_multiple_data );
		$this->assertSame( array( 'dummy' ), $this->Cache->get_repeat_multiple_data( $object ) );
	}

	/**
	 * @group clear_repeat_multiple_data
	 */
	public function test_clear_repeat_multiple_data() {
		$object = get_post( $this->post_id );
		$repeat_multiple_data = array( 'dummy' );
		$this->Cache->save_repeat_multiple_data( $object, $repeat_multiple_data );
		$this->Cache->clear_repeat_multiple_data();
		$this->assertNull( $this->Cache->get_repeat_multiple_data( $object ) );
	}

	/**
	 * @group save_meta
	 */
	public function test_save_meta() {
		$object = get_post( null );
		$this->Cache->save_meta( $object, 'text', 'text' );
		$this->assertNull( $this->Cache->get_meta( $object, 'text' ) );

		$object = get_post( $this->post_id );
		$this->Cache->save_meta( $object, 'text', 'text' );
		$this->assertSame( 'text', $this->Cache->get_meta( $object, 'text' ) );
	}

	/**
	 * @group get_meta
	 */
	public function test_get_meta() {
		$object = get_post( null );
		$this->Cache->save_meta( $object, 'text', 'text' );
		$this->assertNull( $this->Cache->get_meta( $object ) );
		$this->assertNull( $this->Cache->get_meta( $object, 'text' ) );

		$object = get_post( $this->post_id );
		$this->Cache->save_meta( $object, 'text', 'text' );
		$this->assertSame( array( 'text' => 'text' ), $this->Cache->get_meta( $object ) );
		$this->assertSame( 'text', $this->Cache->get_meta( $object, 'text' ) );
	}

	/**
	 * @group clear_meta
	 */
	public function test_clear_meta() {
		$object = get_post( $this->post_id );
		$this->Cache->save_meta( $object, 'text', 'text' );
		$this->Cache->clear_meta();
		$this->assertNull( $this->Cache->get_meta( $object, 'text' ) );
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
			$Setting = SCF::add_setting( 'id', 'Register Test' );
			$Setting->add_group( 0, false, array(
				array(
					'name'  => 'text',
					'label' => 'text',
					'type'  => 'text',
				),
			) );
			$settings[$Setting->get_id()] = $Setting;
		}
		return $settings;
	}
}
