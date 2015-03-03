<?php
class SmartCustomFieldsTest extends WP_UnitTestCase {

	protected $post_id;

	public function setUp() {
		parent::setUp();
		$this->post_id = $this->factory->post->create();
		add_filter( 'smart-cf-register-fields', array( $this, '_register' ) );
	}

	public function test_when_not_saved_metadata() {
		$this->assertFalse( SCF::get( 'text' ), $this->post_id );
		$this->assertFalse( SCF::get( 'checkbox' ), $this->post_id );
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

