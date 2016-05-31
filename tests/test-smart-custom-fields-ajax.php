<?php
class Smart_Custom_Fields_Ajax_Test extends WP_UnitTestCase {

	/**
	 * @var Smart_Custom_Fields_Ajax
	 */
	protected $Ajax;

	/**
	 * setUp
	 */
	public function setUp() {
		parent::setUp();
		$this->Ajax = new Smart_Custom_Fields_Ajax();

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
	 * @group delete_term
	 */
	public function test_delete_term() {
		$taxonomy = 'category';
		$term_id  = $this->factory->term->create( array( 'taxonomy' => $taxonomy ) );
		$term     = get_term( $term_id, 'category' );
		$Meta = new Smart_Custom_Fields_Meta( $term );

		if ( !_get_meta_table( $Meta->get_meta_type() ) ) {
			$Meta->add( 'text', 'text' );
			$this->Ajax->delete_term( $term_id, '', $taxonomy, $term );
			$this->assertSame( array(), $Meta->get( 'text' ) );
		}
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
		if ( type === 'category' ) {
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
