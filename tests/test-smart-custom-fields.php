<?php
class SmartCustomFieldsTest extends WP_UnitTestCase {

	public function setUp() {
		parent::setUp();
		add_filter( 'smart-cf-register-fields', array( $this, '_register' ) );
	}

	public function test_when_not_saved_metadata() {
		$post_id = $this->factory->post->create();
		$this->assertFalse( SCF::get( 'text' ), $post_id );
		$this->assertFalse( SCF::get( 'checkbox' ), $post_id );
	}

	public function test_when_saved_norepeat_text() {
		$post_id = $this->factory->post->create();
		update_post_meta( $post_id, 'text', 'hoge' );
		$this->assertEquals( 'hoge', SCF::get( 'text', $post_id ) );
	}

	public function test_when_saved_norepeat_checkbox() {
		$post_id = $this->factory->post->create();
		add_post_meta( $post_id, 'checkbox', 1 );
		add_post_meta( $post_id, 'checkbox', 2 );
		add_post_meta( $post_id, 'checkbox', 3 );
		add_post_meta( $post_id, 'checkbox', 4 );
		$this->assertEquals(
			array( 1, 2, 3, 4 ),
			SCF::get( 'checkbox', $post_id )
		);
	}

	public function test_gets_when_not_saved() {
		$post_id = $this->factory->post->create();
		$this->assertEquals(
			array(
				'text'     => '',
				'checkbox' => array(),
			),
			SCF::gets( $post_id )
		);
	}

	public function test_gets_when_saved() {
		$post_id = $this->factory->post->create();
		update_post_meta( $post_id, 'text', 'hoge' );
		add_post_meta( $post_id, 'checkbox', 1 );
		add_post_meta( $post_id, 'checkbox', 2 );
		add_post_meta( $post_id, 'checkbox', 3 );
		add_post_meta( $post_id, 'checkbox', 4 );
		$this->assertEquals(
			array(
				'text'     => 'hoge',
				'checkbox' => array(
					1, 2, 3, 4
				),
			),
			SCF::gets( $post_id )
		);
	}

	public function _register( $settings ) {
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

