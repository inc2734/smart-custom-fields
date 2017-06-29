<?php
class SCF_Test extends WP_UnitTestCase {

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
		get_userdata( $this->user_id )->add_role( 'subscriber' );

		// The term for custom fields
		$this->term_id = $this->factory->term->create( array( 'taxonomy' => 'category' ) );

		// The option page for custom fields
		$this->menu_slug = SCF::add_options_page( 'page title', 'menu title', 'manage_options', 'menu-slug' );

		add_filter( 'smart-cf-register-fields', array( $this, '_register' ), 10, 4 );

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
	 * @group get
	 */
	public function test_get() {
		// When the post id isn't get
		$this->assertNull( SCF::get( 'text', false ) );

		// When post isn't saved, return the default value.
		$this->assertSame( '', SCF::get( 'text', $this->new_post_id ) );
		$this->assertSame( array(), SCF::get( 'checkbox' , $this->new_post_id ) );
		$this->assertSame( 'a', SCF::get( 'text-has-default', $this->new_post_id ) );
		$this->assertSame( array( 'a' ), SCF::get( 'checkbox-has-default', $this->new_post_id ) );
		$this->assertSame( array( 'a' ), SCF::get( 'checkbox-key-value', $this->new_post_id ) );

		// When meta data isn't saved, return the default value.
		$this->assertSame( '', SCF::get( 'text', $this->post_id ) );
		$this->assertSame( array(), SCF::get( 'checkbox' , $this->post_id ) );
		$this->assertSame( 'a', SCF::get( 'text-has-default', $this->post_id ) );
		$this->assertSame( array( 'a' ), SCF::get( 'checkbox-has-default', $this->post_id ) );
		$this->assertSame( array( 'a' ), SCF::get( 'checkbox-key-value', $this->post_id ) );

		// When non exist fields
		$this->assertNull( SCF::get( 'not-exist', $this->post_id ) );
	}

	/**
	 * @group get
	 */
	public function test_get__meta_data_saved() {
		update_post_meta( $this->post_id, 'text', 'text' );
		$this->assertSame( 'text', SCF::get( 'text', $this->post_id ) );
		update_post_meta( $this->post_id, 'checkbox', 'not-exist-key' );
		$this->assertSame( array( 'not-exist-key' ), SCF::get( 'checkbox', $this->post_id ) );
		$this->assertSame( 'a', SCF::get( 'text-has-default', $this->post_id ) );
		$this->assertSame( array( 'a' ), SCF::get( 'checkbox-has-default', $this->post_id ) );
		$this->assertSame( array( 'a' ), SCF::get( 'checkbox-key-value', $this->post_id ) );

		// In repeatable group, non multi-value field
		add_post_meta( $this->post_id, 'repeat-text', 'a' );
		add_post_meta( $this->post_id, 'repeat-text', 'b' );
		$this->assertEquals(
			array( 'a', 'b' ),
			SCF::get( 'repeat-text', $this->post_id )
		);

		// In repeatable group, multi-value field
		update_post_meta( $this->post_id, SCF_Config::PREFIX . 'repeat-multiple-data', array(
			'repeat-checkbox' => array( 1, 2 ),
		) );
		add_post_meta( $this->post_id, 'repeat-checkbox', 'a' );
		add_post_meta( $this->post_id, 'repeat-checkbox', 'b' );
		add_post_meta( $this->post_id, 'repeat-checkbox', 'c' );
		$this->assertEquals(
			array(
				array( 'a' ),
				array( 'b', 'c' ),
			),
			SCF::get( 'repeat-checkbox', $this->post_id )
		);
	}

	/**
	 * @group gets
	 */
	public function test_gets() {
		// When the post id isn't get
		$this->assertNull( SCF::gets( false ) );

		// When post isn't saved, return the default value.
		$this->assertSame( array(
			'text'                 => '',
			'text-has-default'     => 'a',
			'checkbox'             => array(),
			'checkbox-has-default' => array( 'a' ),
			'checkbox-key-value'   => array( 'a' ),
			'group'                => array(
				array(
					'repeat-text'     => '',
					'repeat-checkbox' => array(),
				),
			),
		), SCF::gets( $this->new_post_id ) );

		// When meta data isn't saved, return the default value.
		$this->assertSame( array(
			'text'                 => '',
			'text-has-default'     => 'a',
			'checkbox'             => array(),
			'checkbox-has-default' => array( 'a' ),
			'checkbox-key-value'   => array( 'a' ),
			'group'                => array(
				array(
					'repeat-text'     => '',
					'repeat-checkbox' => array(),
				),
			),
		), SCF::gets( $this->post_id ) );
	}

	/**
	 * @group gets
	 */
	public function test_gets__meta_data_saved() {
		update_post_meta( $this->post_id, 'text', 'text' );
		update_post_meta( $this->post_id, 'checkbox', 'not-exist-key' );
		add_post_meta( $this->post_id, 'repeat-text', 'a' );
		add_post_meta( $this->post_id, 'repeat-text', 'b' );
		update_post_meta( $this->post_id, SCF_Config::PREFIX . 'repeat-multiple-data', array(
			'repeat-checkbox' => array( 1, 2 ),
		) );
		add_post_meta( $this->post_id, 'repeat-checkbox', 'a' );
		add_post_meta( $this->post_id, 'repeat-checkbox', 'b' );
		add_post_meta( $this->post_id, 'repeat-checkbox', 'c' );

		$this->assertSame( array(
			'text'                 => 'text',
			'text-has-default'     => 'a',
			'checkbox'             => array( 'not-exist-key' ),
			'checkbox-has-default' => array( 'a' ),
			'checkbox-key-value'   => array( 'a' ),
			'group'                => array(
				array(
					'repeat-text'     => 'a',
					'repeat-checkbox' => array( 'a' ),
				),
				array(
					'repeat-text'     => 'b',
					'repeat-checkbox' => array( 'b', 'c' ),
				),
			),
		), SCF::gets( $this->post_id ) );
	}

	/**
	 * @group get_user_meta
	 */
	public function test_get_user_meta() {
		// When the user id isn't get
		$this->assertNull( SCF::get_user_meta( false, 'text' ) );
		$this->assertNull( SCF::get_user_meta( false ) );

		// When meta data isn't saved, return the default value.
		$this->assertSame( '', SCF::get_user_meta( $this->user_id, 'text' ) );
		$this->assertSame( array(), SCF::get_user_meta( $this->user_id, 'checkbox' ) );
		$this->assertSame( 'a', SCF::get_user_meta( $this->user_id, 'text-has-default' ) );
		$this->assertSame( array( 'a' ), SCF::get_user_meta( $this->user_id, 'checkbox-has-default' ) );
		$this->assertSame( array( 'a' ), SCF::get_user_meta( $this->user_id, 'checkbox-key-value' ) );
		$this->assertSame( array(
			'text'                 => '',
			'text-has-default'     => 'a',
			'checkbox'             => array(),
			'checkbox-has-default' => array( 'a' ),
			'checkbox-key-value'   => array( 'a' ),
			'group'                => array(
				array(
					'repeat-text'     => '',
					'repeat-checkbox' => array(),
				),
			),
		), SCF::get_user_meta( $this->user_id ) );

		// When non exist fields
		$this->assertNull( SCF::get_user_meta( $this->user_id, 'not-exist' ) );
	}

	/**
	 * @group get_user_meta
	 */
	public function test_get_user_meta__meta_data_saved() {
		update_user_meta( $this->user_id, 'text', 'text' );
		$this->assertSame( 'text', SCF::get_user_meta( $this->user_id, 'text' ) );
		update_user_meta( $this->user_id, 'checkbox', 'not-exist-key' );
		$this->assertSame( array( 'not-exist-key' ), SCF::get_user_meta( $this->user_id, 'checkbox' ) );
		$this->assertSame( 'a', SCF::get_user_meta( $this->user_id, 'text-has-default' ) );
		$this->assertSame( array( 'a' ), SCF::get_user_meta( $this->user_id, 'checkbox-has-default' ) );
		$this->assertSame( array( 'a' ), SCF::get_user_meta( $this->user_id, 'checkbox-key-value' ) );

		// In repeatable group, non multi-value field
		add_user_meta( $this->user_id, 'repeat-text', 'a' );
		add_user_meta( $this->user_id, 'repeat-text', 'b' );
		$this->assertEquals(
			array( 'a', 'b' ),
			SCF::get_user_meta( $this->user_id, 'repeat-text' )
		);

		// In repeatable group, multi-value field
		update_user_meta( $this->user_id, SCF_Config::PREFIX . 'repeat-multiple-data', array(
			'repeat-checkbox' => array( 1, 2 ),
		) );
		add_user_meta( $this->user_id, 'repeat-checkbox', 'a' );
		add_user_meta( $this->user_id, 'repeat-checkbox', 'b' );
		add_user_meta( $this->user_id, 'repeat-checkbox', 'c' );
		$this->assertEquals(
			array(
				array( 'a' ),
				array( 'b', 'c' ),
			),
			SCF::get_user_meta( $this->user_id, 'repeat-checkbox' )
		);

		$this->assertSame( array(
			'text'                 => 'text',
			'text-has-default'     => 'a',
			'checkbox'             => array( 'not-exist-key' ),
			'checkbox-has-default' => array( 'a' ),
			'checkbox-key-value'   => array( 'a' ),
			'group'                => array(
				array(
					'repeat-text'     => 'a',
					'repeat-checkbox' => array( 'a' ),
				),
				array(
					'repeat-text'     => 'b',
					'repeat-checkbox' => array( 'b', 'c' ),
				),
			),
		), SCF::get_user_meta( $this->user_id ) );
	}

	/**
	 * @group get_term_meta
	 */
	public function test_get_term_meta() {
		// When the term id isn't get
		$this->assertNull( SCF::get_term_meta( false, 'category', 'text' ) );
		$this->assertNull( SCF::get_term_meta( false, 'category' ) );

		// When the taxonomy slug isn't get
		$this->assertNull( SCF::get_term_meta( $this->term_id, false, 'text' ) );
		$this->assertNull( SCF::get_term_meta( $this->term_id, false ) );

		// When meta data isn't saved, return the default value.
		$this->assertSame( '', SCF::get_term_meta( $this->term_id, 'category', 'text' ) );
		$this->assertSame( array(), SCF::get_term_meta( $this->term_id, 'category', 'checkbox' ) );
		$this->assertSame( 'a', SCF::get_term_meta( $this->term_id, 'category', 'text-has-default' ) );
		$this->assertSame( array( 'a' ), SCF::get_term_meta( $this->term_id, 'category', 'checkbox-has-default' ) );
		$this->assertSame( array( 'a' ), SCF::get_term_meta( $this->term_id, 'category', 'checkbox-key-value' ) );
		$this->assertSame( array(
			'text'                 => '',
			'text-has-default'     => 'a',
			'checkbox'             => array(),
			'checkbox-has-default' => array( 'a' ),
			'checkbox-key-value'   => array( 'a' ),
			'group'                => array(
				array(
					'repeat-text'     => '',
					'repeat-checkbox' => array(),
				),
			),
		), SCF::get_term_meta( $this->term_id, 'category' ) );

		// When non exist fields
		$this->assertNull( SCF::get_term_meta( $this->term_id, 'category', 'not-exist' ) );
	}

	/**
	 * @group get_term_meta
	 */
	public function test_get_term_meta__meta_data_saved() {
		$Meta = new Smart_Custom_Fields_Meta( get_term( $this->term_id, 'category' ) );

		$Meta->update( 'text', 'text' );
		$this->assertSame( 'text', SCF::get_term_meta( $this->term_id, 'category', 'text' ) );
		$Meta->update( 'checkbox', 'not-exist-key' );
		$this->assertSame( array( 'not-exist-key' ), SCF::get_term_meta( $this->term_id, 'category', 'checkbox' ) );
		$this->assertSame( 'a', SCF::get_term_meta( $this->term_id, 'category', 'text-has-default' ) );
		$this->assertSame( array( 'a' ), SCF::get_term_meta( $this->term_id, 'category', 'checkbox-has-default' ) );
		$this->assertSame( array( 'a' ), SCF::get_term_meta( $this->term_id, 'category', 'checkbox-key-value' ) );

		// In repeatable group, non multi-value field
		$Meta->add( 'repeat-text', 'a' );
		$Meta->add( 'repeat-text', 'b' );
		$this->assertEquals(
			array( 'a', 'b' ),
			SCF::get_term_meta( $this->term_id, 'category', 'repeat-text' )
		);

		// In repeatable group, multi-value field
		$Meta->update( SCF_Config::PREFIX . 'repeat-multiple-data', array(
			'repeat-checkbox' => array( 1, 2 ),
		) );
		$Meta->add( 'repeat-checkbox', 'a' );
		$Meta->add( 'repeat-checkbox', 'b' );
		$Meta->add( 'repeat-checkbox', 'c' );
		$this->assertEquals(
			array(
				array( 'a' ),
				array( 'b', 'c' ),
			),
			SCF::get_term_meta( $this->term_id, 'category', 'repeat-checkbox' )
		);

		$this->assertSame( array(
			'text'                 => 'text',
			'text-has-default'     => 'a',
			'checkbox'             => array( 'not-exist-key' ),
			'checkbox-has-default' => array( 'a' ),
			'checkbox-key-value'   => array( 'a' ),
			'group'                => array(
				array(
					'repeat-text'     => 'a',
					'repeat-checkbox' => array( 'a' ),
				),
				array(
					'repeat-text'     => 'b',
					'repeat-checkbox' => array( 'b', 'c' ),
				),
			),
		), SCF::get_term_meta( $this->term_id, 'category' ) );
	}

	/**
	 * @group get_option_meta
	 */
	public function test_get_option_meta() {
		// When the term id isn't get
		$this->assertNull( SCF::get_option_meta( false, 'text' ) );
		$this->assertNull( SCF::get_option_meta( false ) );

		// When meta data isn't saved, return the default value.
		$this->assertSame( '', SCF::get_option_meta( $this->menu_slug, 'text' ) );
		$this->assertSame( array(), SCF::get_option_meta( $this->menu_slug, 'checkbox' ) );
		$this->assertSame( 'a', SCF::get_option_meta( $this->menu_slug, 'text-has-default' ) );
		$this->assertSame( array( 'a' ), SCF::get_option_meta( $this->menu_slug, 'checkbox-has-default' ) );
		$this->assertSame( array( 'a' ), SCF::get_option_meta( $this->menu_slug, 'checkbox-key-value' ) );
		$this->assertSame( array(
			'text'                 => '',
			'text-has-default'     => 'a',
			'checkbox'             => array(),
			'checkbox-has-default' => array( 'a' ),
			'checkbox-key-value'   => array( 'a' ),
			'group'                => array(
				array(
					'repeat-text'     => '',
					'repeat-checkbox' => array(),
				),
			),
		), SCF::get_option_meta( $this->menu_slug ) );

		// When non exist fields
		$this->assertNull( SCF::get_option_meta( $this->menu_slug, 'not-exist' ) );
	}

	/**
	 * @group get_option_meta
	 */
	public function test_get_option_meta__meta_data_saved() {
		$Option = SCF::generate_option_object( $this->menu_slug );
		$Meta   = new Smart_Custom_Fields_Meta( $Option );

		$Meta->update( 'text', 'text' );
		$this->assertSame( 'text', SCF::get_option_meta( $this->menu_slug, 'text' ) );
		$Meta->update( 'checkbox', 'not-exist-key' );
		$this->assertSame( array( 'not-exist-key' ), SCF::get_option_meta( $this->menu_slug, 'checkbox' ) );
		$this->assertSame( 'a', SCF::get_option_meta( $this->menu_slug, 'text-has-default' ) );
		$this->assertSame( array( 'a' ), SCF::get_option_meta( $this->menu_slug, 'checkbox-has-default' ) );
		$this->assertSame( array( 'a' ), SCF::get_option_meta( $this->menu_slug, 'checkbox-key-value' ) );

		// In repeatable group, non multi-value field
		$Meta->add( 'repeat-text', 'a' );
		$Meta->add( 'repeat-text', 'b' );
		$this->assertEquals(
			array( 'a', 'b' ),
			SCF::get_option_meta( $this->menu_slug, 'repeat-text' )
		);

		// In repeatable group, multi-value field
		$Meta->update( SCF_Config::PREFIX . 'repeat-multiple-data', array( 'repeat-checkbox' => array( 1, 2 ) ) );
		$Meta->add( 'repeat-checkbox', 'a' );
		$Meta->add( 'repeat-checkbox', 'b' );
		$Meta->add( 'repeat-checkbox', 'c' );
		$this->assertEquals(
			array(
				array( 'a' ),
				array( 'b', 'c' ),
			),
			SCF::get_option_meta( $this->menu_slug, 'repeat-checkbox' )
		);

		$this->assertSame( array(
			'text'                 => 'text',
			'text-has-default'     => 'a',
			'checkbox'             => array( 'not-exist-key' ),
			'checkbox-has-default' => array( 'a' ),
			'checkbox-key-value'   => array( 'a' ),
			'group'                => array(
				array(
					'repeat-text'     => 'a',
					'repeat-checkbox' => array( 'a' ),
				),
				array(
					'repeat-text'     => 'b',
					'repeat-checkbox' => array( 'b', 'c' ),
				),
			),
		), SCF::get_option_meta( $this->menu_slug ) );
	}

	/**
	 * @group get_field
	 */
	public function test_get_field() {
		// When the field isn't existed
		$this->go_to( $this->post_id );
		$Field = SCF::get_field( get_post( $this->post_id ), 'not-exist' );
		$this->assertNull( $Field );

		// When the field existed
		$Field = SCF::get_field( get_post( $this->post_id ), 'text' );
		$this->assertEquals( 'text', $Field->get( 'name' ) );
	}

	/**
	 * @group add_settings
	 */
	public function test_add_settings() {
		$post_id = $this->factory->post->create( array(
			'post_type'  => SCF_Config::NAME,
			'post_title' => 'test_settings',
		) );
		update_post_meta( $post_id, SCF_Config::PREFIX . 'condition', array( 'post' ) );

		$settings = SCF::get_settings( get_post( $this->post_id ) );
		$this->assertCount( 2, $settings );
		foreach ( $settings as $Setting ) {
			$this->assertTrue( in_array( $Setting->get_title(), array( 'test_settings', 'Register Test' ) ) );
		}
	}

	/**
	 * @group get_settings_posts
	 */
	public function test_get_settings_posts() {
		// When isn't saved
		$this->assertSame( array(), SCF::get_settings_posts( get_post( $this->post_id ) ) );
	}

	/**
	 * @group get_settings_posts
	 */
	public function test_get_settings_posts__saved() {
		$post_id = $this->factory->post->create( array(
			'post_type'  => SCF_Config::NAME,
			'post_title' => 'test_settings_post',
		) );
		update_post_meta( $post_id, SCF_Config::PREFIX . 'condition', array( 'post' ) );
		$settings_posts = SCF::get_settings_posts( get_post( $this->post_id ) );
		$this->assertCount( 1, $settings_posts );
		foreach ( $settings_posts as $settings_post ) {
			$this->assertEquals( 'test_settings_post', $settings_post->post_title );
		}
	}

	/**
	 * @group get_settings
	 */
	public function test_get_settings() {
		// Match the post
		$post_id = $this->factory->post->create( array(
			'post_type'   => 'post',
			'post_status' => 'publish',
		) );
		$settings = SCF::get_settings( get_post( $post_id ) );
		foreach ( $settings as $Setting ) {
			$this->assertTrue( is_a( $Setting, 'Smart_Custom_Fields_Setting' ) );
		}

		// Not match the post
		$settings = SCF::get_settings( get_post( 99999 ) );
		$this->assertSame( array(), $settings );

		// Match the role
		$user_id = $this->factory->user->create( array( 'role' => 'editor' ) );
		$settings = SCF::get_settings( get_userdata( $user_id ) );
		$this->assertTrue( is_a( current( $settings ), 'Smart_Custom_Fields_Setting' ) );

		$user_id = $this->factory->user->create( array( 'role' => 'subscriber' ) );
		$settings = SCF::get_settings( get_userdata( $user_id ) );
		$this->assertTrue( is_a( current( $settings ), 'Smart_Custom_Fields_Setting' ) );

		// Not match the role
		$settings = SCF::get_settings( get_userdata( 99999 ) );
		$this->assertSame( array(), $settings );

		// Match the term
		$term_id = $this->factory->term->create( array( 'taxonomy' => 'category' ) );
		$settings = SCF::get_settings( get_term( $term_id, 'category' ) );
		$this->assertTrue( is_a( current( $settings ), 'Smart_Custom_Fields_Setting' ) );

		// Not match the term
		$settings = SCF::get_settings( get_term( 99999, 'category' ) );
		$this->assertSame( array(), $settings );
	}

	/**
	 * @group get_repeat_multiple_data
	 */
	public function test_get_repeat_multiple_data() {
		// When isn't existed
		$this->assertSame( array(), SCF::get_repeat_multiple_data( get_post( $this->post_id ) ) );

		// When existed
		update_post_meta( $this->post_id, SCF_Config::PREFIX . 'repeat-multiple-data',
			array(
				'repeat-checkbox' => array( 1, 2 ),
			)
		);
		$this->assertSame(
			array(
				'repeat-checkbox' => array( 1, 2 ),
			),
			SCF::get_repeat_multiple_data( get_post( $this->post_id ) )
		);
	}

	/**
	 * @group get_groups
	 */
	public function test_get_groups() {
		$settings = SCF::get_settings( get_post( $this->post_id ) );
		foreach ( $settings as $Setting ) {
			foreach ( $Setting->get_groups() as $Group ) {
				// Return null when group name is numeric.
				$this->assertTrue(
					in_array(
						$Group->get_name(),
						array( null, 'text-has-default', 'checkbox', 'checkbox-has-default', 'checkbox-key-value', 'group' ),
						true
					)
				);
			}
		}
	}

	/**
	 * @group get_default_value
	 */
	public function test_get_default_value() {
		// When the default value isn't existed, single is true
		$Field = SCF::get_field( get_post( $this->post_id ), 'text' );
		$this->assertSame( '', SCF::get_default_value( $Field, true ) );

		// When the default value isn't existed, single is false
		$Field = SCF::get_field( get_post( $this->post_id ), 'text' );
		$this->assertSame( array(), SCF::get_default_value( $Field, false ) );

		// When the default value existed, single is true
		$Field = SCF::get_field( get_post( $this->post_id ), 'text-has-default' );
		$this->assertSame( 'a', SCF::get_default_value( $Field, true ) );

		// When the default value existed, single is false
		$Field = SCF::get_field( get_post( $this->post_id ), 'text-has-default' );
		$this->assertSame( array( 'a' ), SCF::get_default_value( $Field, false ) );

		// When the default value existed, multi-value field, single is true
		$Field = SCF::get_field( get_post( $this->post_id ), 'checkbox-has-default' );
		$this->assertSame( array( 'a' ), SCF::get_default_value( $Field, true ) );

		// When the default value existed, multi-value field, single is false
		$Field = SCF::get_field( get_post( $this->post_id ), 'checkbox-has-default' );
		$this->assertSame( array( 'a' ), SCF::get_default_value( $Field, false ) );

		// When the default value isn't existed, multi-value field, single is true
		$Field = SCF::get_field( get_post( $this->post_id ), 'checkbox' );
		$this->assertSame( array(), SCF::get_default_value( $Field, true ) );

		// When the default value isn't existed, multi-value field, single is false
		$Field = SCF::get_field( get_post( $this->post_id ), 'checkbox' );
		$this->assertSame( array(), SCF::get_default_value( $Field, false ) );
	}

	/**
	 * @group get_post_meta
	 */
	public function test_get_post_meta() {
		// If getting meta data other than SCF
		update_post_meta( $this->post_id, '_get_post_meta', 'value' );
		$this->assertSame( 'value', get_post_meta( $this->post_id, '_get_post_meta', true ) );
		update_post_meta( $this->new_post_id, '_get_post_meta', 'value' );
		$this->assertSame( 'value', get_post_meta( $this->new_post_id, '_get_post_meta', true ) );
		update_post_meta( $this->draft_post_id, '_get_post_meta', 'value' );
		$this->assertSame( 'value', get_post_meta( $this->draft_post_id, '_get_post_meta', true ) );
	}

	/**
	 * @group get_post_meta
	 */
	public function test_get_post_meta__in_preview() {
		// If getting meta data other than SCF in preview
		global $wp_query, $post;

		update_post_meta( $this->post_id, '_get_post_meta', 'value' );
		$this->create_revision( $this->post_id );

		update_post_meta( $this->new_post_id, '_get_post_meta', 'value' );
		$this->create_revision( $this->new_post_id );

		update_post_meta( $this->draft_post_id, '_get_post_meta', 'value' );
		$this->create_revision( $this->draft_post_id );

		// Set preview state
		$backup_wp_query = clone $wp_query;
		$wp_query->is_preview = true;

		$post = get_post( $this->post_id );
		setup_postdata( $post );
		$this->assertSame( 'value', get_post_meta( $this->post_id, '_get_post_meta', true ) );
		wp_reset_postdata();

		$post = get_post( $this->new_post_id );
		setup_postdata( $post );
		$this->assertSame( 'value', get_post_meta( $this->new_post_id, '_get_post_meta', true ) );
		wp_reset_postdata();

		$post = get_post( $this->draft_post_id );
		setup_postdata( $post );
		$this->assertSame( 'value', get_post_meta( $this->draft_post_id, '_get_post_meta', true ) );
		wp_reset_postdata();

		// Release the preview state
		$wp_query = $backup_wp_query;
	}

	/**
	 * @group is_empty
	 */
	public function test_is_empty() {
		$this->assertTrue( SCF::is_empty( $value ) );

		$value = null;
		$this->assertTrue( SCF::is_empty( $value ) );

		$value = 'a';
		$this->assertFalse( SCF::is_empty( $value ) );

		$value = 0;
		$this->assertFalse( SCF::is_empty( $value ) );

		$value = array( 'a' );
		$this->assertFalse( SCF::is_empty( $value ) );
	}

	/**
	 * @group is_assoc
	 */
	public function test_is_assoc() {
		$this->assertFalse( SCF::is_assoc( 0 ) );
		$this->assertFalse( SCF::is_assoc( 'a' ) );
		$this->assertFalse( SCF::is_assoc( array( 'a' ) ) );
		$this->assertTrue( SCF::is_assoc( array( 'a' => 'a' ) ) );
	}

	/**
	 * @group choices_eol_to_array
	 */
	public function test_choices_eol_to_array() {
		$this->assertSame( array(), SCF::choices_eol_to_array( '' ) );
		$this->assertSame( array(), SCF::choices_eol_to_array( false ) );
		$this->assertSame( array(), SCF::choices_eol_to_array( null ) );
		$this->assertSame( array(), SCF::choices_eol_to_array( array() ) );
		$this->assertSame( array( 'A', 'B', 'C' ), SCF::choices_eol_to_array( array( 'A', 'B', 'C' ) ) );
		$this->assertSame( array( 'A', 'B', 'C' ), SCF::choices_eol_to_array( "A\r\nB\r\nC" ) );
		$this->assertSame( array( 'A', 'B', 'C' ), SCF::choices_eol_to_array( "A\rB\rC" ) );
		$this->assertSame( array( 'A', 'B', 'C' ), SCF::choices_eol_to_array( "A\nB\nC" ) );
		$this->assertSame( array( 'a' => 'AAA', 'b' => 'BBB' ), SCF::choices_eol_to_array( array( 'a' => 'AAA', 'b' => 'BBB' ) ) );
		$this->assertSame( array( 'a' => 'AAA', 'b' => 'BBB' ), SCF::choices_eol_to_array( "a => AAA\nb => BBB" ) );
		$this->assertSame( array( 'a' => 'AAA', 0 => 'BBB' ), SCF::choices_eol_to_array( "a => AAA\nBBB" ) );
		$this->assertSame( array( '0' => 'AAA', '1' => 'BBB' ), SCF::choices_eol_to_array( "0 => AAA\n1 => BBB" ) );
	}

	protected function create_revision( $post_id ) {
		return $this->factory->post->create( array(
			'post_type'   => 'revision',
			'post_parent' => $post_id,
			'post_status' => 'inherit',
			'post_name'   => $post_id . '-autosave',
		) );
	}

	/**
	 * Register custom fields using filter hook
	 */
	public function _register( $settings, $type, $id, $meta_type ) {
		if (
			( $type === 'post' && $id === $this->post_id ) ||
			( $type === 'post' && $id === $this->new_post_id ) ||
			( $type === 'editor' ) ||
			( $type === 'subscriber' ) ||
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
			$settings[$Setting->get_id()] = $Setting;
		}
		return $settings;
	}
}
