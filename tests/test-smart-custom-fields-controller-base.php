<?php
class Smart_Custom_Fields_Controller_Base_Test extends WP_UnitTestCase {

	/**
	 * @var int
	 */
	protected $new_post_id;

	/**
	 * @var int
	 */
	protected $post_id;

	/**
	 * setUp
	 */
	public function setUp() {
		parent::setUp();
		// カスタムフィールドを設定するための投稿（未保存）
		$this->new_post_id = $this->factory->post->create( array(
			'post_type'   => 'post',
			'post_status' => 'auto-draft',
		) );

		// カスタムフィールドを設定するための投稿
		$this->post_id = $this->factory->post->create( array(
			'post_type'   => 'post',
			'post_status' => 'publish',
		) );
		// コードでカスタムフィールドを定義
		add_filter( 'smart-cf-register-fields', array( $this, '_register' ), 10, 4 );

		require_once plugin_dir_path( __FILE__ ) . '../classes/controller/class.controller-base.php';
		$this->Controller = new Smart_Custom_Fields_Controller_Base();

		$Cache = Smart_Custom_Fields_Cache::getInstance();
		$Cache->clear_all_cache();
	}

	/**
	 * tearDown
	 */
	public function tearDown() {
		parent::tearDown();
		$Cache = Smart_Custom_Fields_Cache::getInstance();
		$Cache->clear_all_cache();
	}

	/**
	 * @group get_multiple_data_field_value
	 */
	public function test_get_multiple_data_field_value__indexがnull_デフォルト値ありの場合はデフォルト値を返す() {
		$object = get_post( $this->post_id );
		$Field  = SCF::get_field( $object, 'checkbox-has-default' );
		$index  = null;
		$this->assertEquals(
			array( 'A', 'B', ),
			$this->Controller->get_multiple_data_field_value( $object, $Field, $index )
		);
	}


	/**
	 * @group get_multiple_data_field_value
	 */
	public function test_get_multiple_data_field_value__indexがnull_デフォルト値なしの場合は空配列を返す() {
		$object = get_post( $this->post_id );
		$Field  = SCF::get_field( $object, 'checkbox-has-not-default' );
		$index  = null;
		$this->assertEquals(
			array(),
			$this->Controller->get_multiple_data_field_value( $object, $Field, $index )
		);
	}

	/**
	 * @group get_multiple_data_field_value
	 */
	public function test_get_multiple_data_field_value__投稿未保存_デフォルト値ありの場合はデフォルト値を返す() {
		$object = get_post( $this->new_post_id );
		$Field  = SCF::get_field( $object, 'checkbox-has-default' );
		$index  = 0;
		$this->assertEquals(
			array( 'A', 'B', ),
			$this->Controller->get_multiple_data_field_value( $object, $Field, $index )
		);
	}

	/**
	 * @group get_multiple_data_field_value
	 */
	public function test_get_multiple_data_field_value__投稿保存済_メタデータ未保存の場合はデフォルト値を返す() {
		$object = get_post( $this->post_id );
		$Field  = SCF::get_field( $object, 'checkbox-has-default' );
		$index  = 0;
		$this->assertEquals(
			array( 'A', 'B', ),
			$this->Controller->get_multiple_data_field_value( $object, $Field, $index )
		);
	}

	/**
	 * @group get_multiple_data_field_value
	 */
	public function test_get_multiple_data_field_value__未保存_デフォルト値なしの場合は空配列を返す() {
		$object = get_post( $this->post_id );
		$Field  = SCF::get_field( $object, 'checkbox-has-not-default' );
		$index  = 0;
		$this->assertEquals(
			array(),
			$this->Controller->get_multiple_data_field_value( $object, $Field, $index )
		);
	}

	/**
	 * @group get_multiple_data_field_value
	 */
	public function test_get_multiple_data_field_value__保存済の場合は配列を返す() {
		$object = get_post( $this->post_id );
		$Field  = SCF::get_field( $object, 'checkbox-has-default' );
		$index  = 0;

		$Meta = new Smart_Custom_Fields_Meta( $object );
		$Meta->add( 'checkbox-has-default', 'A' );

		$this->assertEquals(
			array( 'A', ),
			$this->Controller->get_multiple_data_field_value( $object, $Field, $index )
		);
	}

	/**
	 * @group get_multiple_data_field_value
	 */
	public function test_get_multiple_data_field_value__空値を保存済みの場合は空配列を返す() {
		$object = get_post( $this->post_id );
		$Meta   = new Smart_Custom_Fields_Meta( $object );
		$POST   = array(
			SCF_Config::NAME => array(
				'checkbox3' => array(
					array(),
					array( 1, 2 ),
					array( 2, 3 ),
				),
			),
		);
		$Meta->save( $POST );

		$Field = SCF::get_field( $object, 'checkbox3' );
		$index = 0; // 空配列が返るべきキー

		$this->assertSame(
			array(),
			$this->Controller->get_multiple_data_field_value( $object, $Field, $index )
		);
	}

	/**
	 * @group get_single_data_field_value
	 */
	public function test_get_single_data_field_value__indexがnull_デフォルト値ありの場合はデフォルト値を返す() {
		$object = get_post( $this->post_id );
		$Field  = SCF::get_field( $object, 'text-has-default' );
		$index  = null;
		$this->assertEquals(
			'text default',
			$this->Controller->get_single_data_field_value( $object, $Field, $index )
		);
	}

	/**
	 * @group get_single_data_field_value
	 */
	public function test_get_single_data_field_value__indexがnull_デフォルト値なしの場合は空文字列を返す() {
		$object = get_post( $this->post_id );
		$Field  = SCF::get_field( $object, 'text-has-not-default' );
		$index  = null;
		$this->assertEquals(
			'',
			$this->Controller->get_single_data_field_value( $object, $Field, $index )
		);
	}

	/**
	 * @group get_single_data_field_value
	 */
	public function test_get_single_data_field_value__投稿未保存_デフォルト値ありの場合はデフォルト値を返す() {
		$object = get_post( $this->new_post_id );
		$Field  = SCF::get_field( $object, 'text-has-default' );
		$index  = 0;
		$this->assertEquals(
			'text default',
			$this->Controller->get_single_data_field_value( $object, $Field, $index )
		);
	}

	/**
	 * @group get_single_data_field_value
	 */
	public function test_get_single_data_field_value__投稿保存済み_メタデータ未保存の場合はデフォルト値を返す() {
		$object = get_post( $this->post_id );
		$Field  = SCF::get_field( $object, 'text-has-default' );
		$index  = 0;
		$this->assertEquals(
			'text default',
			$this->Controller->get_single_data_field_value( $object, $Field, $index )
		);
	}

	/**
	 * @group get_single_data_field_value
	 */
	public function test_get_single_data_field_value__未保存_デフォルト値なしの場合は空文字列を返す() {
		$object = get_post( $this->post_id );
		$Field  = SCF::get_field( $object, 'text-has-not-default' );
		$index  = 0;
		$this->assertEquals(
			'',
			$this->Controller->get_single_data_field_value( $object, $Field, $index )
		);
	}

	/**
	 * @group get_single_data_field_value
	 */
	public function test_get_single_data_field_value__保存済の場合は文字列を返す() {
		$object = get_post( $this->post_id );
		$Field  = SCF::get_field( $object, 'text-has-default' );
		$index  = 0;

		$Meta = new Smart_Custom_Fields_Meta( $object );
		$Meta->add( 'text-has-default', 'A' );

		$this->assertEquals(
			'A',
			$this->Controller->get_single_data_field_value( $object, $Field, $index )
		);
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

			$Setting = SCF::add_setting( 'id-2', 'Register Test 2' );
			$Setting->add_group( 0, false, array(
				array(
					'name'  => 'text2-1',
					'label' => 'text field 2-1',
					'type'  => 'text',
				),
			) );
			$settings['id-2'] = $Setting;
		}
		return $settings;
	}
}
