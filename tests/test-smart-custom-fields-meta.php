<?php
class Smart_Custom_Fields_Meta_Test extends WP_UnitTestCase {

	/**
	 * @var int
	 */
	protected $new_post_id;

	/**
	 * @var int
	 */
	protected $post_id;

	/**
	 * @var int
	 */
	protected $revision_id;

	/**
	 * @var int
	 */
	protected $user_id;

	/**
	 * @var int
	 */
	protected $term_id;

	/**
	 * Set up.
	 */
	public function set_up() {
		parent::set_up();

		// The post for custom fields
		$this->post_id   = $this->factory->post->create(
			array(
				'post_type'   => 'post',
				'post_status' => 'publish',
			)
		);
		$this->Meta_post = new Smart_Custom_Fields_Meta( get_post( $this->post_id ) );

		// The auto draft post for custom fields
		$this->new_post_id   = $this->factory->post->create(
			array(
				'post_type'   => 'post',
				'post_status' => 'auto-draft',
			)
		);
		$this->Meta_new_post = new Smart_Custom_Fields_Meta( get_post( $this->new_post_id ) );

		// The revision post for custom fields
		$this->revision_id   = $this->factory->post->create(
			array(
				'post_type'   => 'revision',
				'post_parent' => $this->post_id,
			)
		);
		$this->Meta_revision = new Smart_Custom_Fields_Meta( get_post( $this->revision_id ) );

		// The user for custom fields
		$this->user_id = $this->factory->user->create( array( 'role' => 'editor' ) );
		get_userdata( $this->user_id )->add_role( 'subscriber' );
		$this->Meta_user = new Smart_Custom_Fields_Meta( get_userdata( $this->user_id ) );

		// The term for custom fields
		$this->term_id   = $this->factory->term->create( array( 'taxonomy' => 'category' ) );
		$this->Meta_term = new Smart_Custom_Fields_Meta( get_term( $this->term_id, 'category' ) );

		// The option page for custom fields
		$this->menu_slug   = SCF::add_options_page( 'page title', 'menu title', 'manage_options', 'menu-slug' );
		$this->Meta_option = new Smart_Custom_Fields_Meta( SCF::generate_option_object( $this->menu_slug ) );

		add_filter( 'smart-cf-register-fields', array( $this, '_register' ), 10, 4 );

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
	 * @group get_meta_type
	 */
	public function test_get_meta_type() {
		$this->assertSame( 'post', $this->Meta_post->get_meta_type() );
		$this->assertSame( 'post', $this->Meta_new_post->get_meta_type() );
		$this->assertSame( 'post', $this->Meta_revision->get_meta_type() );
		$this->assertSame( 'user', $this->Meta_user->get_meta_type() );
		$this->assertSame( 'term', $this->Meta_term->get_meta_type() );
		$this->assertSame( 'option', $this->Meta_option->get_meta_type() );
	}

	/**
	 * @group get_id
	 */
	public function test_get_id() {
		$this->assertSame( $this->post_id, $this->Meta_post->get_id() );
		$this->assertSame( $this->new_post_id, $this->Meta_new_post->get_id() );
		$this->assertSame( $this->revision_id, $this->Meta_revision->get_id() );
		$this->assertSame( $this->user_id, $this->Meta_user->get_id() );
		$this->assertSame( $this->term_id, $this->Meta_term->get_id() );
		$this->assertSame( $this->menu_slug, $this->Meta_option->get_id() );
	}

	/**
	 * @group get_type
	 */
	public function test_get_type() {
		$this->assertSame( 'post', $this->Meta_post->get_type() );
		$this->assertSame( 'post', $this->Meta_new_post->get_type() );
		$this->assertSame( 'revision', $this->Meta_revision->get_type( true ) );
		$this->assertSame( 'post', $this->Meta_revision->get_type( false ) );
		$this->assertSame( 'category', $this->Meta_term->get_type() );
		$this->assertSame( 'editor', $this->Meta_user->get_type() );
		$this->assertSame( $this->menu_slug, $this->Meta_option->get_type() );
	}

	/**
	 * @group get_types
	 */
	public function test_get_types() {
		$this->assertSame( array( 'post' ), $this->Meta_post->get_types() );
		$this->assertSame( array( 'post' ), $this->Meta_new_post->get_types() );
		$this->assertSame( array( 'revision' ), $this->Meta_revision->get_types( true ) );
		$this->assertSame( array( 'post' ), $this->Meta_revision->get_types( false ) );
		$this->assertSame( array( 'category' ), $this->Meta_term->get_types() );
		$this->assertSame( array( 'editor', 'subscriber' ), $this->Meta_user->get_types() );
		$this->assertSame( array( $this->menu_slug ), $this->Meta_option->get_types() );
	}

	/**
	 * @group is_saved
	 */
	public function test_is_saved() {
		$this->assertFalse( $this->Meta_post->is_saved() );
		$this->assertFalse( $this->Meta_new_post->is_saved() );
		$this->assertFalse( $this->Meta_revision->is_saved() );
		$this->assertFalse( $this->Meta_term->is_saved() );
		$this->assertFalse( $this->Meta_user->is_saved() );
		$this->assertFalse( $this->Meta_option->is_saved() );
	}

	/**
	 * @group is_saved
	 */
	public function test_is_saved__saved() {
		$POST = $this->_return_post_data_for_save( SCF_Config::NAME );
		$this->Meta_post->save( $POST );
		$this->Meta_new_post->save( $POST );
		$this->Meta_revision->save( $POST );
		$this->Meta_term->save( $POST );
		$this->Meta_user->save( $POST );
		$this->Meta_option->save( $POST );

		$this->assertTrue( $this->Meta_post->is_saved() );
		$this->assertFalse( $this->Meta_new_post->is_saved() );
		$this->assertTrue( $this->Meta_revision->is_saved() );
		$this->assertTrue( $this->Meta_term->is_saved() );
		$this->assertTrue( $this->Meta_user->is_saved() );
		$this->assertTrue( $this->Meta_option->is_saved() );
	}

	/**
	 * @group is_saved_the_key
	 */
	public function test_is_saved_the_key() {
		$this->assertFalse( $this->Meta_post->is_saved_the_key( 'not-exist' ) );

		$this->assertFalse( $this->Meta_post->is_saved_the_key( 'text' ) );
		$this->Meta_post->update( 'text', 'text' );
		$this->assertTrue( $this->Meta_post->is_saved_the_key( 'text' ) );

		$this->assertFalse( $this->Meta_new_post->is_saved_the_key( 'text' ) );
		$this->Meta_new_post->update( 'text', 'text' );
		$this->assertFalse( $this->Meta_new_post->is_saved_the_key( 'text' ) );

		$this->assertFalse( $this->Meta_term->is_saved_the_key( 'text' ) );
		$this->Meta_term->update( 'text', 'text' );
		$this->assertTrue( $this->Meta_term->is_saved_the_key( 'text' ) );

		$this->assertFalse( $this->Meta_user->is_saved_the_key( 'text' ) );
		$this->Meta_user->update( 'text', 'text' );
		$this->assertTrue( $this->Meta_user->is_saved_the_key( 'text' ) );

		$this->assertFalse( $this->Meta_option->is_saved_the_key( 'text' ) );
		$this->Meta_option->update( 'text', 'text' );
		$this->assertTrue( $this->Meta_option->is_saved_the_key( 'text' ) );
	}

	/**
	 * @group maybe_4_3_term_meta
	 */
	public function test_maybe_4_3_term_meta() {
		$this->assertFalse( $this->Meta_post->maybe_4_3_term_meta() );
		$this->assertFalse( $this->Meta_term->maybe_4_3_term_meta() );

		update_option( $this->Meta_term->get_option_name(), 'text', 'text' );
		$this->assertTrue( $this->Meta_term->maybe_4_3_term_meta() );

		if ( _get_meta_table( $this->Meta_term->get_meta_type() ) ) {
			update_metadata( 'term', $this->term_id, 'text', 'text' );
			$this->assertFalse( $this->Meta_term->maybe_4_3_term_meta() );
		}
	}

	/**
	 * @group get
	 */
	public function test_get__post() {
		$this->assertSame( array(), $this->Meta_post->get( 'text' ) );
		$this->assertSame( '', $this->Meta_post->get( 'text', true ) );
		$this->assertSame( array(), $this->Meta_post->get( 'not-exist' ) );
		$this->assertSame( '', $this->Meta_post->get( 'not-exist', true ) );
		$this->assertSame( array(), $this->Meta_post->get() );
		$this->assertSame( '', $this->Meta_post->get( '', true ) );
		$this->assertSame( array(), $this->Meta_post->get( 'group' ) );
		$this->assertSame( '', $this->Meta_post->get( 'group', true ) );
		$this->assertSame( array(), $this->Meta_post->get( SCF_Config::PREFIX . 'repeat-multiple-data' ) );
		$this->assertSame( '', $this->Meta_post->get( SCF_Config::PREFIX . 'repeat-multiple-data', true ) );
		$this->assertSame( array(), $this->Meta_post->get( 'text-has-default' ) );
		$this->assertSame( '', $this->Meta_post->get( 'text-has-default', true ) );
		$this->assertSame( array(), $this->Meta_post->get( 'checkbox-has-default' ) );
		$this->assertSame( '', $this->Meta_post->get( 'checkbox-has-default', true ) );
	}

	/**
	 * @group get
	 */
	public function test_get__term() {
		$this->assertSame( array(), $this->Meta_term->get( 'text' ) );
		$this->assertSame( '', $this->Meta_term->get( 'text', true ) );
		$this->assertSame( array(), $this->Meta_term->get( 'not-exist' ) );
		$this->assertSame( '', $this->Meta_term->get( 'not-exist', true ) );
		$this->assertSame( array(), $this->Meta_term->get() );
		$this->assertSame( '', $this->Meta_term->get( '', true ) );
		$this->assertSame( array(), $this->Meta_term->get( 'group' ) );
		$this->assertSame( '', $this->Meta_term->get( 'group', true ) );
		$this->assertSame( array(), $this->Meta_term->get( SCF_Config::PREFIX . 'repeat-multiple-data' ) );
		$this->assertSame( '', $this->Meta_term->get( SCF_Config::PREFIX . 'repeat-multiple-data', true ) );
		$this->assertSame( array(), $this->Meta_term->get( 'text-has-default' ) );
		$this->assertSame( '', $this->Meta_term->get( 'text-has-default', true ) );
		$this->assertSame( array(), $this->Meta_term->get( 'checkbox-has-default' ) );
		$this->assertSame( '', $this->Meta_term->get( 'checkbox-has-default', true ) );
	}

	/**
	 * @group get
	 */
	public function test_get__user() {
		$this->assertSame( array(), $this->Meta_user->get( 'text' ) );
		$this->assertSame( '', $this->Meta_user->get( 'text', true ) );
		$this->assertSame( array(), $this->Meta_user->get( 'not-exist' ) );
		$this->assertSame( '', $this->Meta_user->get( 'not-exist', true ) );
		$this->assertSame( array(), $this->Meta_user->get() );
		$this->assertSame( '', $this->Meta_user->get( '', true ) );
		$this->assertSame( array(), $this->Meta_user->get( 'group' ) );
		$this->assertSame( '', $this->Meta_user->get( 'group', true ) );
		$this->assertSame( array(), $this->Meta_user->get( SCF_Config::PREFIX . 'repeat-multiple-data' ) );
		$this->assertSame( '', $this->Meta_user->get( SCF_Config::PREFIX . 'repeat-multiple-data', true ) );
		$this->assertSame( array(), $this->Meta_user->get( 'text-has-default' ) );
		$this->assertSame( '', $this->Meta_user->get( 'text-has-default', true ) );
		$this->assertSame( array(), $this->Meta_user->get( 'checkbox-has-default' ) );
		$this->assertSame( '', $this->Meta_user->get( 'checkbox-has-default', true ) );
	}

	/**
	 * @group get
	 */
	public function test_get__option() {
		$this->assertSame( array(), $this->Meta_option->get( 'text' ) );
		$this->assertSame( '', $this->Meta_option->get( 'text', true ) );
		$this->assertSame( array(), $this->Meta_option->get( 'not-exist' ) );
		$this->assertSame( '', $this->Meta_option->get( 'not-exist', true ) );
		$this->assertSame( array(), $this->Meta_option->get() );
		$this->assertSame( '', $this->Meta_option->get( '', true ) );
		$this->assertSame( array(), $this->Meta_option->get( 'group' ) );
		$this->assertSame( '', $this->Meta_option->get( 'group', true ) );
		$this->assertSame( array(), $this->Meta_option->get( SCF_Config::PREFIX . 'repeat-multiple-data' ) );
		$this->assertSame( '', $this->Meta_option->get( SCF_Config::PREFIX . 'repeat-multiple-data', true ) );
		$this->assertSame( array(), $this->Meta_option->get( 'text-has-default' ) );
		$this->assertSame( '', $this->Meta_option->get( 'text-has-default', true ) );
		$this->assertSame( array(), $this->Meta_option->get( 'checkbox-has-default' ) );
		$this->assertSame( '', $this->Meta_option->get( 'checkbox-has-default', true ) );
	}

	/**
	 * @group get
	 */
	public function test_get__post_saved() {
		$this->Meta_post->update( 'text', 'text' );
		$this->Meta_post->add( 'checkbox', 'a' );
		$this->Meta_post->add( 'checkbox', 'b' );
		$this->Meta_post->add( 'repeat-checkbox', 'a' );
		$this->Meta_post->add( 'repeat-checkbox', 'b' );
		$this->Meta_post->update( SCF_Config::PREFIX . 'repeat-multiple-data', array( 'repeat-checkbox' => array( 1, 1 ) ) );
		$this->assertSame( array( 'text' ), $this->Meta_post->get( 'text' ) );
		$this->assertSame( 'text', $this->Meta_post->get( 'text', true ) );
		$this->assertSame( array(), $this->Meta_post->get( 'group' ) );
		$this->assertSame( '', $this->Meta_post->get( 'group', true ) );
		$this->assertSame(
			array( array( 'repeat-checkbox' => array( 1, 1 ) ) ),
			$this->Meta_post->get( SCF_Config::PREFIX . 'repeat-multiple-data' )
		);
		$this->assertSame(
			array( 'repeat-checkbox' => array( 1, 1 ) ),
			$this->Meta_post->get( SCF_Config::PREFIX . 'repeat-multiple-data', true )
		);
		$this->assertSame(
			array(
				'text'            => array( 'text' ),
				'checkbox'        => array( 'a', 'b' ),
				'repeat-checkbox' => array( 'a', 'b' ),
			),
			$this->Meta_post->get()
		);
		$this->assertSame(
			array(
				'text'            => array( 'text' ),
				'checkbox'        => array( 'a', 'b' ),
				'repeat-checkbox' => array( 'a', 'b' ),
			),
			$this->Meta_post->get( '', true )
		);
	}

	/**
	 * @group get
	 */
	public function test_get__term_saved() {
		$this->Meta_term->update( 'text', 'text' );
		$this->Meta_term->add( 'checkbox', 'a' );
		$this->Meta_term->add( 'checkbox', 'b' );
		$this->Meta_term->add( 'repeat-checkbox', 'a' );
		$this->Meta_term->add( 'repeat-checkbox', 'b' );
		$this->Meta_term->update( SCF_Config::PREFIX . 'repeat-multiple-data', array( 'repeat-checkbox' => array( 1, 1 ) ) );
		$this->assertSame( array( 'text' ), $this->Meta_term->get( 'text' ) );
		$this->assertSame( 'text', $this->Meta_term->get( 'text', true ) );
		$this->assertSame( array(), $this->Meta_term->get( 'group' ) );
		$this->assertSame( '', $this->Meta_term->get( 'group', true ) );
		$this->assertSame(
			array( array( 'repeat-checkbox' => array( 1, 1 ) ) ),
			$this->Meta_term->get( SCF_Config::PREFIX . 'repeat-multiple-data' )
		);
		$this->assertSame(
			array( 'repeat-checkbox' => array( 1, 1 ) ),
			$this->Meta_term->get( SCF_Config::PREFIX . 'repeat-multiple-data', true )
		);
		$this->assertSame(
			array(
				'text'            => array( 'text' ),
				'checkbox'        => array( 'a', 'b' ),
				'repeat-checkbox' => array( 'a', 'b' ),
			),
			$this->Meta_term->get()
		);
		$this->assertSame(
			array(
				'text'            => array( 'text' ),
				'checkbox'        => array( 'a', 'b' ),
				'repeat-checkbox' => array( 'a', 'b' ),
			),
			$this->Meta_term->get( '', true )
		);
	}

	/**
	 * @group get
	 */
	public function test_get__user_saved() {
		$this->Meta_user->update( 'text', 'text' );
		$this->Meta_user->add( 'checkbox', 'a' );
		$this->Meta_user->add( 'checkbox', 'b' );
		$this->Meta_user->add( 'repeat-checkbox', 'a' );
		$this->Meta_user->add( 'repeat-checkbox', 'b' );
		$this->Meta_user->update( SCF_Config::PREFIX . 'repeat-multiple-data', array( 'repeat-checkbox' => array( 1, 1 ) ) );
		$this->assertSame( array( 'text' ), $this->Meta_user->get( 'text' ) );
		$this->assertSame( 'text', $this->Meta_user->get( 'text', true ) );
		$this->assertSame( array(), $this->Meta_user->get( 'group' ) );
		$this->assertSame( '', $this->Meta_user->get( 'group', true ) );
		$this->assertSame(
			array( array( 'repeat-checkbox' => array( 1, 1 ) ) ),
			$this->Meta_user->get( SCF_Config::PREFIX . 'repeat-multiple-data' )
		);
		$this->assertSame(
			array( 'repeat-checkbox' => array( 1, 1 ) ),
			$this->Meta_user->get( SCF_Config::PREFIX . 'repeat-multiple-data', true )
		);
		$this->assertSame(
			array(
				'text'            => array( 'text' ),
				'checkbox'        => array( 'a', 'b' ),
				'repeat-checkbox' => array( 'a', 'b' ),
			),
			$this->Meta_user->get()
		);
		$this->assertSame(
			array(
				'text'            => array( 'text' ),
				'checkbox'        => array( 'a', 'b' ),
				'repeat-checkbox' => array( 'a', 'b' ),
			),
			$this->Meta_user->get( '', true )
		);
	}

	/**
	 * @group get
	 */
	public function test_get__option_saved() {
		$this->Meta_option->update( 'text', 'text' );
		$this->Meta_option->add( 'checkbox', 'a' );
		$this->Meta_option->add( 'checkbox', 'b' );
		$this->Meta_option->add( 'repeat-checkbox', 'a' );
		$this->Meta_option->add( 'repeat-checkbox', 'b' );
		$this->Meta_option->update( SCF_Config::PREFIX . 'repeat-multiple-data', array( 'repeat-checkbox' => array( 1, 1 ) ) );
		$this->assertSame( array( 'text' ), $this->Meta_option->get( 'text' ) );
		$this->assertSame( 'text', $this->Meta_option->get( 'text', true ) );
		$this->assertSame( array(), $this->Meta_option->get( 'group' ) );
		$this->assertSame( '', $this->Meta_option->get( 'group', true ) );
		$this->assertSame(
			array( array( 'repeat-checkbox' => array( 1, 1 ) ) ),
			$this->Meta_option->get( SCF_Config::PREFIX . 'repeat-multiple-data' )
		);
		$this->assertSame(
			array( 'repeat-checkbox' => array( 1, 1 ) ),
			$this->Meta_option->get( SCF_Config::PREFIX . 'repeat-multiple-data', true )
		);
		$this->assertSame(
			array(
				'text'            => array( 'text' ),
				'checkbox'        => array( 'a', 'b' ),
				'repeat-checkbox' => array( 'a', 'b' ),
			),
			$this->Meta_option->get()
		);
		$this->assertSame(
			array(
				'text'            => array( 'text' ),
				'checkbox'        => array( 'a', 'b' ),
				'repeat-checkbox' => array( 'a', 'b' ),
			),
			$this->Meta_option->get( '', true )
		);
	}

	/**
	 * @group update
	 */
	public function test_update__post() {
		$this->Meta_post->update( 'text', 'text' );
		$this->assertSame( 'text', $this->Meta_post->get( 'text', true ) );
		$this->Meta_post->update( 'text', 'new-value' );
		$this->assertSame( 'new-value', $this->Meta_post->get( 'text', true ) );
	}

	/**
	 * @group update
	 */
	public function test_update__term() {
		$this->Meta_term->update( 'text', 'text' );
		$this->assertSame( 'text', $this->Meta_term->get( 'text', true ) );
		$this->Meta_term->update( 'text', 'new-value' );
		$this->assertSame( 'new-value', $this->Meta_term->get( 'text', true ) );
	}

	/**
	 * @group update
	 */
	public function test_update__user() {
		$this->Meta_user->update( 'text', 'text' );
		$this->assertSame( 'text', $this->Meta_user->get( 'text', true ) );
		$this->Meta_user->update( 'text', 'new-value' );
		$this->assertSame( 'new-value', $this->Meta_user->get( 'text', true ) );
	}

	/**
	 * @group update
	 */
	public function test_update__option() {
		$this->Meta_option->update( 'text', 'text' );
		$this->assertSame( 'text', $this->Meta_option->get( 'text', true ) );
		$this->Meta_option->update( 'text', 'new-value' );
		$this->assertSame( 'new-value', $this->Meta_option->get( 'text', true ) );
	}

	/**
	 * @group update
	 */
	public function test_update__post_using_prev_value() {
		$this->Meta_post->update( 'text', 'no-value', 'prev-value' );
		$this->assertEquals( 'no-value', $this->Meta_post->get( 'text', true ) );
		$this->Meta_post->update( 'text', 'prev-value' );
		$this->Meta_post->update( 'text', 'new-value', 'prev-value' );
		$this->assertEquals( 'new-value', $this->Meta_post->get( 'text', true ) );
		$this->Meta_post->update( 'text', 'text', 'incorrect-value' );
		$this->assertEquals( 'new-value', $this->Meta_post->get( 'text', true ) );
	}

	/**
	 * @group update
	 */
	public function test_update__term_using_prev_value() {
		$this->Meta_term->update( 'text', 'no-value', 'prev-value' );
		$this->assertEquals( 'no-value', $this->Meta_term->get( 'text', true ) );
		$this->Meta_term->update( 'text', 'prev-value' );
		$this->Meta_term->update( 'text', 'new-value', 'prev-value' );
		$this->assertEquals( 'new-value', $this->Meta_term->get( 'text', true ) );
		$this->Meta_term->update( 'text', 'text', 'incorrect-value' );
		$this->assertEquals( 'new-value', $this->Meta_term->get( 'text', true ) );
	}

	/**
	 * @group update
	 */
	public function test_update__user_using_prev_value() {
		$this->Meta_user->update( 'text', 'no-value', 'prev-value' );
		$this->assertEquals( 'no-value', $this->Meta_user->get( 'text', true ) );
		$this->Meta_user->update( 'text', 'prev-value' );
		$this->Meta_user->update( 'text', 'new-value', 'prev-value' );
		$this->assertEquals( 'new-value', $this->Meta_user->get( 'text', true ) );
		$this->Meta_user->update( 'text', 'text', 'incorrect-value' );
		$this->assertEquals( 'new-value', $this->Meta_user->get( 'text', true ) );
	}

	/**
	 * @group update
	 */
	public function test_update__option_using_prev_value() {
		$this->Meta_option->update( 'text', 'no-value', 'prev-value' );
		$this->assertEquals( 'no-value', $this->Meta_option->get( 'text', true ) );
		$this->Meta_option->update( 'text', 'prev-value' );
		$this->Meta_option->update( 'text', 'new-value', 'prev-value' );
		$this->assertEquals( 'new-value', $this->Meta_option->get( 'text', true ) );
		$this->Meta_option->update( 'text', 'text', 'incorrect-value' );
		$this->assertEquals( 'new-value', $this->Meta_option->get( 'text', true ) );
	}

	/**
	 * @group add
	 */
	public function test_add__post() {
		$this->Meta_post->add( 'text', 'text' );
		$this->assertSame( array( 'text' ), $this->Meta_post->get( 'text' ) );
		$this->Meta_post->add( 'text', 'text' );
		$this->assertSame( array( 'text', 'text' ), $this->Meta_post->get( 'text' ) );
	}

	/**
	 * @group add
	 */
	public function test_add__term() {
		$this->Meta_term->add( 'text', 'text' );
		$this->assertSame( array( 'text' ), $this->Meta_term->get( 'text' ) );
		$this->Meta_term->add( 'text', 'text' );
		$this->assertSame( array( 'text', 'text' ), $this->Meta_term->get( 'text' ) );
	}

	/**
	 * @group add
	 */
	public function test_add__user() {
		$this->Meta_user->add( 'text', 'text' );
		$this->assertSame( array( 'text' ), $this->Meta_user->get( 'text' ) );
		$this->Meta_user->add( 'text', 'text' );
		$this->assertSame( array( 'text', 'text' ), $this->Meta_user->get( 'text' ) );
	}

	/**
	 * @group add
	 */
	public function test_add__option() {
		$this->Meta_option->add( 'text', 'text' );
		$this->assertSame( array( 'text' ), $this->Meta_option->get( 'text' ) );
		$this->Meta_option->add( 'text', 'text' );
		$this->assertSame( array( 'text', 'text' ), $this->Meta_option->get( 'text' ) );
	}

	/**
	 * @group add
	 */
	public function test_add__post_using_unique() {
		$this->Meta_post->add( 'text', 'text', true );
		$this->assertEquals( array( 'text' ), $this->Meta_post->get( 'text' ) );
		$this->Meta_post->add( 'text', 'new-value', true );
		$this->assertEquals( array( 'text' ), $this->Meta_post->get( 'text' ) );
	}

	/**
	 * @group add
	 */
	public function test_add__term_using_unique() {
		$this->Meta_term->add( 'text', 'text', true );
		$this->assertEquals( array( 'text' ), $this->Meta_term->get( 'text' ) );
		$this->Meta_term->add( 'text', 'new-value', true );
		$this->assertEquals( array( 'text' ), $this->Meta_term->get( 'text' ) );
	}

	/**
	 * @group add
	 */
	public function test_add__user_using_unique() {
		$this->Meta_user->add( 'text', 'text', true );
		$this->assertEquals( array( 'text' ), $this->Meta_user->get( 'text' ) );
		$this->Meta_user->add( 'text', 'new-value', true );
		$this->assertEquals( array( 'text' ), $this->Meta_user->get( 'text' ) );
	}

	/**
	 * @group add
	 */
	public function test_add__option_using_unique() {
		$this->Meta_option->add( 'text', 'text', true );
		$this->assertEquals( array( 'text' ), $this->Meta_option->get( 'text' ) );
		$this->Meta_option->add( 'text', 'new-value', true );
		$this->assertEquals( array( 'text' ), $this->Meta_option->get( 'text' ) );
	}

	/**
	 * @group delete
	 */
	public function test_delete__post() {
		$this->Meta_post->add( 'text', '1' );
		$this->Meta_post->add( 'text', '2' );
		$this->Meta_post->delete();
		$this->assertSame( array( '1', '2' ), $this->Meta_post->get( 'text' ) );
	}

	/**
	 * @group delete
	 */
	public function test_delete__term() {
		$this->Meta_term->add( 'text', '1' );
		$this->Meta_term->add( 'text', '2' );
		$this->Meta_term->delete();
		$this->assertSame( array( '1', '2' ), $this->Meta_term->get( 'text' ) );
	}

	/**
	 * @group delete
	 */
	public function test_delete__user() {
		$this->Meta_user->add( 'text', '1' );
		$this->Meta_user->add( 'text', '2' );
		$this->Meta_user->delete();
		$this->assertSame( array( '1', '2' ), $this->Meta_user->get( 'text' ) );
	}

	/**
	 * @group delete
	 */
	public function test_delete__option() {
		$this->Meta_option->add( 'text', '1' );
		$this->Meta_option->add( 'text', '2' );
		$this->Meta_option->delete();
		$this->assertSame( array( '1', '2' ), $this->Meta_option->get( 'text' ) );
	}

	/**
	 * @group delete
	 */
	public function test_delete__post_using_key() {
		$this->Meta_post->add( 'text', '1' );
		$this->Meta_post->add( 'text', '2' );
		$this->Meta_post->delete( 'text' );
		$this->assertSame( array(), $this->Meta_post->get( 'text' ) );
	}

	/**
	 * @group delete
	 */
	public function test_delete__term_using_key() {
		$this->Meta_term->add( 'text', '1' );
		$this->Meta_term->add( 'text', '2' );
		$this->Meta_term->delete( 'text' );
		$this->assertSame( array(), $this->Meta_term->get( 'text' ) );
	}

	/**
	 * @group delete
	 */
	public function test_delete__user_using_key() {
		$this->Meta_user->add( 'text', '1' );
		$this->Meta_user->add( 'text', '2' );
		$this->Meta_user->delete( 'text' );
		$this->assertSame( array(), $this->Meta_user->get( 'text' ) );
	}

	/**
	 * @group delete
	 */
	public function test_delete__option_using_key() {
		$this->Meta_option->add( 'text', '1' );
		$this->Meta_option->add( 'text', '2' );
		$this->Meta_option->delete( 'text' );
		$this->assertSame( array(), $this->Meta_option->get( 'text' ) );
	}

	/**
	 * @group delete
	 */
	public function test_delete__post_using_value() {
		$this->Meta_post->add( 'text', '1' );
		$this->Meta_post->add( 'text', '2' );
		$this->Meta_post->delete( 'text', '2' );
		$this->assertSame( array( '1' ), $this->Meta_post->get( 'text' ) );
	}

	/**
	 * @group delete
	 */
	public function test_delete__term_using_value() {
		$this->Meta_term->add( 'text', '1' );
		$this->Meta_term->add( 'text', '2' );
		$this->Meta_term->delete( 'text', '2' );
		$this->assertSame( array( '1' ), $this->Meta_term->get( 'text' ) );
	}

	/**
	 * @group delete
	 */
	public function test_delete__user_using_value() {
		$this->Meta_user->add( 'text', '1' );
		$this->Meta_user->add( 'text', '2' );
		$this->Meta_user->delete( 'text', '2' );
		$this->assertSame( array( '1' ), $this->Meta_user->get( 'text' ) );
	}

	/**
	 * @group delete
	 */
	public function test_delete__option_using_value() {
		$this->Meta_option->add( 'text', '1' );
		$this->Meta_option->add( 'text', '2' );
		$this->Meta_option->delete( 'text', '2' );
		$this->assertSame( array( '1' ), $this->Meta_option->get( 'text' ) );
	}

	/**
	 * @group save
	 */
	public function test_save__post() {
		$POST = $this->_return_post_data_for_save( SCF_Config::NAME );
		$this->Meta_post->save( $POST );
		$this->assertEquals( array( 1, 2 ), SCF::get( 'checkbox', $this->post_id ) );
		$this->assertEquals(
			array(
				array(
					'repeat-text'     => '1',
					'repeat-checkbox' => array( 1, 2 ),
				),
				array(
					'repeat-text'     => '2',
					'repeat-checkbox' => array( 2, 3 ),
				),
			),
			SCF::get( 'group', $this->post_id )
		);
	}

	/**
	 * @group save
	 */
	public function test_save__term() {
		$POST = $this->_return_post_data_for_save( SCF_Config::NAME );
		$this->Meta_term->save( $POST );
		$this->assertEquals( array( 1, 2 ), SCF::get_term_meta( $this->term_id, 'category', 'checkbox' ) );
		$this->assertEquals(
			array(
				array(
					'repeat-text'     => '1',
					'repeat-checkbox' => array( 1, 2 ),
				),
				array(
					'repeat-text'     => '2',
					'repeat-checkbox' => array( 2, 3 ),
				),
			),
			SCF::get_term_meta( $this->term_id, 'category', 'group' )
		);
	}

	/**
	 * @group save
	 */
	public function test_save__user() {
		$POST = $this->_return_post_data_for_save( SCF_Config::NAME );
		$this->Meta_user->save( $POST );
		$this->assertEquals( array( 1, 2 ), SCF::get_user_meta( $this->user_id, 'checkbox' ) );
		$this->assertEquals(
			array(
				array(
					'repeat-text'     => '1',
					'repeat-checkbox' => array( 1, 2 ),
				),
				array(
					'repeat-text'     => '2',
					'repeat-checkbox' => array( 2, 3 ),
				),
			),
			SCF::get_user_meta( $this->user_id, 'group' )
		);
	}

	/**
	 * @group save
	 */
	public function test_save__option() {
		$POST = $this->_return_post_data_for_save( SCF_Config::NAME );
		$this->Meta_option->save( $POST );
		$this->assertEquals( array( 1, 2 ), SCF::get_option_meta( $this->menu_slug, 'checkbox' ) );
		$this->assertEquals(
			array(
				array(
					'repeat-text'     => '1',
					'repeat-checkbox' => array( 1, 2 ),
				),
				array(
					'repeat-text'     => '2',
					'repeat-checkbox' => array( 2, 3 ),
				),
			),
			SCF::get_option_meta( $this->menu_slug, 'group' )
		);
	}

	/**
	 * @group save
	 */
	public function test_save__post_not_posting_metadata() {
		$POST = $this->_return_post_data_for_save( 'dummy' );
		$this->Meta_post->save( $POST );
		$this->assertSame( array(), SCF::get( 'checkbox', $this->post_id ) );
		$this->assertSame(
			array(
				array(
					'repeat-text'     => '',
					'repeat-checkbox' => array(),
				),
			),
			SCF::get( 'group', $this->post_id )
		);
	}

	/**
	 * @group save
	 */
	public function test_save__term_not_posting_metadata() {
		$POST = $this->_return_post_data_for_save( 'dummy' );
		$this->Meta_term->save( $POST );
		$this->assertSame( array(), SCF::get_term_meta( $this->term_id, 'category', 'checkbox' ) );
		$this->assertSame(
			array(
				array(
					'repeat-text'     => '',
					'repeat-checkbox' => array(),
				),
			),
			SCF::get_term_meta( $this->term_id, 'category', 'group' )
		);
	}

	/**
	 * @group save
	 */
	public function test_save__user_not_posting_metadata() {
		$POST = $this->_return_post_data_for_save( 'dummy' );
		$this->Meta_user->save( $POST );
		$this->assertSame( array(), SCF::get_user_meta( $this->user_id, 'checkbox' ) );
		$this->assertSame(
			array(
				array(
					'repeat-text'     => '',
					'repeat-checkbox' => array(),
				),
			),
			SCF::get_user_meta( $this->user_id, 'group' )
		);
	}

	/**
	 * @group save
	 */
	public function test_save__option_not_posting_metadata() {
		$POST = $this->_return_post_data_for_save( 'dummy' );
		$this->Meta_option->save( $POST );
		$this->assertSame( array(), SCF::get_option_meta( $this->menu_slug, 'checkbox' ) );
		$this->assertSame(
			array(
				array(
					'repeat-text'     => '',
					'repeat-checkbox' => array(),
				),
			),
			SCF::get_option_meta( $this->menu_slug, 'group' )
		);
	}

	/**
	 * @group restore
	 */
	public function test_restore() {
		$POST = $this->_return_post_data_for_save( SCF_Config::NAME );

		$this->Meta_revision->save( $POST );
		$this->Meta_post->restore( get_post( $this->revision_id ) );
		$this->assertEquals( array( 1, 2 ), SCF::get( 'checkbox', $this->post_id ) );
		$this->assertEquals(
			array(
				array(
					'repeat-text'     => '1',
					'repeat-checkbox' => array( 1, 2 ),
				),
				array(
					'repeat-text'     => '2',
					'repeat-checkbox' => array( 2, 3 ),
				),
			),
			SCF::get( 'group', $this->post_id )
		);

		$this->assertNull( $this->Meta_post->restore( get_term( $this->term_id, 'category' ) ) );
		$this->assertNull( $this->Meta_post->restore( get_userdata( $this->user_id ) ) );
		$this->assertNull( $this->Meta_post->restore( SCF::generate_option_object( $this->menu_slug ) ) );
	}

	/**
	 * Return post date for save.
	 *
	 * @param string $key SCF_Config::NAME.
	 */
	protected function _return_post_data_for_save( $key ) {
		return array(
			$key => array(
				'text'            => array( 'text' ),
				'checkbox'        => array(
					array( 1, 2 ),
				),
				'repeat-text'     => array(
					array( '1', '2' ),
				),
				'repeat-checkbox' => array(
					array( 1, 2 ),
					array( 2, 3 ),
				),
			),
		);
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
		if (
			( 'post' === $type && $id === $this->post_id ) ||
			( 'post' === $type && $id === $this->new_post_id ) ||
			( 'post' === $type && $id === $this->revision_id ) ||
			( 'editor' === $type ) ||
			( 'subscriber' === $type ) ||
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
