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
	 * setUp
	 */
	public function setUp() {
		parent::setUp();
		// カスタムフィールドを設定するための投稿
		$this->post_id = $this->factory->post->create( array(
			'post_type'   => 'post',
			'post_status' => 'publish',
		) );
		// カスタムフィールドを設定するための投稿（新規投稿時）
		$this->new_post_id = $this->factory->post->create( array(
			'post_type'   => 'post',
			'post_status' => 'auto-draft',
		) );
		// カスタムフィールドを設定するための投稿（下書き）
		$this->draft_post_id = $this->factory->post->create( array(
			'post_type'   => 'post',
			'post_status' => 'draft',
		) );
		// カスタムフィールドを設定するためのユーザー
		$this->user_id = $this->factory->user->create( array( 'role' => 'editor' ) );
		// カスタムフィールドを設定するためのターム
		$this->term_id = $this->factory->term->create( array( 'taxonomy' => 'category' ) );
		// コードでカスタムフィールドを定義
		add_filter( 'smart-cf-register-fields', array( $this, '_register' ), 10, 4 );

		SCF::clear_all_cache();
	}

	/**
	 * tearDown
	 */
	public function tearDown() {
		SCF::clear_all_cache();
	}

	/**
	 * @group get
	 */
	public function test_get__Post_IDが取得できないときはnull() {
		$this->assertNull( SCF::get( 'text'     , false ) );
		$this->assertNull( SCF::get( 'text3'    , false ) );
		$this->assertNull( SCF::get( 'checkbox' , false ) );
		$this->assertNull( SCF::get( 'checkbox3', false ) );
	}

	/**
	 * @group get
	 */
	public function test_get__未保存の投稿の場合はデフォルト値を返す() {
		$this->assertSame( ''     , SCF::get( 'text'     , $this->new_post_id ) );
		$this->assertSame( array(), SCF::get( 'text3'    , $this->new_post_id ) );
		$this->assertSame( array(), SCF::get( 'checkbox' , $this->new_post_id ) );
		$this->assertSame( array(), SCF::get( 'checkbox3', $this->new_post_id ) );
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
				'text-has-default'         => 'text default',
				'text-has-not-default'     => '',
				'checkbox-has-default'     => array( 'A', 'B' ),
				'checkbox-has-not-default' => array(),
			),
			SCF::gets( $this->new_post_id )
		);
	}

	/**
	 * @group get
	 */
	public function test_get__メタデータが保存されていないときはデフォルト値を返す() {
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
				'text-has-default'         => 'text default',
				'text-has-not-default'     => '',
				'checkbox-has-default'     => array( 'A', 'B' ),
				'checkbox-has-not-default' => array(),
			),
			SCF::gets( $this->post_id )
		);
	}

	/**
	 * @group get
	 */
	public function test_get__存在しないカスタムフィールドの場合はnull() {
		$this->assertNull( SCF::get( 'not_exist', $this->post_id ) );
	}

	/**
	 * @group gets
	 */
	public function test_gets__Post_IDが取得できないときはnull() {
		$this->assertNull( SCF::gets( false ) );
	}

	/**
	 * @group get_user_meta
	 */
	public function test_get_user_meta__User_IDが取得できないときはnull() {
		$this->assertNull( SCF::get_user_meta( false, 'text' ) );
		$this->assertNull( SCF::get_user_meta( false, 'checkbox' ) );
		$this->assertNull( SCF::get_user_meta( false, 'text3' ) );
		$this->assertNull( SCF::get_user_meta( false, 'checkbox3' ) );
		$this->assertNull( SCF::get_user_meta( false ) );
	}

	/**
	 * @group get_user_meta
	 */
	public function test_get_user_meta__メタデータが保存されていないときはデフォルト値を返す() {
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
				'text-has-default'         => 'text default',
				'text-has-not-default'     => '',
				'checkbox-has-default'     => array( 'A', 'B' ),
				'checkbox-has-not-default' => array(),
			),
			SCF::get_user_meta( $this->user_id )
		);
	}

	/**
	 * @group get_user_meta
	 */
	public function test_get_user_meta__存在しないカスタムフィールドの場合はnull() {
		$this->assertNull( SCF::get_user_meta( $this->user_id, 'not_exist' ) );
	}

	/**
	 * @group get_term_meta
	 */
	public function test_get_term_meta__User_IDが取得できないときはnull() {
		$this->assertNull( SCF::get_term_meta( false, 'category', 'text' ) );
		$this->assertNull( SCF::get_term_meta( false, 'category', 'checkbox' ) );
		$this->assertNull( SCF::get_term_meta( false, 'category', 'text3' ) );
		$this->assertNull( SCF::get_term_meta( false, 'category', 'checkbox3' ) );
		$this->assertNull( SCF::get_term_meta( false, 'category' ) );
	}

	/**
	 * @group get_term_meta
	 */
	public function test_get_term_meta__メタデータが保存されていないときは空値() {
		$this->assertSame( ''     , SCF::get_term_meta( $this->term_id, 'category', 'text' ) );
		$this->assertSame( array(), SCF::get_term_meta( $this->term_id, 'category', 'text3' ) );
		$this->assertSame( array(), SCF::get_term_meta( $this->term_id, 'category', 'checkbox' ) );
		$this->assertSame( array(), SCF::get_term_meta( $this->term_id, 'category', 'checkbox3' ) );
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
				'text-has-default'         => 'text default',
				'text-has-not-default'     => '',
				'checkbox-has-default'     => array( 'A', 'B' ),
				'checkbox-has-not-default' => array(),
			),
			SCF::get_term_meta( $this->term_id, 'category' )
		);
	}

	/**
	 * @group get_term_meta
	 */
	public function test_get_term_meta__存在しないカスタムフィールドの場合はnull() {
		$this->assertNull( SCF::get_term_meta( $this->term_id, 'category', 'not_exist' ) );
	}

	/**
	 * @group get
	 */
	public function test_get__非繰り返し内の単一値項目() {
		update_post_meta( $this->post_id, 'text', 'hoge' );
		$this->assertEquals( 'hoge', SCF::get( 'text', $this->post_id ) );
	}

	/**
	 * @group get
	 */
	public function test_get__非繰り返し内の複数値項目() {
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
	 * @group get
	 */
	public function test_get__繰り返し内の単一値項目() {
		add_post_meta( $this->post_id, 'text3', 1 );
		add_post_meta( $this->post_id, 'text3', 2 );
		$this->assertEquals(
			array( 1, 2 ),
			SCF::get( 'text3', $this->post_id )
		);
	}

	/**
	 * @group get
	 */
	public function test_get__繰り返し内の複数値項目() {
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
	 * @group gets
	 */
	public function test_gets__未保存の投稿の場合はデフォルト値を返す() {
		$this->assertEquals(
			array(
				'text'     => '',
				'checkbox' => array(),
				'group-name-3' => array(
					array(
						'text3'     => '',
						'checkbox3' => array(),
					),
				),
				'text-has-default'         => 'text default',
				'text-has-not-default'     => '',
				'checkbox-has-default'     => array( 'A', 'B' ),
				'checkbox-has-not-default' => array(),
			),
			SCF::gets( $this->new_post_id )
		);
	}

	/**
	 * @group gets
	 */
	public function test_gets__メタデータが保存されていないときは空値() {
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
				'text-has-default'         => '',
				'text-has-not-default'     => '',
				'checkbox-has-default'     => array(),
				'checkbox-has-not-default' => array(),
			),
			SCF::gets( $this->post_id )
		);
	}

	/**
	 * @group get_user_meta
	 */
	public function test_get_user_meta__繰り返し内の単一値項目() {
		add_user_meta( $this->user_id, 'text3', 1 );
		add_user_meta( $this->user_id, 'text3', 2 );
		$this->assertEquals(
			array( 1, 2 ),
			SCF::get_user_meta( $this->user_id, 'text3' )
		);
	}

	/**
	 * @group get_user_meta
	 */
	public function test_get_user_meta__繰り返し内の複数値項目() {
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
	 * @group get_user_meta
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
				'text-has-default'         => '',
				'text-has-not-default'     => '',
				'checkbox-has-default'     => array(),
				'checkbox-has-not-default' => array(),
			),
			SCF::get_user_meta( $this->user_id )
		);
	}

	/**
	 * @group get_term_meta
	 */
	public function test_get_term_meta__繰り返し内の単一値項目() {
		$Meta = new Smart_Custom_Fields_Meta( get_term( $this->term_id, 'category' ) );
		$Meta->add( 'text3', 1 );
		$Meta->add( 'text3', 2 );
		$this->assertEquals(
			array( 1, 2 ),
			SCF::get_term_meta( $this->term_id, 'category', 'text3' )
		);
	}

	/**
	 * @group get_term_meta
	 */
	public function test_get_term_meta__繰り返し内の複数値項目() {
		$Meta = new Smart_Custom_Fields_Meta( get_term( $this->term_id, 'category' ) );
		// ループ内のチェックボックス（複数値項目）は必ずこのメタデータを持つ
		$Meta->add( SCF_Config::PREFIX . 'repeat-multiple-data', array(
			'checkbox3' => array( 1, 2 ),
		) );

		$Meta->add( 'checkbox3', 1 );
		$Meta->add( 'checkbox3', 2 );
		$Meta->add( 'checkbox3', 3 );
		$this->assertEquals(
			array(
				array( 1 ),
				array( 2, 3 ),
			),
			SCF::get_term_meta( $this->term_id, 'category', 'checkbox3' )
		);
	}

	/**
	 * @group get_term_meta
	 */
	public function test_get_term_meta_all() {
		$Meta = new Smart_Custom_Fields_Meta( get_term( $this->term_id, 'category' ) );
		$Meta->update( 'text', 'hoge' );
		$Meta->add( 'checkbox', 1 );
		$Meta->add( 'checkbox', 2 );

		// ループ内のチェックボックス（複数値項目）は必ずこのメタデータを持つ
		$Meta->add( SCF_Config::PREFIX . 'repeat-multiple-data', array(
			'checkbox3' => array( 1, 2 ),
		) );

		$Meta->add( 'checkbox3', 1 );
		$Meta->add( 'checkbox3', 2 );
		$Meta->add( 'checkbox3', 3 );

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
				'text-has-default'         => '',
				'text-has-not-default'     => '',
				'checkbox-has-default'     => array(),
				'checkbox-has-not-default' => array(),
			),
			SCF::get_term_meta( $this->term_id, 'category' )
		);
	}

	/**
	 * @group get_field
	 */
	public function test_get_field__フィールドが存在しないときはnull() {
		$this->go_to( $this->post_id );
		$Field = SCF::get_field( get_post( $this->post_id ), 'not_exist' );
		$this->assertNull( $Field );
	}

	/**
	 * @group get_field
	 */
	public function test_get_field__フィールドが存在する() {
		$this->go_to( $this->post_id );
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
	 * @group get_settings_posts_cache
	 */
	public function test_get_settings_posts__設定されていないときは空配列() {
		$this->assertSame( array(), SCF::get_settings_posts( get_post( $this->post_id ) ) );
	}

	/**
	 * @group get_settings_posts_cache
	 */
	public function test_get_settings_posts_cache() {
		$post_id = $this->factory->post->create( array(
			'post_type'  => SCF_Config::NAME,
			'post_title' => 'test_settings_post',
		) );
		update_post_meta( $post_id, SCF_Config::PREFIX . 'condition', array( 'post' ) );

		$settings_posts = SCF::get_settings_posts( get_post( $this->post_id ) );
		$settings_posts_cache = SCF::get_settings_posts_cache( get_post( $this->post_id ) );
		$this->assertCount( 1, $settings_posts_cache );
		foreach ( $settings_posts_cache as $settings_post ) {
			$this->assertEquals( 'test_settings_post', $settings_post->post_title );
		}
	}

	/**
	 * @group get_settings_posts_cache
	 */
	public function test_get_settings_posts_cache__キャッシュされていないときはnull() {
		$this->assertNull( SCF::get_settings_posts_cache( get_post( $this->post_id ) ) );
	}

	/**
	 * @group get_settings
	 */
	public function test_get_settings__投稿タイプとPost_IDが一致する() {
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
	 * @group get_settings
	 */
	public function test_get_settings__投稿タイプが一致しない() {
		$post_id = $this->factory->post->create( array(
			'post_type'  => SCF_Config::NAME,
			'post_title' => 'test_settings',
		) );

		$settings = SCF::get_settings( get_post( 99999 ) );
		$this->assertSame( array(), $settings );
	}

	/**
	 * @group get_settings
	 */
	public function test_get_settings__ロールが一致する() {
		$post_id = $this->factory->post->create( array(
			'post_type'  => SCF_Config::NAME,
			'post_title' => 'test_settings',
		) );

		$settings = SCF::get_settings( get_userdata( $this->user_id ) );
		$this->assertTrue( is_a( current( $settings ), 'Smart_Custom_Fields_Setting' ) );
	}

	/**
	 * @group get_settings
	 */
	public function test_get_settings__ロールが一致しない() {
		$post_id = $this->factory->post->create( array(
			'post_type'  => SCF_Config::NAME,
			'post_title' => 'test_settings',
		) );

		$settings = SCF::get_settings( get_userdata( 99999 ) );
		$this->assertSame( array(), $settings );
	}

	/**
	 * @group get_settings
	 */
	public function test_get_settings__タームが一致する() {
		$post_id = $this->factory->post->create( array(
			'post_type'  => SCF_Config::NAME,
			'post_title' => 'test_settings',
		) );

		$settings = SCF::get_settings( get_term( $this->term_id, 'category' ) );
		$this->assertTrue( is_a( current( $settings ), 'Smart_Custom_Fields_Setting' ) );
	}

	/**
	 * @group get_settings
	 */
	public function test_get_settings__タームが一致しない() {
		$post_id = $this->factory->post->create( array(
			'post_type'  => SCF_Config::NAME,
			'post_title' => 'test_settings',
		) );

		$settings = SCF::get_settings( get_term( 99999, 'category' ) );
		$this->assertSame( array(), $settings );
	}

	/**
	 * @group get_settings_cache
	 */
	public function test_get_settings_cache__投稿タイプが一致する() {
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
	 * @group get_settings_cache
	 */
	public function test_get_settings_cache__投稿タイプが一致しない() {
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
	 * @group get_settings_cache
	 */
	public function test_get_settings_cache__投稿タイプとPost_IDが一致する() {
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
	 * @group get_settings_cache
	 */
	public function test_get_settings_cache__投稿タイプは一致するがPost_IDは一致しない() {
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
	 * @group get_settings_cache
	 */
	public function test_get_settings_cache__ロールが一致する() {
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
	 * @group get_settings_cache
	 */
	public function test_get_settings_cache__ロールが一致しない() {
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
	 * @group get_settings_cache
	 */
	public function test_get_settings_cache__タームが一致する() {
		$post_id = $this->factory->post->create( array(
			'post_type'  => SCF_Config::NAME,
			'post_title' => 'test_settings',
		) );
		update_post_meta( $post_id, SCF_Config::PREFIX . 'taxonomies', array( 'category' ) );

		// キャッシュに保存
		$settings = SCF::get_settings( get_term( $this->term_id, 'category' ) );

		$Setting = SCF::get_settings_cache( $post_id, get_term( $this->term_id, 'category' ) );
		$this->assertTrue( is_a( $Setting, 'Smart_Custom_Fields_Setting' ) );
	}

	/**
	 * @group get_settings_cache
	 */
	public function test_get_settings_cache__タームが一致しない() {
		$post_id = $this->factory->post->create( array(
			'post_type'  => SCF_Config::NAME,
			'post_title' => 'test_settings',
		) );
		update_post_meta( $post_id, SCF_Config::PREFIX . 'taxonomies', array( 'post_tag' ) );

		// キャッシュに保存
		$settings = SCF::get_settings( get_term( $this->term_id, 'category' ) );

		$Setting = SCF::get_settings_cache( $post_id, get_term( $this->term_id, 'category' ) );
		$this->assertNull( $Setting );
	}

	/**
	 * @group get_repeat_multiple_data
	 */
	public function test_get_repeat_multiple_data__存在しないときは空配列() {
		$this->assertSame( array(), SCF::get_repeat_multiple_data( get_post( $this->post_id ) ) );
	}

	/**
	 * @group get_repeat_multiple_data
	 */
	public function test_get_repeat_multiple_data() {
		update_post_meta( $this->post_id, SCF_Config::PREFIX . 'repeat-multiple-data', array(
			'checkbox3' => array( 1, 2 ),
		) );
		$this->assertSame(
			array(
				'checkbox3' => array( 1, 2 ),
			),
			SCF::get_repeat_multiple_data( get_post( $this->post_id ) )
		);
	}

	/**
	 * @group get_groups
	 */
	public function test_get_groups__Groupが返ってくるか() {
		$settings = SCF::get_settings( get_post( $this->post_id ) );
		foreach ( $settings as $Setting ) {
			foreach ( $Setting->get_groups() as $Group ) {
				// グループ名が数字の場合は null が返る
				$this->assertTrue(
					in_array(
						$Group->get_name(),
						array( null, null, 'group-name-3', 'group-name-4' ),
						true
					)
				);
			}
		}
	}

	/**
	 * @group get_default_value
	 */
	public function test_get_default_value__指定されたFieldのデフォルト値なし_singleがtrueのときは空文字列() {
		$Field = SCF::get_field( get_post( $this->post_id ), 'text-has-not-default' );
		$this->assertSame(
			'',
			SCF::get_default_value( $Field, true )
		);
	}

	/**
	 * @group get_default_value
	 */
	public function test_get_default_value__指定されたFieldのデフォルト値なし_singleがfalseのときは空配列() {
		$Field = SCF::get_field( get_post( $this->post_id ), 'text-has-not-default' );
		$this->assertSame(
			array(),
			SCF::get_default_value( $Field )
		);
	}

	/**
	 * @group get_default_value
	 */
	public function test_get_default_value__指定されたFieldのデフォルト値あり_singleがtrueのときは文字列() {
		$Field = SCF::get_field( get_post( $this->post_id ), 'text-has-default' );
		$this->assertSame(
			'text default',
			SCF::get_default_value( $Field, true )
		);
	}

	/**
	 * @group get_default_value
	 */
	public function test_get_default_value__指定されたFieldのデフォルト値あり_singleがfalseのときは配列() {
		$Field = SCF::get_field( get_post( $this->post_id ), 'text-has-default' );
		$this->assertSame(
			array(
				'text default',
			),
			SCF::get_default_value( $Field )
		);
	}

	/**
	 * @group get_default_value
	 */
	public function test_get_default_value__指定されたFieldのデフォルト値あり_複数値項目_singleがtrueのときは配列() {
		$Field = SCF::get_field( get_post( $this->post_id ), 'checkbox-has-default' );
		$this->assertSame(
			array(
				'A', 'B',
			),
			SCF::get_default_value( $Field, true )
		);
	}

	/**
	 * @group get_default_value
	 */
	public function test_get_default_value__指定されたFieldのデフォルト値あり_複数値項目_singleがfalseのときは配列() {
		$Field = SCF::get_field( get_post( $this->post_id ), 'checkbox-has-default' );
		$this->assertSame(
			array(
				'A', 'B',
			),
			SCF::get_default_value( $Field )
		);
	}

	/**
	 * @group get_default_value
	 */
	public function test_get_default_value__指定されたFieldのデフォルト値なし_複数値項目_singleがtrueのときは配列() {
		$Field = SCF::get_field( get_post( $this->post_id ), 'checkbox-has-not-default' );
		$this->assertSame(
			array(),
			SCF::get_default_value( $Field, true )
		);
	}

	/**
	 * @group get_default_value
	 */
	public function test_get_default_value__指定されたFieldのデフォルト値なし_複数値項目_singleがfalseのときは配列() {
		$Field = SCF::get_field( get_post( $this->post_id ), 'checkbox-has-not-default' );
		$this->assertSame(
			array(),
			SCF::get_default_value( $Field )
		);
	}
	
	/**
	 * @group get_post_meta
	 */
	public function test_get_post_meta__SCF以外のメタデータを取得できるか() {
		update_post_meta( $this->post_id, '_get_post_meta', 'value' );
		$this->assertSame(
			'value',
			get_post_meta( $this->post_id, '_get_post_meta', true )
		);
		
		update_post_meta( $this->new_post_id, '_get_post_meta', 'value' );
		$this->assertSame(
			'value',
			get_post_meta( $this->new_post_id, '_get_post_meta', true )
		);
		
		update_post_meta( $this->draft_post_id, '_get_post_meta', 'value' );
		$this->assertSame(
			'value',
			get_post_meta( $this->draft_post_id, '_get_post_meta', true )
		);
	}
	
	/**
	 * @group get_post_meta
	 */
	public function test_get_post_meta__プレビュー時にSCF以外のメタデータを取得できるか() {
		global $wp_query, $post;
		
		update_post_meta( $this->post_id, '_get_post_meta', 'value' );
		$this->create_revision( $this->post_id );
		
		update_post_meta( $this->new_post_id, '_get_post_meta', 'value' );
		$this->create_revision( $this->new_post_id );
		
		update_post_meta( $this->draft_post_id, '_get_post_meta', 'value' );
		$this->create_revision( $this->draft_post_id );
		
		// プレビュー状態に設定
		$backup_wp_query = clone $wp_query;
		$wp_query->is_preview = true;
		
		$post = get_post( $this->post_id );
		setup_postdata( $post );
		$this->assertSame(
			'value',
			get_post_meta( $this->post_id, '_get_post_meta', true )
		);
		wp_reset_postdata();
		
		$post = get_post( $this->new_post_id );
		setup_postdata( $post );
		$this->assertSame(
			'value',
			get_post_meta( $this->new_post_id, '_get_post_meta', true )
		);
		wp_reset_postdata();
		
		$post = get_post( $this->draft_post_id );
		setup_postdata( $post );
		$this->assertSame(
			'value',
			get_post_meta( $this->draft_post_id, '_get_post_meta', true )
		);
		wp_reset_postdata();
		
		// プレビュー状態を解除
		$wp_query = $backup_wp_query;
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
	 * フック経由でカスタムフィールドを設定
	 *
	 * @param array $settings 管理画面で設定された Smart_Custom_Fields_Setting の配列
	 * @param string $type 投稿タイプ or ロール or タクソノミー
	 * @param int $id 投稿ID or ユーザーID or タームID
	 * @param string $meta_type メタデータのタイプ。post or user or term
	 * @return array
	 */
	public function _register( $settings, $type, $id, $meta_type ) {
		// SCF::add_setting( 'ユニークなID', 'メタボックスのタイトル' );
		if (
			( $type === 'post' && $id === $this->post_id ) ||
			( $type === 'post' && $id === $this->new_post_id ) ||
			( $type === 'editor' ) ||
			( $type === 'category' )
		) {
			$Setting = SCF::add_setting( 'id-1', 'Register Test' );
			// $Setting->add_group( 'ユニークなID', 繰り返し可能か, カスタムフィールドの配列 );
			$Setting->add_group( 0, false, array(
				array(
					'name'  => 'text',
					'label' => 'text field',
					'type'  => 'text',
				),
			) );
			$Setting->add_group( 1, false, array(
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
			$Setting->add_group( 'group-name-4', false, array(
				array(
					'name'    => 'text-has-default',
					'label'   => 'text has default',
					'type'    => 'text',
					'default' => 'text default',
				),
				array(
					'name'    => 'text-has-not-default',
					'label'   => 'text has not default',
					'type'    => 'text',
				),
				array(
					'name'    => 'checkbox-has-default',
					'label'   => 'checkbox has default',
					'type'    => 'check',
					'choices' => array( 'A', 'B', 'C' ),
					'default' => "A\nB\nX",
				),
				array(
					'name'    => 'checkbox-has-not-default',
					'label'   => 'checkbox has not default',
					'type'    => 'check',
					'choices' => array( 'A', 'B', 'C' ),
				),
			) );
			$settings['id-1'] = $Setting;
		}
		return $settings;
	}
}
