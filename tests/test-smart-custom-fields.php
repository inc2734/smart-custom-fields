<?php
class SmartCustomFieldsTest extends WP_UnitTestCase {

	protected $post_id;

	public function setUp() {
		parent::setUp();
		$this->post_id = $this->factory->post->create();
		$this->user_id = $this->factory->user->create( array( 'role' => 'editor' ) );
		add_filter( 'smart-cf-register-fields', array( $this, '_register' ), 10, 4 );
	}

	/**
	 * @backupStaticAttributes enabled
	 */
	public function test_when_not_saved_metadata() {
		// 通常、投稿を表示したときは post_id が自動的に取得されるけど、ここでは取得できないので null
		$this->assertNull( SCF::get( 'text' ) );
		$this->assertNull( SCF::get( 'checkbox' ) );
		$this->assertEquals( array(), SCF::gets() );
		// 通常はこっち
		$this->assertEquals( '', SCF::get( 'text', $this->post_id ) );
		$this->assertEquals( array(), SCF::get( 'checkbox', $this->post_id ) );
		$this->assertEquals(
			array(
				'text'         => '',
				'checkbox'     => array(),
				'group-name-3' => array(
					array(
						'text3'     => '',
						'checkbox3' => array(),
					),
				),
			),
			SCF::gets( $this->post_id )
		);
		// 存在しないものは null
		$this->assertNull( SCF::get( 'not_exist', $this->post_id ) );
	}

	/**
	 * @backupStaticAttributes enabled
	 */
	public function test_when_saved_norepeat_text() {
		update_post_meta( $this->post_id, 'text', 'hoge' );
		$this->assertEquals( 'hoge', SCF::get( 'text', $this->post_id ) );
	}

	/**
	 * @backupStaticAttributes enabled
	 */
	public function test_when_saved_norepeat_checkbox() {
		add_post_meta( $this->post_id, 'checkbox', 1 );
		add_post_meta( $this->post_id, 'checkbox', 2 );
		add_post_meta( $this->post_id, 'checkbox', 3 );
		add_post_meta( $this->post_id, 'checkbox', 4 );
		$this->assertEquals(
			array( 1, 2, 3, 4 ),
			SCF::get( 'checkbox', $this->post_id )
		);
	}

	/**
	 * @backupStaticAttributes enabled
	 */
	public function test_when_saved_repeat_text() {
		add_post_meta( $this->post_id, 'text3', 1 );
		add_post_meta( $this->post_id, 'text3', 2 );
		$this->assertEquals(
			array( 1, 2 ),
			SCF::get( 'text3', $this->post_id )
		);
	}

	/**
	 * @backupStaticAttributes enabled
	 */
	public function test_when_saved_repeat_checkbox() {
		// ループ内のチェックボックス（複数値項目）は必ずこのメタデータを持つ
		update_post_meta( $this->post_id, SCF_Config::PREFIX . 'repeat-multiple-data', array(
			'checkbox3' => array( 1, 2 ),
		) );

		add_post_meta( $this->post_id, 'checkbox3', 1 );
		add_post_meta( $this->post_id, 'checkbox3', 2 );
		add_post_meta( $this->post_id, 'checkbox3', 3 );
		$this->assertEquals(
			array(
				array( 1 ),
				array( 2, 3 ),
			),
			SCF::get( 'checkbox3', $this->post_id )
		);
	}

	/**
	 * @backupStaticAttributes enabled
	 */
	public function test_gets_when_not_saved() {
		$this->assertEquals(
			array(
				'text'         => '',
				'checkbox'     => array(),
				'group-name-3' => array(
					array(
						'text3'     => '',
						'checkbox3' => array(),
					),
				),
			),
			SCF::gets( $this->post_id )
		);
	}

	/**
	 * @backupStaticAttributes enabled
	 */
	public function test_gets_when_saved() {
		update_post_meta( $this->post_id, 'text', 'hoge' );
		add_post_meta( $this->post_id, 'checkbox', 1 );
		add_post_meta( $this->post_id, 'checkbox', 2 );
		add_post_meta( $this->post_id, 'checkbox', 3 );
		add_post_meta( $this->post_id, 'checkbox', 4 );

		// ループ内のチェックボックス（複数値項目）は必ずこのメタデータを持つ
		update_post_meta( $this->post_id, SCF_Config::PREFIX . 'repeat-multiple-data', array(
			'checkbox3' => array( 1, 2 ),
		) );

		add_post_meta( $this->post_id, 'checkbox3', 1 );
		add_post_meta( $this->post_id, 'checkbox3', 2 );
		add_post_meta( $this->post_id, 'checkbox3', 3 );

		$this->assertEquals(
			array(
				'text'     => 'hoge',
				'checkbox' => array(
					1, 2, 3, 4
				),
				'group-name-3' => array(
					array(
						'text3'     => '',
						'checkbox3' => array( 1 ),
					),
					array(
						'text3'     => '',
						'checkbox3' => array( 2, 3 ),
					),
				),
			),
			SCF::gets( $this->post_id )
		);
	}

	/**
	 * @backupStaticAttributes enabled
	 */
	public function test_get_field_when_exist() {
		$this->go_to( $this->post_id );
		$Field = SCF::get_field( 'post', 'text' );
		$this->assertEquals( 'text', $Field->get( 'name' ) );
	}

	/**
	 * @backupStaticAttributes enabled
	 */
	public function test_get_field_when_not_exist() {
		$this->go_to( $this->post_id );
		$Field = SCF::get_field( 'post', 'not_exist' );
		$this->assertNull( $Field );
	}

	/**
	 * @backupStaticAttributes enabled
	 */
	public function test_get_settings_posts() {
		$post_id = $this->factory->post->create( array(
			'post_type'  => SCF_Config::NAME,
			'post_title' => 'test_settings_post',
		) );
		update_post_meta( $post_id, SCF_Config::PREFIX . 'condition', array( 'post' ) );

		$this->assertNull( SCF::get_settings_posts_cache( 'post' ) );
		$settings_posts = SCF::get_settings_posts( 'post' );
		$this->assertEquals( 'test_settings_post', $settings_posts[0]->post_title );
		$settings_posts_cache = SCF::get_settings_posts_cache( 'post' );
		$this->assertEquals( 'test_settings_post', $settings_posts_cache[0]->post_title );
	}

	/**
	 * @backupStaticAttributes enabled
	 */
	public function test_add_settings() {
		$post_id = $this->factory->post->create( array(
			'post_type'  => SCF_Config::NAME,
			'post_title' => 'test_settings',
		) );
		update_post_meta( $post_id, SCF_Config::PREFIX . 'condition', array( 'post' ) );

		$this->assertNull( SCF::get_settings_cache( false ) );

		$settings = SCF::get_settings( 'post', $this->post_id );
		foreach ( $settings as $Setting ) {
			$this->assertTrue( in_array( $Setting->get_title(), array( 'test_settings', 'Register Test' ) ) );
		}
	}

	/**
	 * @backupStaticAttributes enabled
	 */
	public function test_get_settings_when_post_id_is_match() {
		$post_id = $this->factory->post->create( array(
			'post_type'  => SCF_Config::NAME,
			'post_title' => 'test_settings',
		) );
		$settings = SCF::get_settings( get_post_type( $this->post_id ), $this->post_id );
		$this->assertTrue( is_a( $settings[0], 'Smart_Custom_Fields_Setting' ) );
	}

	/**
	 * @backupStaticAttributes enabled
	 */
	public function test_get_settings_when_post_id_is_not_match() {
		$post_id = $this->factory->post->create( array(
			'post_type'  => SCF_Config::NAME,
			'post_title' => 'test_settings',
		) );
		$settings = SCF::get_settings( get_post_type( $this->post_id ), 99999 );
		$this->assertEquals( array(), $settings );
	}

	/**
	 * @backupStaticAttributes enabled
	 */
	public function test_get_settings_when_post_type_is_not_match() {
		$post_id = $this->factory->post->create( array(
			'post_type'  => SCF_Config::NAME,
			'post_title' => 'test_settings',
		) );
		$settings = SCF::get_settings( 'page', $this->post_id );
		$this->assertEquals( array(), $settings );
	}

	/**
	 * @backupStaticAttributes enabled
	 */
	public function test_get_settings_when_user_role_is_match() {
		$post_id = $this->factory->post->create( array(
			'post_type'  => SCF_Config::NAME,
			'post_title' => 'test_settings',
		) );
		$settings = SCF::get_settings( 'editor', $this->user_id );
		$this->assertTrue( is_a( $settings[0], 'Smart_Custom_Fields_Setting' ) );
	}

	/**
	 * @backupStaticAttributes enabled
	 */
	public function test_get_settings_when_user_role_is_not_match() {
		$post_id = $this->factory->post->create( array(
			'post_type'  => SCF_Config::NAME,
			'post_title' => 'test_settings',
		) );
		$settings = SCF::get_settings( 'administrator', $this->user_id );
		$this->assertEquals( array(), $settings );
	}

	/**
	 * @backupStaticAttributes enabled
	 */
	public function test_get_settings_cache_when_post_type_is_match() {
		$post_id = $this->factory->post->create( array(
			'post_type'  => SCF_Config::NAME,
			'post_title' => 'test_settings',
		) );
		update_post_meta( $post_id, SCF_Config::PREFIX . 'condition', array( 'post' ) );

		// キャッシュに保存
		$settings = SCF::get_settings( get_post_type( $this->post_id ), $this->post_id );

		$Setting = SCF::get_settings_cache( $post_id );
		$this->assertTrue( is_a( $Setting, 'Smart_Custom_Fields_Setting' ) );
	}

	/**
	 * @backupStaticAttributes enabled
	 */
	public function test_get_settings_cache_when_post_type_is_not_match() {
		$post_id = $this->factory->post->create( array(
			'post_type'  => SCF_Config::NAME,
			'post_title' => 'test_settings',
		) );
		update_post_meta( $post_id, SCF_Config::PREFIX . 'condition', array( 'page' ) );

		// キャッシュに保存
		$settings = SCF::get_settings( get_post_type( $this->post_id ), $this->post_id );

		$Setting = SCF::get_settings_cache( $post_id );
		$this->assertNull( $Setting );
	}

	/**
	 * @backupStaticAttributes enabled
	 */
	public function test_get_settings_cache_when_post_id_is_match() {
		$post_id = $this->factory->post->create( array(
			'post_type'  => SCF_Config::NAME,
			'post_title' => 'test_settings',
		) );
		update_post_meta( $post_id, SCF_Config::PREFIX . 'condition', array( 'post' ) );
		update_post_meta( $post_id, SCF_Config::PREFIX . 'condition-post-ids', $this->post_id );

		// キャッシュに保存
		$settings = SCF::get_settings( get_post_type( $this->post_id ), $this->post_id );

		$Setting = SCF::get_settings_cache( $post_id, 'post', $this->post_id );
		$this->assertTrue( is_a( $Setting, 'Smart_Custom_Fields_Setting' ) );
	}

	/**
	 * @backupStaticAttributes enabled
	 */
	public function test_get_settings_cache_when_post_id_is_not_match() {
		$post_id = $this->factory->post->create( array(
			'post_type'  => SCF_Config::NAME,
			'post_title' => 'test_settings',
		) );
		update_post_meta( $post_id, SCF_Config::PREFIX . 'condition', array( 'post' ) );
		update_post_meta( $post_id, SCF_Config::PREFIX . 'condition-post-ids', '99999' );

		// キャッシュに保存
		$settings = SCF::get_settings( get_post_type( $this->post_id ), $this->post_id );

		$Setting = SCF::get_settings_cache( $post_id, 'post', $this->post_id );
		$this->assertFalse( $Setting );
	}

	/**
	 * @backupStaticAttributes enabled
	 */
	public function test_get_settings_cache_when_role_is_match() {
		$post_id = $this->factory->post->create( array(
			'post_type'  => SCF_Config::NAME,
			'post_title' => 'test_settings',
		) );
		update_post_meta( $post_id, SCF_Config::PREFIX . 'roles', array( 'editor' ) );

		// キャッシュに保存
		$userdata = get_userdata( $this->user_id );
		$settings = SCF::get_settings( $userdata->roles[0], $this->user_id );

		$Setting = SCF::get_settings_cache( $post_id, 'user', $this->user_id );
		$this->assertTrue( is_a( $Setting, 'Smart_Custom_Fields_Setting' ) );
	}

	/**
	 * @backupStaticAttributes enabled
	 */
	public function test_get_settings_cache_when_role_is_not_match() {
		$post_id = $this->factory->post->create( array(
			'post_type'  => SCF_Config::NAME,
			'post_title' => 'test_settings',
		) );
		update_post_meta( $post_id, SCF_Config::PREFIX . 'roles', array( 'administrator' ) );

		// キャッシュに保存
		$userdata = get_userdata( $this->user_id );
		$settings = SCF::get_settings( $userdata->roles[0], $this->user_id );

		$Setting = SCF::get_settings_cache( $post_id, 'user', $this->user_id );
		$this->assertNull( $Setting );
	}

	public function _register( $settings, $type, $id, $meta_type ) {
		// SCF::add_setting( 'ユニークなID', 'メタボックスのタイトル' );
		if ( ( $type === 'post' && $id === $this->post_id ) || ( $type === 'editor' ) ) {
			$Setting = SCF::add_setting( 'id-1', 'Register Test' );
			// $Setting->add_group( 'ユニークなID', 繰り返し可能か, カスタムフィールドの配列 );
			$Setting->add_group( 'group-name-1', false, array(
				array(
					'name'  => 'text',
					'label' => 'text field',
					'type'  => 'text',
				),
			) );
			$Setting->add_group( 'group-name-2', false, array(
				array(
					'name'    => 'checkbox',
					'label'   => 'checkbox field',
					'type'    => 'check',
					'choices' => array( 1, 2, 3 ),
				),
			) );
			$Setting->add_group( 'group-name-3', true, array(
				array(
					'name'  => 'text3',
					'label' => 'text field 3',
					'type'  => 'text',
				),
				array(
					'name'    => 'checkbox3',
					'label'   => 'checkbox field 3',
					'type'    => 'check',
					'choices' => array( 1, 2, 3 ),
				),
			) );
			$settings[] = $Setting;
		}
		return $settings;
	}
}

