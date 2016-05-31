<?php
class Smart_Custom_Fields_Cache_Test extends WP_UnitTestCase {

	/**
	 * @var int
	 */
	protected $post_id;

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
	 * @group get_settings_posts
	 */
	public function test_get_settings_posts() {
		$Cache = Smart_Custom_Fields_Cache::getInstance();
		$post_id = $this->factory->post->create( array(
			'post_type'  => SCF_Config::NAME,
			'post_title' => 'test_settings_post',
		) );
		update_post_meta( $post_id, SCF_Config::PREFIX . 'condition', array( 'post' ) );

		$settings_posts = SCF::get_settings_posts( get_post( $this->post_id ) );
		$settings_posts_cache = $Cache->get_settings_posts( get_post( $this->post_id ) );
		$this->assertCount( 1, $settings_posts_cache );
		foreach ( $settings_posts_cache as $settings_post ) {
			$this->assertEquals( 'test_settings_post', $settings_post->post_title );
		}
	}

	/**
	 * @group get_settings_posts
	 */
	public function test_get_settings_posts__キャッシュされていないときはnull() {
		$Cache = Smart_Custom_Fields_Cache::getInstance();
		$this->assertNull( $Cache->get_settings_posts( get_post( $this->post_id ) ) );
	}

	/**
	 * @group get_settings
	 */
	public function test_get_settings__投稿タイプが一致する() {
		$post_id = $this->factory->post->create( array(
			'post_type'  => SCF_Config::NAME,
			'post_title' => 'test_settings',
		) );
		update_post_meta( $post_id, SCF_Config::PREFIX . 'condition', array( 'post' ) );

		// キャッシュに保存
		$settings = SCF::get_settings( get_post( $this->post_id ) );

		$Cache = Smart_Custom_Fields_Cache::getInstance();
		$Setting = $Cache->get_settings( $post_id );
		$this->assertTrue( is_a( $Setting, 'Smart_Custom_Fields_Setting' ) );
	}

	/**
	 * @group get_settings
	 */
	public function test_get_settings__投稿タイプが一致しない() {
		$post_id = $this->factory->post->create( array(
			'post_type'  => SCF_Config::NAME,
			'post_title' => 'test_settings',
		) );
		update_post_meta( $post_id, SCF_Config::PREFIX . 'condition', array( 'page' ) );

		// キャッシュに保存
		$settings = SCF::get_settings( get_post( $this->post_id ) );

		$Cache = Smart_Custom_Fields_Cache::getInstance();
		$Setting = $Cache->get_settings( $post_id );
		$this->assertNull( $Setting );
	}

	/**
	 * @group get_settings
	 */
	public function test_get_settings__投稿タイプとPost_IDが一致する() {
		$post_id = $this->factory->post->create( array(
			'post_type'  => SCF_Config::NAME,
			'post_title' => 'test_settings',
		) );
		update_post_meta( $post_id, SCF_Config::PREFIX . 'condition', array( 'post' ) );
		update_post_meta( $post_id, SCF_Config::PREFIX . 'condition-post-ids', $this->post_id );

		// キャッシュに保存
		$settings = SCF::get_settings( get_post( $this->post_id ) );

		$Cache = Smart_Custom_Fields_Cache::getInstance();
		$Setting = $Cache->get_settings( $post_id, get_post( $this->post_id ) );
		$this->assertTrue( is_a( $Setting, 'Smart_Custom_Fields_Setting' ) );
	}

	/**
	 * @group get_settings
	 */
	public function test_get_settings__投稿タイプは一致するがPost_IDは一致しない() {
		$post_id = $this->factory->post->create( array(
			'post_type'  => SCF_Config::NAME,
			'post_title' => 'test_settings',
		) );
		update_post_meta( $post_id, SCF_Config::PREFIX . 'condition', array( 'post' ) );
		update_post_meta( $post_id, SCF_Config::PREFIX . 'condition-post-ids', '99999' );

		// キャッシュに保存
		$settings = SCF::get_settings( get_post( $this->post_id ) );

		$Cache = Smart_Custom_Fields_Cache::getInstance();
		$Setting = $Cache->get_settings( $post_id, get_post( $this->post_id ) );
		$this->assertFalse( $Setting );
	}

	/**
	 * @group get_settings
	 */
	public function test_get_settings__ロールが一致する() {
		$post_id = $this->factory->post->create( array(
			'post_type'  => SCF_Config::NAME,
			'post_title' => 'test_settings',
		) );
		update_post_meta( $post_id, SCF_Config::PREFIX . 'roles', array( 'editor' ) );

		// キャッシュに保存
		$settings = SCF::get_settings( get_userdata( $this->user_id ) );

		$Cache = Smart_Custom_Fields_Cache::getInstance();
		$Setting = $Cache->get_settings( $post_id, get_userdata( $this->user_id ) );
		$this->assertTrue( is_a( $Setting, 'Smart_Custom_Fields_Setting' ) );
	}

	/**
	 * @group get_settings
	 */
	public function test_get_settings__ロールが一致しない() {
		$post_id = $this->factory->post->create( array(
			'post_type'  => SCF_Config::NAME,
			'post_title' => 'test_settings',
		) );
		update_post_meta( $post_id, SCF_Config::PREFIX . 'roles', array( 'administrator' ) );

		// キャッシュに保存
		$settings = SCF::get_settings( get_userdata( $this->user_id ) );

		$Cache = Smart_Custom_Fields_Cache::getInstance();
		$Setting = $Cache->get_settings( $post_id, get_userdata( $this->user_id ) );
		$this->assertNull( $Setting );
	}

	/**
	 * @group get_settings
	 */
	public function test_get_settings__タームが一致する() {
		$post_id = $this->factory->post->create( array(
			'post_type'  => SCF_Config::NAME,
			'post_title' => 'test_settings',
		) );
		update_post_meta( $post_id, SCF_Config::PREFIX . 'taxonomies', array( 'category' ) );

		// キャッシュに保存
		$settings = SCF::get_settings( get_term( $this->term_id, 'category' ) );

		$Cache = Smart_Custom_Fields_Cache::getInstance();
		$Setting = $Cache->get_settings( $post_id, get_term( $this->term_id, 'category' ) );
		$this->assertTrue( is_a( $Setting, 'Smart_Custom_Fields_Setting' ) );
	}

	/**
	 * @group get_settings
	 */
	public function test_get_settings__タームが一致しない() {
		$post_id = $this->factory->post->create( array(
			'post_type'  => SCF_Config::NAME,
			'post_title' => 'test_settings',
		) );
		update_post_meta( $post_id, SCF_Config::PREFIX . 'taxonomies', array( 'post_tag' ) );

		// キャッシュに保存
		$settings = SCF::get_settings( get_term( $this->term_id, 'category' ) );

		$Cache = Smart_Custom_Fields_Cache::getInstance();
		$Setting = $Cache->get_settings( $post_id, get_term( $this->term_id, 'category' ) );
		$this->assertNull( $Setting );
	}

	/**
	 * フック経由でカスタムフィールドを設定
	 *
	 * @param array $settings 管理画面で設定された Smart_Custom_Fields_Setting の配列
	 * @param string $type 投稿タイプ or ロール or タクソノミー
	 * @param int $id 投稿ID or ユーザーID or タームID
	 * @param string $meta_type メタデータのタイプ。post or user or term or option
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
