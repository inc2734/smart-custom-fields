<?php
class Smart_Custom_Fields_Meta_Test extends WP_UnitTestCase {

	/**
	 * @var int
	 */
	protected $new_post_id;

	/**
	 * @var int
	 */
	protected $post_id;

	/**
	 * @var int
	 */
	protected $revision_id;

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
		// カスタムフィールドを設定するための投稿（未保存）
		$this->new_post_id = $this->factory->post->create( array(
			'post_type'   => 'post',
			'post_status' => 'auto-draft',
		) );
		$this->Meta_new_post = new Smart_Custom_Fields_Meta( get_post( $this->new_post_id ) );
		
		// カスタムフィールドを設定するための投稿
		$this->post_id = $this->factory->post->create( array(
			'post_type'   => 'post',
			'post_status' => 'publish',
		) );
		$this->Meta_post = new Smart_Custom_Fields_Meta( get_post( $this->post_id ) );

		// リビジョン用として投稿を準備
		$this->revision_id = $this->factory->post->create( array(
			'post_type'   => 'revision',
			'post_parent' => $this->post_id,
		) );
		$this->Meta_revision = new Smart_Custom_Fields_Meta( get_post( $this->revision_id ) );

		// カスタムフィールドを設定するためのユーザー
		$this->user_id = $this->factory->user->create( array( 'role' => 'editor' ) );
		$this->Meta_user = new Smart_Custom_Fields_Meta( get_userdata( $this->user_id ) );

		// カスタムフィールドを設定するためのターム
		$this->term_id = $this->factory->term->create( array( 'taxonomy' => 'category' ) );
		$this->Meta_term = new Smart_Custom_Fields_Meta( get_term( $this->term_id, 'category' ) );

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
	 * @group get_type
	 */
	public function test_get_type__objectが空のときはnullを返す() {
		$object = null;
		$Meta = new Smart_Custom_Fields_Meta( $object );
		$type = $Meta->get_type();
		$this->assertNull( $type );
	}

	/**
	 * @group get_type
	 */
	public function test_get_type__objectが投稿のときは投稿タイプを返す() {
		$type = $this->Meta_post->get_type();
		$this->assertEquals( get_post_type( $this->post_id ), $type );
	}

	/**
	 * @group get_type
	 */
	public function test_get_type__objectが存在しない投稿のときはnullを返す() {
		$object = get_post( 99999 );
		$Meta = new Smart_Custom_Fields_Meta( $object );
		$type = $Meta->get_type();
		$this->assertNull( $type );
	}

	/**
	 * @group get_type
	 */
	public function test_get_type__objectがリビジョンのときはrevisionを返す() {
		$type = $this->Meta_revision->get_type();
		$this->assertEquals( get_post_type( $this->revision_id ), $type );
	}

	/**
	 * @group get_type
	 */
	public function test_get_type__objectがリビジョンでaccept_revisionがfalseのときは親投稿の投稿タイプを返す() {
		$type = $this->Meta_revision->get_type( false );
		$this->assertEquals( get_post_type( wp_is_post_revision( $this->revision_id ) ), $type );
	}

	/**
	 * @group get_type
	 */
	public function test_get_type__objectがユーザーのときはロールを返す() {
		$type = $this->Meta_user->get_type();
		$this->assertEquals( 'editor', $type );
	}

	/**
	 * @group get_type
	 */
	public function test_get_type__objectが存在しないロールのときはnullを返す() {
		$object = get_userdata( 99999 );
		$Meta = new Smart_Custom_Fields_Meta( $object );
		$type = $Meta->get_type();
		$this->assertNull( $type );
	}

	/**
	 * @group get_type
	 */
	public function test_get_type__objectがタームのときはタクソノミー名を返す() {
		$type = $this->Meta_term->get_type();
		$this->assertEquals( 'category', $type );
	}

	/**
	 * @group get_type
	 */
	public function test_get_type__objectが存在しないタームのときはnullを返す() {
		$object = get_term( 99999, 'category' );
		$Meta = new Smart_Custom_Fields_Meta( $object );
		$type = $Meta->get_type();
		$this->assertNull( $type );
	}

	/**
	 * @group get
	 */
	public function test_get__値未保存_singleがtrueでないときは空配列を返す() {
		$this->assertSame( array(), $this->Meta_post->get( 'text' ) );
	}

	/**
	 * @group get
	 */
	public function test_get__値未保存_singleがtrueのときは空文字列を返す() {
		$this->assertSame( '', $this->Meta_post->get( 'text', true ) );
	}

	/**
	 * @group get
	 */
	public function test_get__存在しないキー_singleがtureでないときは空配列を返す() {
		$this->assertSame( array(), $this->Meta_post->get( 'not_exist' ) );
	}

	/**
	 * @group get
	 */
	public function test_get__存在しないキー_singleがtrueのときは空文字列を返す() {
		$this->assertSame( '', $this->Meta_post->get( 'not_exist', true ) );
	}

	/**
	 * @group get
	 */
	public function test_get__メタテーブル無し_値未保存_singleがtrueでないときは空配列を返す() {
		$this->assertSame( array(), $this->Meta_term->get( 'text' ) );
	}

	/**
	 * @group get
	 */
	public function test_get__メタテーブル無し_値未保存_singleがtrueのときは空文字列を返す() {
		$this->assertSame( '', $this->Meta_term->get( 'text', true ) );
	}

	/**
	 * @group get
	 */
	public function test_get__メタテーブル無し_存在しないキー_singleがtrueのときは空配列を返す() {
		$this->assertSame( array(), $this->Meta_term->get( 'not_exist' ) );
	}

	/**
	 * @group get
	 */
	public function test_get__メタテーブル無し_存在しないキー_singleがtrueのときは空文字列を返す() {
		$this->assertSame( '', $this->Meta_term->get( 'not_exist', true ) );
	}

	/**
	 * @group get
	 */
	public function test_get__メタテーブル無し_キー指定なし_singleがtrueでないときは空配列を返す() {
		$this->assertSame( array(), $this->Meta_term->get() );
	}

	/**
	 * @group get
	 */
	public function test_get__メタテーブル無し_キー指定なし_singleがtrueのときは空文字列を返す() {
		$this->assertSame( '', $this->Meta_term->get( '', true ) );
	}

	/**
	 * @group update
	 */
	public function test_update__prev_valueの指定がないときは更新() {
		$this->Meta_post->update( 'text', 'text' );
		$this->assertEquals( 'text', $this->Meta_post->get( 'text', true ) );
	}

	/**
	 * @group update
	 */
	public function test_update__prev_valueと値が同じときは上書き() {
		$this->Meta_post->add( 'text', 'prev_value' );
		$this->Meta_post->update( 'text', 'text2', 'prev_value' );
		$this->assertEquals( 'text2', $this->Meta_post->get( 'text', true ) );
	}

	/**
	 * @group update
	 */
	public function test_update__prev_valueと値が違うときは上書きしない() {
		$this->Meta_post->add( 'text', 'text' );
		$this->Meta_post->update( 'text', 'text2', 'prev_value' );
		$this->assertEquals( 'text', $this->Meta_post->get( 'text', true ) );
	}

	/**
	 * @group update
	 */
	public function test_update__prev_valueの指定あり_値未保存のときは更新() {
		$this->Meta_post->update( 'text', 'text2', 'prev_value' );
		$this->assertEquals( 'text2', $this->Meta_post->get( 'text', true ) );
	}

	/**
	 * @group update
	 */
	public function test_update__メタテーブル無し_prev_valueの指定がないときは更新() {
		$this->Meta_term->update( 'text', 'text' );
		$this->assertEquals( 'text', $this->Meta_term->get( 'text', true ) );
	}

	/**
	 * @group update
	 */
	public function test_update__メタテーブル無し_prev_valueと値が同じときは上書き() {
		$this->Meta_term->add( 'text', 'prev_value' );
		$this->Meta_term->update( 'text', 'text2', 'prev_value' );
		$this->assertEquals( 'text2', $this->Meta_term->get( 'text', true ) );
	}

	/**
	 * @group update
	 */
	public function test_update__メタテーブル無し_prev_valueと値が違うときは上書きしない() {
		$this->Meta_term->add( 'text', 'text' );
		$this->Meta_term->update( 'text', 'text2', 'prev_value' );
		$this->assertEquals( 'text', $this->Meta_term->get( 'text', true ) );
	}

	/**
	 * @group update
	 */
	public function test_update_メタテーブル無し_prev_valueの指定あり_値未保存のときは更新() {
		$this->Meta_term->update( 'text', 'text2', 'prev_value' );
		$this->assertEquals( 'text2', $this->Meta_term->get( 'text', true ) );
	}

	/**
	 * @group add
	 */
	public function test_add__uniqueの指定がないときは追加() {
		$this->Meta_post->add( 'text', 'text' );
		$this->assertEquals( 'text', $this->Meta_post->get( 'text', true ) );
	}

	/**
	 * @group add
	 */
	public function test_add__uniqueの指定あり_値未保存のときは追加() {
		$this->Meta_post->add( 'text', 'text', true );
		$this->assertEquals( array( 'text' ), $this->Meta_post->get( 'text' ) );
	}

	/**
	 * @group add
	 */
	public function test_add__unique_の指定あり_値があるときは追加しない() {
		$this->Meta_post->add( 'text', 'text' );
		$this->Meta_post->add( 'text', 'text2', true );
		$this->assertEquals( array( 'text' ), $this->Meta_post->get( 'text' ) );
	}

	/**
	 * @group add
	 */
	public function test_add__メタテーブル無し_uniqueの指定がないときは追加() {
		$this->Meta_term->add( 'text', 'text' );
		$this->assertEquals( 'text', $this->Meta_term->get( 'text', true ) );
	}

	/**
	 * @group add
	 */
	public function test_add__メタテーブル無し_uniqueの指定あり_値未保存のときは追加() {
		$this->Meta_term->add( 'text', 'text', true );
		$this->assertEquals( array( 'text' ), $this->Meta_term->get( 'text' ) );
	}

	/**
	 * @group add
	 */
	public function test_add__メタテーブル無し_uniqueの指定あり_値があるときは追加しない() {
		$this->Meta_term->add( 'text', 'text' );
		$this->Meta_term->add( 'text', 'text2', true );
		$this->assertEquals( array( 'text' ), $this->Meta_term->get( 'text' ) );
	}

	/**
	 * @group delete
	 */
	public function test_delete__valueの指定がないときはそのキーを全て削除() {
		$this->Meta_post->add( 'text', 'text' );
		$this->Meta_post->add( 'text', 'text2' );
		$this->Meta_post->delete( 'text' );
		$this->assertSame( array(), $this->Meta_post->get( 'text' ) );
	}

	/**
	 * @group delete
	 */
	public function test_delete__valueの指定あり_値が一致するキーだけ削除() {
		$this->Meta_post->add( 'text', 'text' );
		$this->Meta_post->add( 'text', 'text2' );
		$this->Meta_post->delete( 'text', 'text2' );
		$this->assertSame(
			array(
				'text',
			),
			$this->Meta_post->get( 'text' )
		);
	}

	/**
	 * @group delete
	 */
	public function test_delete__メタテーブル無し_valueの指定がないときはそのキーを全て削除() {
		$this->Meta_term->add( 'text', 'text' );
		$this->Meta_term->add( 'text', 'text2' );
		$this->Meta_term->delete( 'text' );
		$this->assertSame( array(), $this->Meta_term->get( 'text' ) );
	}

	/**
	 * @group delete
	 */
	public function test_delete__メタテーブル無し_valueの指定あり_値が一致するキーだけ削除() {
		$this->Meta_term->add( 'text', 'text' );
		$this->Meta_term->add( 'text', 'text2' );
		$this->Meta_term->delete( 'text', 'text2' );
		$this->assertSame(
			array(
				'text',
			),
			$this->Meta_term->get( 'text' )
		);
	}

	/**
	 * @group delete
	 */
	public function test_delete__メタテーブル無し_キー指定が無いときは全メタデータ削除() {
		if ( !_get_meta_table( $this->Meta_term->get_meta_type() ) ) {
			$this->Meta_term->add( 'text'    , 'text' );
			$this->Meta_term->add( 'checkbox', 'checkbox-1' );
			$this->Meta_term->delete();
			$this->assertSame( array(), $this->Meta_term->get() );
		}
	}

	/**
	 * @group save
	 */
	public function test_save__送信されたデータが定義されていれば保存() {
		$POST = $this->_return_post_data_for_save( SCF_Config::NAME );
		$this->Meta_post->save( $POST );
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
	}

	/**
	 * @group save
	 */
	public function test_save__ロールの場合_送信されたデータが定義されていれば保存() {
		$POST = $this->_return_post_data_for_save( SCF_Config::NAME );
		$this->Meta_user->save( $POST );
		$this->assertEquals(
			array( 1, 2 ),
			SCF::get_user_meta( $this->user_id, 'checkbox' )
		);
		$this->assertEquals(
			array(
				array( 1, 2 ),
				array( 2, 3 ),
			),
			SCF::get_user_meta( $this->user_id, 'checkbox3' )
		);
	}

	/**
	 * @group save
	 */
	public function test_save__タームの場合_送信されたデータが定義されていれば保存() {
		$POST = $this->_return_post_data_for_save( SCF_Config::NAME );
		$this->Meta_term->save( $POST );
		$this->assertEquals(
			array( 1, 2 ),
			SCF::get_term_meta( $this->term_id, 'category', 'checkbox' )
		);
		$this->assertEquals(
			array(
				array( 1, 2 ),
				array( 2, 3 ),
			),
			SCF::get_term_meta( $this->term_id, 'category', 'checkbox3' )
		);
	}

	/**
	 * @group save
	 */
	public function test_save__SCFの定義されたデータが送信されていないときは保存しない() {
		$POST = $this->_return_post_data_for_save( 'dummy' );
		$this->Meta_post->save( $POST );
		$this->assertSame( array(), SCF::get( 'checkbox' , $this->post_id ) );
	}

	/**
	 * @group save
	 */
	public function test_save_ロールの場合_SCFの定義されたデータが送信されていないときは保存しない() {
		$POST = $this->_return_post_data_for_save( 'dummy' );
		$this->Meta_user->save( $POST );
		$this->assertSame( array(), SCF::get_user_meta( $this->user_id, 'checkbox' ) );
	}

	/**
	 * @group save
	 */
	public function test_save_タームの場合_SCFの定義されたデータが送信されていないときは保存しない() {
		$POST = $this->_return_post_data_for_save( 'dummy' );
		$this->Meta_term->save( $POST );
		$this->assertSame( array(), SCF::get_term_meta( $this->term_id, 'category', 'checkbox' ) );
	}

	protected function _return_post_data_for_save( $key ) {
		return array(
			$key => array(
				'checkbox'  => array(
					array( 1, 2 ),
				),
				'checkbox3' => array(
					array( 1, 2 ),
					array( 2, 3 ),
				),
			),
		);
	}

	/**
	 * @group restore
	 */
	public function test_restore__revisionのデータを復元() {
		$POST = $this->_return_post_data_for_save( SCF_Config::NAME );
		$this->Meta_revision->save( $POST );
		$this->Meta_post->restore( get_post( $this->revision_id ) );
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
	}
	
	/**
	 * @group is_saved
	 */
	public function test_is_saved__全てのメタデータが空ならfalse() {
		$this->assertFalse( $this->Meta_post->is_saved() );
		$this->assertFalse( $this->Meta_term->is_saved() );
		$this->assertFalse( $this->Meta_user->is_saved() );
	}
	
	/**
	 * @group is_saved
	 */
	public function test_is_saved__いずれかのメタデータが存在すればtrue() {
		$POST = $this->_return_post_data_for_save( SCF_Config::NAME );
		$this->Meta_post->save( $POST );
		$this->Meta_term->save( $POST );
		$this->Meta_user->save( $POST );
		
		$this->assertTrue( $this->Meta_post->is_saved() );
		$this->assertTrue( $this->Meta_term->is_saved() );
		$this->assertTrue( $this->Meta_user->is_saved() );
	}
	
	/**
	 * @group is_saved
	 */
	public function test_is_saved__投稿でautodraftのときはfalse() {
		$this->assertFalse( $this->Meta_new_post->is_saved() );
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
		if (
			(
				$type === 'post' &&
				( $id === $this->post_id || $id === $this->revision_id || $id === $this->new_post_id )
			) ||
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
			$settings['id-1'] = $Setting;
		}
		return $settings;
	}
}
