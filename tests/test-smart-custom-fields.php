<?php
class SmartCustomFieldsTest extends WP_UnitTestCase {

	protected $post_id;

	public function setUp() {
		parent::setUp();
		$this->post_id = $this->factory->post->create();
		add_filter( 'smart-cf-register-fields', array( $this, '_register' ), 10, 3 );
	}

	public function test_when_not_saved_metadata() {
		// 通常、投稿を表示したときは post_id が自動的に取得されるけど、ここでは取得できないので null
		$this->assertNull( SCF::get( 'text' ) );
		$this->assertNull( SCF::get( 'checkbox' ) );
		$this->assertEquals( array(), SCF::gets() );
		// 通常はこっち
		$this->assertEquals( '', SCF::get( 'text', $this->post_id ) );
		$this->assertEquals( array(), SCF::get( 'checkbox', $this->post_id ) );
		$this->assertEquals( array( 'text' => '', 'checkbox' => array() ), SCF::gets( $this->post_id ) );
		// 存在しないものは null
		$this->assertNull( SCF::get( 'not_exist', $this->post_id ) );
	}

	public function test_when_saved_norepeat_text() {
		update_post_meta( $this->post_id, 'text', 'hoge' );
		$this->assertEquals( 'hoge', SCF::get( 'text', $this->post_id ) );
	}

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

	public function test_gets_when_not_saved() {
		$this->assertEquals(
			array(
				'text'     => '',
				'checkbox' => array(),
			),
			SCF::gets( $this->post_id )
		);
	}

	public function test_gets_when_saved() {
		update_post_meta( $this->post_id, 'text', 'hoge' );
		add_post_meta( $this->post_id, 'checkbox', 1 );
		add_post_meta( $this->post_id, 'checkbox', 2 );
		add_post_meta( $this->post_id, 'checkbox', 3 );
		add_post_meta( $this->post_id, 'checkbox', 4 );
		$this->assertEquals(
			array(
				'text'     => 'hoge',
				'checkbox' => array(
					1, 2, 3, 4
				),
			),
			SCF::gets( $this->post_id )
		);
	}

	public function test_get_field_when_exist() {
		$Field = SCF::get_field( 'post', 'text' );
		$this->assertEquals( 'text', $Field->get( 'name' ) );
	}

	public function test_get_field_when_not_exist() {
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

		$this->assertEquals( array(), SCF::get_settings_posts_cache( 'post' ) );
		$settings_posts = SCF::get_settings_posts( 'post' );
		$this->assertEquals( 'test_settings_post', $settings_posts[0]->post_title );
		$settings_posts_cache = SCF::get_settings_posts_cache( 'post' );
		$this->assertEquals( 'test_settings_post', $settings_posts_cache[0]->post_title );
	}

	/**
	 * @backupStaticAttributes enabled
	 */
	public function test_get_settings() {
		$post_id = $this->factory->post->create( array(
			'post_type'  => SCF_Config::NAME,
			'post_title' => 'test_settings',
		) );
		update_post_meta( $post_id, SCF_Config::PREFIX . 'condition', array( 'post' ) );

		$this->assertEquals( array(), SCF::get_settings_cache( 'post', false ) );
		$this->assertEquals( array(), SCF::get_settings_cache( 'post', $this->post_id ) );
		$settings = SCF::get_settings( 'post', $this->post_id );
		foreach ( $settings as $Setting ) {
			$this->assertTrue( in_array( $Setting->get_title(), array( 'test_settings', 'Register Test' ) ) );
		}
		$this->assertEquals( array(), SCF::get_settings_cache( 'post', false ) );
		$settings_cache = SCF::get_settings_cache( 'post', $this->post_id );
		foreach ( $settings_cache as $Setting ) {
			$this->assertTrue( in_array( $Setting->get_title(), array( 'test_settings', 'Register Test' ) ) );
		}
	}

	public function _register( $settings, $post_type, $post_id ) {
		// SCF::add_setting( 'ユニークなID', 'メタボックスのタイトル' );
		$Setting = SCF::add_setting( 'id-1', 'Register Test' );
		// $Setting->add_group( 'ユニークなID', 繰り返し可能か, カスタムフィールドの配列 );
		$Setting->add_group( 'group-name-1', false, array(
			array(
				'name'  => 'text',
				'label' => 'text field',
				'type'  => 'text',
			),
			array(
				'name'    => 'checkbox',
				'label'   => 'checkbox field',
				'type'    => 'check',
				'choices' => array( 1, 2, 3 ),
			),
		) );
		$settings[] = $Setting;
		return $settings;
	}
}

