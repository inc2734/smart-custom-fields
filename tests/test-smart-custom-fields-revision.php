<?php
class Smart_Custom_Fields_Revision_Test extends WP_UnitTestCase {

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
		// コードでカスタムフィールドを定義
		add_filter( 'smart-cf-register-fields', array( $this, '_register' ), 10, 4 );

		SCF::clear_all_cache();
	}

	/**
	 * tearDown
	 */
	public function tearDown() {
		parent::tearDown();
		SCF::clear_all_cache();
	}

	/**
	 * @group wp_restore_post_revision
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
					'loop-check-1'
				),
				array(
					'loop-check-2', 'loop-check-3',
				),
			), SCF::get( 'checkbox3', $this->post_id )
		);
	}

	/**
	 * @group wp_insert_post
	 */
	public function test_wp_insert_post_Post_IDがrevisionのときはnull() {
		$_POST[SCF_Config::NAME] = array(
			'text' => 'text',
		);
		$Revision = new Smart_Custom_Fields_Revisions();
		$this->assertNull( $Revision->wp_insert_post( $this->post_id ) );
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
			$settings['id-1'] = $Setting;
		}
		return $settings;
	}
}
