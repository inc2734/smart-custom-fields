<?php
class SCF__No_Registerd_Test extends WP_UnitTestCase {

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
	 * @group get
	 */
	public function test_get() {
		$this->assertNull( SCF::get( 'text', false ) );
	}

	/**
	 * @group get_term_meta
	 */
	public function test_get_term_meta() {
		$this->assertNull( SCF::get_term_meta( $this->term_id, 'category', 'text' ) );
	}

	/**
	 * @group get_user_meta
	 */
	public function test_get_user_meta() {
		$this->assertNull( SCF::get_user_meta( $this->user_id, 'category', 'text' ) );
	}

	protected function create_revision( $post_id ) {
		return $this->factory->post->create( array(
			'post_type'   => 'revision',
			'post_parent' => $post_id,
			'post_status' => 'inherit',
			'post_name'   => $post_id . '-autosave',
		) );
	}
}
