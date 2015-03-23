<?php
class SmartCustomFieldsTest extends WP_UnitTestCase {

	/**
	 * @var int
	 */
	protected $post_id;

	/**
	 * @var int
	 */
	protected $revision_id;

	/**
	 * setUp
	 */
	public function setUp() {
		parent::setUp();
		// カスタムフィールドを設定するための投稿
		$this->post_id = $this->factory->post->create( array(
			'post_type' => 'post',
		) );
		// リビジョン用として投稿を準備
		$this->revision_id = $this->factory->post->create( array(
			'post_type'   => 'revision',
			'post_parent' => $this->post_id,
		) );
		// カスタムフィールドを設定するためのユーザー
		$this->user_id = $this->factory->user->create( array( 'role' => 'editor' ) );

		add_filter( 'smart-cf-register-fields', array( $this, '_register' ), 10, 4 );
	}

	/**
	 * @backupStaticAttributes enabled
	 * @group get
	 */
	public function test_get_Post_IDが取得できないときはnull() {
		$this->assertNull( SCF::get( 'text'     , false ) );
		$this->assertNull( SCF::get( 'text3'    , false ) );
		$this->assertNull( SCF::get( 'checkbox' , false ) );
		$this->assertNull( SCF::get( 'checkbox3', false ) );
	}

	/**
	 * @backupStaticAttributes enabled
	 * @group get
	 */
	public function test_get_メタデータが保存されていないときは空値() {
		$this->assertSame( ''     , SCF::get( 'text'     , $this->post_id ) );
		$this->assertSame( array(), SCF::get( 'text3'    , $this->post_id ) );
		$this->assertSame( array(), SCF::get( 'checkbox' , $this->post_id ) );
		$this->assertSame( array(), SCF::get( 'checkbox3', $this->post_id ) );
		$this->assertSame(
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
	 * @group get
	 */
	public function test_get_存在しないカスタムフィールドの場合はnull() {
		$this->assertNull( SCF::get( 'not_exist', $this->post_id ) );
	}

	/**
	 * @backupStaticAttributes enabled
	 * @group gets
	 */
	public function test_gets_Post_IDが取得できないときはnull() {
		$this->assertNull( SCF::gets( false ) );
	}

	/**
	 * @backupStaticAttributes enabled
	 * @group get_user_meta
	 */
	public function test_get_user_meta_User_IDが取得できないときはnull() {
		$this->assertNull( SCF::get_user_meta( false, 'text' ) );
		$this->assertNull( SCF::get_user_meta( false, 'checkbox' ) );
		$this->assertNull( SCF::get_user_meta( false, 'text3' ) );
		$this->assertNull( SCF::get_user_meta( false, 'checkbox3' ) );
		$this->assertNull( SCF::get_user_meta( false ) );
	}

	/**
	 * @backupStaticAttributes enabled
	 * @group get_user_meta
	 */
	public function test_get_user_meta_メタデータが保存されていないときは空値() {
		$this->assertSame( ''     , SCF::get_user_meta( $this->user_id, 'text' ) );
		$this->assertSame( array(), SCF::get_user_meta( $this->user_id, 'text3' ) );
		$this->assertSame( array(), SCF::get_user_meta( $this->user_id, 'checkbox' ) );
		$this->assertSame( array(), SCF::get_user_meta( $this->user_id, 'checkbox3' ) );
		$this->assertSame(
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
			SCF::get_user_meta( $this->user_id )
		);
	}

	/**
	 * @backupStaticAttributes enabled
	 * @group get_user_meta
	 */
	public function test_get_user_meta_存在しないカスタムフィールドの場合はnull() {
		$this->assertNull( SCF::get_user_meta( $this->user_id, 'not_exist' ) );
	}

	/**
	 * @backupStaticAttributes enabled
	 * @group get
	 */
	public function test_get_非繰り返し内の単一値項目() {
		update_post_meta( $this->post_id, 'text', 'hoge' );
		$this->assertEquals( 'hoge', SCF::get( 'text', $this->post_id ) );
	}

	/**
	 * @backupStaticAttributes enabled
	 * @group get
	 */
	public function test_get_非繰り返し内の複数値項目() {
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
	 * @group get
	 */
	public function test_get_繰り返し内の単一値項目() {
		add_post_meta( $this->post_id, 'text3', 1 );
		add_post_meta( $this->post_id, 'text3', 2 );
		$this->assertEquals(
			array( 1, 2 ),
			SCF::get( 'text3', $this->post_id )
		);
	}

	/**
	 * @backupStaticAttributes enabled
	 * @group get
	 */
	public function test_get_繰り返し内の複数値項目() {
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
	 * @group gets
	 */
	public function test_gets() {
		update_post_meta( $this->post_id, 'text', 'hoge' );
		add_post_meta( $this->post_id, 'checkbox', 1 );
		add_post_meta( $this->post_id, 'checkbox', 2 );

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
					1, 2,
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
	 * @group get_user_meta
	 */
	public function test_get_user_meta_繰り返し内の単一値項目() {
		add_user_meta( $this->user_id, 'text3', 1 );
		add_user_meta( $this->user_id, 'text3', 2 );
		$this->assertEquals(
			array( 1, 2 ),
			SCF::get_user_meta( $this->user_id, 'text3' )
		);
	}

	/**
	 * @backupStaticAttributes enabled
	 * @group get_user_meta
	 */
	public function test_get_user_meta_繰り返し内の複数値項目() {
		// ループ内のチェックボックス（複数値項目）は必ずこのメタデータを持つ
		update_user_meta( $this->user_id, SCF_Config::PREFIX . 'repeat-multiple-data', array(
			'checkbox3' => array( 1, 2 ),
		) );

		add_user_meta( $this->user_id, 'checkbox3', 1 );
		add_user_meta( $this->user_id, 'checkbox3', 2 );
		add_user_meta( $this->user_id, 'checkbox3', 3 );
		$this->assertEquals(
			array(
				array( 1 ),
				array( 2, 3 ),
			),
			SCF::get_user_meta( $this->user_id, 'checkbox3' )
		);
	}

	/**
	 * @backupStaticAttributes enabled
	 */
	public function test_get_user_meta_all() {
		update_user_meta( $this->user_id, 'text', 'hoge' );
		add_user_meta( $this->user_id, 'checkbox', 1 );
		add_user_meta( $this->user_id, 'checkbox', 2 );

		// ループ内のチェックボックス（複数値項目）は必ずこのメタデータを持つ
		update_user_meta( $this->user_id, SCF_Config::PREFIX . 'repeat-multiple-data', array(
			'checkbox3' => array( 1, 2 ),
		) );

		add_user_meta( $this->user_id, 'checkbox3', 1 );
		add_user_meta( $this->user_id, 'checkbox3', 2 );
		add_user_meta( $this->user_id, 'checkbox3', 3 );

		$this->assertEquals(
			array(
				'text'     => 'hoge',
				'checkbox' => array(
					1, 2,
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
			SCF::get_user_meta( $this->user_id )
		);
	}

	/**
	 * @backupStaticAttributes enabled
	 * @group get_field
	 */
	public function test_get_field_フィールドが存在しないときはnull() {
		$this->go_to( $this->post_id );
		$Field = SCF::get_field( 'post', 'not_exist' );
		$this->assertNull( $Field );
	}

	/**
	 * @backupStaticAttributes enabled
	 * @group get_field
	 */
	public function test_get_field_フィールドが存在する() {
		$this->go_to( $this->post_id );
		$Field = SCF::get_field( get_post_type( $this->post_id ), 'text' );
		$this->assertEquals( 'text', $Field->get( 'name' ) );
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

		$settings_posts = SCF::get_settings_posts( get_post( $post_id ) );
		foreach ( $settings_posts as $settings_post ) {
			$this->assertEquals( 'test_settings_post', $settings_post->post_title );
		}
	}

	/**
	 * @backupStaticAttributes enabled
	 */
	public function test_get_settings_posts_cache() {
		$post_id = $this->factory->post->create( array(
			'post_type'  => SCF_Config::NAME,
			'post_title' => 'test_settings_post',
		) );
		update_post_meta( $post_id, SCF_Config::PREFIX . 'condition', array( 'post' ) );

		$this->assertNull( SCF::get_settings_posts_cache( get_post( $post_id ) ) );

		$settings_posts = SCF::get_settings_posts( get_post( $post_id ) );
		$settings_posts_cache = SCF::get_settings_posts_cache( get_post( $post_id ) );
		foreach ( $settings_posts_cache as $settings_post ) {
			$this->assertEquals( 'test_settings_post', $settings_post->post_title );
		}
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

		$settings = SCF::get_settings( get_post( $this->post_id ) );
		foreach ( $settings as $Setting ) {
			$this->assertTrue( in_array( $Setting->get_title(), array( 'test_settings', 'Register Test' ) ) );
		}
	}

	/**
	 * @backupStaticAttributes enabled
	 * @group get_settings
	 */
	public function test_get_settings_投稿タイプとPost_IDが一致する() {
		$post_id = $this->factory->post->create( array(
			'post_type'  => SCF_Config::NAME,
			'post_title' => 'test_settings',
		) );

		$settings = SCF::get_settings( get_post( $this->post_id ) );
		foreach ( $settings as $Setting ) {
			$this->assertTrue( is_a( $Setting, 'Smart_Custom_Fields_Setting' ) );
		}
	}

	/**
	 * @backupStaticAttributes enabled
	 * @group get_settings
	 */
	public function test_get_settings_投稿タイプが一致しない() {
		$post_id = $this->factory->post->create( array(
			'post_type'  => SCF_Config::NAME,
			'post_title' => 'test_settings',
		) );

		$settings = SCF::get_settings( get_post( 99999 ) );
		$this->assertSame( array(), $settings );
	}

	/**
	 * @backupStaticAttributes enabled
	 * @group get_settings
	 */
	public function test_get_settings_ロールが一致する() {
		$post_id = $this->factory->post->create( array(
			'post_type'  => SCF_Config::NAME,
			'post_title' => 'test_settings',
		) );

		$settings = SCF::get_settings( get_userdata( $this->user_id ) );
		$this->assertTrue( is_a( current( $settings ), 'Smart_Custom_Fields_Setting' ) );
	}

	/**
	 * @backupStaticAttributes enabled
	 * @group get_settings
	 */
	public function test_get_settings_ロールが一致しない() {
		$post_id = $this->factory->post->create( array(
			'post_type'  => SCF_Config::NAME,
			'post_title' => 'test_settings',
		) );

		$settings = SCF::get_settings( get_userdata( 99999 ) );
		$this->assertSame( array(), $settings );
	}

	/**
	 * @backupStaticAttributes enabled
	 * @group get_settings_cache
	 */
	public function test_get_settings_cache_投稿タイプが一致する() {
		$post_id = $this->factory->post->create( array(
			'post_type'  => SCF_Config::NAME,
			'post_title' => 'test_settings',
		) );
		update_post_meta( $post_id, SCF_Config::PREFIX . 'condition', array( 'post' ) );

		// キャッシュに保存
		$settings = SCF::get_settings( get_post( $this->post_id ) );

		$Setting = SCF::get_settings_cache( $post_id );
		$this->assertTrue( is_a( $Setting, 'Smart_Custom_Fields_Setting' ) );
	}

	/**
	 * @backupStaticAttributes enabled
	 * @group get_settings_cache
	 */
	public function test_get_settings_cache_投稿タイプが一致しない() {
		$post_id = $this->factory->post->create( array(
			'post_type'  => SCF_Config::NAME,
			'post_title' => 'test_settings',
		) );
		update_post_meta( $post_id, SCF_Config::PREFIX . 'condition', array( 'page' ) );

		// キャッシュに保存
		$settings = SCF::get_settings( get_post( $this->post_id ) );

		$Setting = SCF::get_settings_cache( $post_id );
		$this->assertNull( $Setting );
	}

	/**
	 * @backupStaticAttributes enabled
	 * @group get_settings_cache
	 */
	public function test_get_settings_cache_投稿タイプとPost_IDが一致する() {
		$post_id = $this->factory->post->create( array(
			'post_type'  => SCF_Config::NAME,
			'post_title' => 'test_settings',
		) );
		update_post_meta( $post_id, SCF_Config::PREFIX . 'condition', array( 'post' ) );
		update_post_meta( $post_id, SCF_Config::PREFIX . 'condition-post-ids', $this->post_id );

		// キャッシュに保存
		$settings = SCF::get_settings( get_post( $this->post_id ) );

		$Setting = SCF::get_settings_cache( $post_id, get_post( $this->post_id ) );
		$this->assertTrue( is_a( $Setting, 'Smart_Custom_Fields_Setting' ) );
	}

	/**
	 * @backupStaticAttributes enabled
	 * @group get_settings_cache
	 */
	public function test_get_settings_cache_投稿タイプは一致するがPost_IDは一致しない() {
		$post_id = $this->factory->post->create( array(
			'post_type'  => SCF_Config::NAME,
			'post_title' => 'test_settings',
		) );
		update_post_meta( $post_id, SCF_Config::PREFIX . 'condition', array( 'post' ) );
		update_post_meta( $post_id, SCF_Config::PREFIX . 'condition-post-ids', '99999' );

		// キャッシュに保存
		$settings = SCF::get_settings( get_post( $this->post_id ) );

		$Setting = SCF::get_settings_cache( $post_id, get_post( $this->post_id ) );
		$this->assertFalse( $Setting );
	}

	/**
	 * @backupStaticAttributes enabled
	 * @group get_settings_cache
	 */
	public function test_get_settings_cache_ロールが一致する() {
		$post_id = $this->factory->post->create( array(
			'post_type'  => SCF_Config::NAME,
			'post_title' => 'test_settings',
		) );
		update_post_meta( $post_id, SCF_Config::PREFIX . 'roles', array( 'editor' ) );

		// キャッシュに保存
		$settings = SCF::get_settings( get_userdata( $this->user_id ) );

		$Setting = SCF::get_settings_cache( $post_id, get_userdata( $this->user_id ) );
		$this->assertTrue( is_a( $Setting, 'Smart_Custom_Fields_Setting' ) );
	}

	/**
	 * @backupStaticAttributes enabled
	 * @group get_settings_cache
	 */
	public function test_get_settings_cache_when_ロールが一致しない() {
		$post_id = $this->factory->post->create( array(
			'post_type'  => SCF_Config::NAME,
			'post_title' => 'test_settings',
		) );
		update_post_meta( $post_id, SCF_Config::PREFIX . 'roles', array( 'administrator' ) );

		// キャッシュに保存
		$settings = SCF::get_settings( get_userdata( $this->user_id ) );

		$Setting = SCF::get_settings_cache( $post_id, get_userdata( $this->user_id ) );
		$this->assertNull( $Setting );
	}

	/**
	 * @backupStaticAttributes enabled
	 */
	public function test_wp_restore_post_revision() {
		// 投稿のメタデータ
		add_post_meta( $this->post_id, 'text', 'text' );
		add_post_meta( $this->post_id, 'checkbox', 'check' );
		add_post_meta( $this->post_id, 'text3', 'loop-text' );

		// リビジョンのメタデータ
		add_metadata( 'post', $this->revision_id, 'text', 'text-2' );
		add_metadata( 'post', $this->revision_id, SCF_Config::PREFIX . 'repeat-multiple-data', array(
			'checkbox3' => array( 1, 2 ),
		) );
		add_metadata( 'post', $this->revision_id, 'checkbox3', 'loop-check-1' );
		add_metadata( 'post', $this->revision_id, 'checkbox3', 'loop-check-2' );
		add_metadata( 'post', $this->revision_id, 'checkbox3', 'loop-check-3' );

		$Revision = new Smart_Custom_Fields_Revisions();
		$Revision->wp_restore_post_revision( $this->post_id, $this->revision_id );

		$this->assertEquals( 'text-2', SCF::get( 'text', $this->post_id ) );
		$this->assertSame( array(), SCF::get( 'checkbox', $this->post_id ) );
		$this->assertEquals(
			array(
				array(
					'loop-check-1',
				),
				array(
					'loop-check-2', 'loop-check-3',
				),
			), SCF::get( 'checkbox3', $this->post_id )
		);
		$this->assertEquals(
			array(
				'checkbox3' => array( 1, 2 ),
			),
			get_post_meta( $this->post_id, SCF_Config::PREFIX . 'repeat-multiple-data', true )
		);
	}

	/**
	 * @backupStaticAttributes enabled
	 */
	public function test_wp_insert_post_Post_IDがrevisionのときはnull() {
		$_POST[SCF_Config::NAME] = array(
			'text' => 'text',
		);
		$Revision = new Smart_Custom_Fields_Revisions();
		$this->assertNull( $Revision->wp_insert_post( $this->post_id ) );
	}

	/**
	 * @group get_type
	 */
	public function test_get_type_objectが空のときはnull() {
		$object = null;
		$Meta = new Smart_Custom_Fields_Meta( $object );
		$type = $Meta->get_type();
		$this->assertNull( $type );
	}

	/**
	 * @group get_type
	 */
	public function test_get_type_objectがpost() {
		$object = get_post( $this->post_id );
		$Meta = new Smart_Custom_Fields_Meta( $object );
		$type = $Meta->get_type();
		$this->assertEquals( get_post_type( $this->post_id ), $type );
	}

	/**
	 * @group get_type
	 */
	public function test_get_type_objectが存在しないpostのときはnull() {
		$object = get_post( 99999 );
		$Meta = new Smart_Custom_Fields_Meta( $object );
		$type = $Meta->get_type();
		$this->assertNull( $type );
	}

	/**
	 * @group get_type
	 */
	public function test_get_type_objectがpost_typeを持っている() {
		$Post = new stdClass();
		$Post->post_type = 'my-post-type';
		$object = new WP_Post( $Post );
		$Meta = new Smart_Custom_Fields_Meta( $object );
		$type = $Meta->get_type();
		$this->assertEquals( 'my-post-type', $type );
	}

	/**
	 * @group get_type
	 */
	public function test_get_type_objectがrevisoinのときはrevision() {
		$object = get_post( $this->revision_id );
		$Meta = new Smart_Custom_Fields_Meta( $object );
		$type = $Meta->get_type();
		$this->assertEquals( get_post_type( $this->revision_id ), $type );
	}

	/**
	 * @group get_type
	 */
	public function test_get_type_objectがrevisionでaccept_revisionがfalseのときは親投稿のpost_tye() {
		$object = get_post( $this->revision_id );
		$Meta = new Smart_Custom_Fields_Meta( $object );
		$type = $Meta->get_type( false );
		$this->assertEquals( get_post_type( wp_is_post_revision( $this->revision_id ) ), $type );
	}

	/**
	 * @group get_type
	 */
	public function test_get_type_objectがeditor() {
		$object = get_userdata( $this->user_id );
		$Meta = new Smart_Custom_Fields_Meta( $object );
		$type = $Meta->get_type();
		$this->assertEquals( 'editor', $type );
	}

	/**
	 * @group get_type
	 */
	public function test_get_type_objectが存在しないロールのときはnull() {
		$object = get_userdata( 99999 );
		$Meta = new Smart_Custom_Fields_Meta( $object );
		$type = $Meta->get_type();
		$this->assertNull( $type );
	}

	/**
	 * @backupStaticAttributes enabled
	 * @group save
	 */
	public function test_save() {
		$object = get_post( $this->post_id );
		$Meta = new Smart_Custom_Fields_Meta( $object );
		$POST = array(
			SCF_Config::NAME => array(
				'checkbox'  => array(
					array( 1, 2 ),
				),
				'checkbox3' => array(
					array( 1, 2 ),
					array( 2, 3 ),
				),
			),
		);
		$Meta->save( $POST );
		$this->assertEquals(
			array( 1, 2 ),
			SCF::get( 'checkbox', $this->post_id )
		);
		$this->assertEquals(
			array(
				array( 1, 2 ),
				array( 2, 3 ),
			),
			SCF::get( 'checkbox3', $this->post_id )
		);
		$this->assertEquals(
			array(
				'checkbox3' => array( 2, 2 ),
			),
			get_post_meta( $this->post_id, SCF_Config::PREFIX . 'repeat-multiple-data', true )
		);
	}

	/**
	 * @backupStaticAttributes enabled
	 * @group save
	 */
	public function test_save_SCFのカスタムフィールドのデータが送信されていない() {
		$object = get_post( $this->post_id );
		$Meta = new Smart_Custom_Fields_Meta( $object );
		$POST = array(
			'hoge' => array(
				'checkbox'  => array(
					array( 1, 2 ),
				),
				'checkbox3' => array(
					array( 1, 2 ),
					array( 2, 3 ),
				),
			),
		);
		$Meta->save( $POST );
		$this->assertSame( array(), SCF::get( 'checkbox' , $this->post_id ) );
		$this->assertSame( array(), SCF::get( 'checkbox3', $this->post_id ) );
		$this->assertSame( '', get_post_meta( $this->post_id, SCF_Config::PREFIX . 'repeat-multiple-data', true ) );
	}

	/**
	 * フック経由でカスタムフィールドを設定
	 *
	 * @param array $settings 管理画面で設定された Smart_Custom_Fields_Setting の配列
	 * @param string $type 投稿タイプ or ロール
	 * @param int $id 投稿ID or ユーザーID
	 * @param string $meta_type メタデータのタイプ。post or user
	 * @return array
	 */
	public function _register( $settings, $type, $id, $meta_type ) {
		// SCF::add_setting( 'ユニークなID', 'メタボックスのタイトル' );
		if ( ( $type === 'post' && ( $id === $this->post_id || $id === $this->revision_id ) ) || ( $type === 'editor' ) ) {
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
