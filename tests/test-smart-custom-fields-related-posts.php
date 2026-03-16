<?php
class Smart_Custom_Fields_Related_Posts_Test extends WP_UnitTestCase {

	/**
	 * @var Smart_Custom_Fields_Field_Related_Posts
	 */
	protected $Field;

	/**
	 * Set up.
	 */
	public function set_up() {
		parent::set_up();
		$this->Field = new Smart_Custom_Fields_Field_Related_Posts();

		$Cache = Smart_Custom_Fields_Cache::get_instance();
		$Cache->flush();
	}

	/**
	 * Tear down.
	 */
	public function tear_down() {
		parent::tear_down();

		$Cache = Smart_Custom_Fields_Cache::get_instance();
		$Cache->flush();
	}

	/**
	 * @group related_posts
	 */
	public function test_get_retrievable_post_types() {
		$contributor_id = $this->factory->user->create(
			array(
				'role' => 'contributor',
			)
		);
		wp_set_current_user( $contributor_id );

		$method = new ReflectionMethod( $this->Field, 'get_retrievable_post_types' );
		$method->setAccessible( true );

		$post_types = $method->invoke( $this->Field, array( 'post', 'page', 'invalid-post-type' ) );

		$this->assertSame( array( 'post' ), $post_types );
	}

	/**
	 * @group related_posts
	 */
	public function test_filter_readable_posts_for_current_user() {
		$admin_id       = $this->factory->user->create(
			array(
				'role' => 'administrator',
			)
		);
		$contributor_id = $this->factory->user->create(
			array(
				'role' => 'contributor',
			)
		);

		$published_post_id = $this->factory->post->create(
			array(
				'post_author'  => $admin_id,
				'post_status'  => 'publish',
				'post_title'   => 'Published',
				'post_content' => 'published content',
			)
		);
		$draft_post_id     = $this->factory->post->create(
			array(
				'post_author'  => $admin_id,
				'post_status'  => 'draft',
				'post_title'   => 'Admin Draft',
				'post_content' => 'admin draft content',
			)
		);
		$private_post_id   = $this->factory->post->create(
			array(
				'post_author'  => $admin_id,
				'post_status'  => 'private',
				'post_title'   => 'Admin Private',
				'post_content' => 'admin private content',
			)
		);
		$own_draft_post_id = $this->factory->post->create(
			array(
				'post_author'  => $contributor_id,
				'post_status'  => 'draft',
				'post_title'   => 'Own Draft',
				'post_content' => 'own draft content',
			)
		);

		wp_set_current_user( $contributor_id );

		$method = new ReflectionMethod( $this->Field, 'filter_readable_posts_for_current_user' );
		$method->setAccessible( true );

		$posts = $method->invoke(
			$this->Field,
			array(
				get_post( $published_post_id ),
				get_post( $draft_post_id ),
				get_post( $private_post_id ),
				get_post( $own_draft_post_id ),
			)
		);

		$this->assertSame( array( $published_post_id, $own_draft_post_id ), wp_list_pluck( $posts, 'ID' ) );
	}

	/**
	 * @group related_posts
	 */
	public function test_prepare_posts_for_response() {
		$post_id = $this->factory->post->create(
			array(
				'post_status'  => 'publish',
				'post_title'   => 'Response Post',
				'post_content' => 'secret content',
			)
		);

		$method = new ReflectionMethod( $this->Field, 'prepare_posts_for_response' );
		$method->setAccessible( true );

		$posts = $method->invoke( $this->Field, array( get_post( $post_id ) ) );

		$this->assertCount( 1, $posts );
		$this->assertSame( $post_id, $posts[0]->ID );
		$this->assertSame( 'Response Post', $posts[0]->post_title );
		$this->assertSame( 'publish', $posts[0]->post_status );
		$this->assertObjectNotHasProperty( 'post_content', $posts[0] );
	}
}
