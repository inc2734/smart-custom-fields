<?php
class Smart_Custom_Fields_Revision_Test extends WP_UnitTestCase {

	/**
	 * @var Smart_Custom_Fields_Revisions
	 */
	protected $Revision;

	/**
	 * @var int
	 */
	protected $post_id;

	/**
	 * @var int
	 */
	protected $revision_id;

	/**
	 * Set up.
	 */
	public function set_up() {
		parent::set_up();

		$this->Revision = new Smart_Custom_Fields_Revisions();

		// The post for custom fields
		$this->post_id = $this->factory->post->create(
			array(
				'post_type'   => 'post',
				'post_status' => 'publish',
			)
		);

		// The revision post for custom fields
		$this->revision_id = $this->factory->post->create(
			array(
				'post_type'   => 'revision',
				'post_parent' => $this->post_id,
				'post_status' => 'inherit',
				'post_name'   => $this->post_id . '-autosave-v1',
			)
		);

		add_filter( 'smart-cf-register-fields', array( $this, '_register' ), 10, 4 );

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
	 * @group wp_restore_post_revision
	 */
	public function test_wp_restore_post_revision() {
		// Meta data for the post
		add_post_meta( $this->post_id, 'text', 'text' );
		add_post_meta( $this->post_id, 'checkbox', 1 );

		// Meta data for the revision post
		add_metadata( 'post', $this->revision_id, 'text', 'text-revision' );
		add_metadata( 'post', $this->revision_id, 'checkbox', 2 );

		$this->assertEquals( 'text', get_post_meta( $this->post_id, 'text', true ) );
		$this->assertEquals( array( 1 ), get_post_meta( $this->post_id, 'checkbox' ) );

		$this->Revision->wp_restore_post_revision( $this->post_id, $this->revision_id );

		$this->assertEquals( 'text-revision', get_post_meta( $this->post_id, 'text', true ) );
		$this->assertEquals( array( 2 ), get_post_meta( $this->post_id, 'checkbox' ) );
	}

	/**
	 * @group wp_insert_post
	 */
	public function test_wp_insert_post() {
		$_REQUEST[ SCF_Config::PREFIX . 'fields-nonce' ] = wp_create_nonce( SCF_Config::NAME . '-fields' );

		$_POST = array(
			SCF_Config::NAME => array(
				'text' => array( 'text' ),
			),
		);
		$this->Revision->wp_insert_post( $this->revision_id );
		$this->assertEquals( 'text', SCF::get( 'text', $this->revision_id ) );

		$this->Revision->wp_insert_post( $this->post_id );
		$this->assertEquals( '', SCF::get( 'text', $this->post_id ) );
	}

	/**
	 * @group get_post_metadata
	 */
	public function test_get_post_metadata() {
		update_metadata( 'post', $this->revision_id, 'text', 'text' );

		$meta = $this->Revision->get_post_metadata( 'default-value', $this->post_id, 'text', true );
		$this->assertEquals( 'default-value', $meta );

		global $wp_query, $post;
		$wp_query->is_preview = true;
		$post                 = get_post( $this->post_id );
		$meta                 = $this->Revision->get_post_metadata( 'default-value', $this->post_id, 'text', true );
		$this->assertEquals( 'text', $meta );
	}

	/**
	 * Register custom fields using filter hook.
	 *
	 * @param array  $settings  Array of Smart_Custom_Fields_Setting object.
	 * @param string $type      Post type or Role.
	 * @param int    $id        Post ID or User ID.
	 * @param string $meta_type post or user.
	 * @return array
	 */
	public function _register( $settings, $type, $id, $meta_type ) {
		if ( 'post' === $type && ( $id === $this->post_id || $id === $this->revision_id ) ) {
			$Setting = SCF::add_setting( 'id-1', 'Register Test' );
			$Setting->add_group(
				0,
				false,
				array(
					array(
						'name'  => 'text',
						'label' => 'text',
						'type'  => 'text',
					),
				)
			);
			$Setting->add_group(
				'text-has-default',
				false,
				array(
					array(
						'name'    => 'text-has-default',
						'label'   => 'text has default',
						'type'    => 'text',
						'default' => 'a',
					),
				)
			);
			$Setting->add_group(
				'checkbox',
				false,
				array(
					array(
						'name'    => 'checkbox',
						'label'   => 'checkbox field',
						'type'    => 'check',
						'choices' => array( 'a', 'b', 'c' ),
					),
				)
			);
			$Setting->add_group(
				'checkbox-has-default',
				false,
				array(
					array(
						'name'    => 'checkbox-has-default',
						'label'   => 'checkbox has default',
						'type'    => 'check',
						'choices' => array( 'a', 'b', 'c' ),
						'default' => array( 'a' ),
					),
				)
			);
			$Setting->add_group(
				'checkbox-key-value',
				false,
				array(
					array(
						'name'    => 'checkbox-key-value',
						'label'   => 'checkbox key value',
						'type'    => 'check',
						'choices' => array(
							'a' => 'apple',
							'b' => 'banana',
							'c' => 'carrot',
						),
						'default' => array( 'a' ),
					),
				)
			);
			$Setting->add_group(
				'group',
				true,
				array(
					array(
						'name'  => 'repeat-text',
						'label' => 'repeat text',
						'type'  => 'text',
					),
					array(
						'name'    => 'repeat-checkbox',
						'label'   => 'repeat checkbox',
						'type'    => 'check',
						'choices' => array( 'a', 'b', 'c' ),
					),
				)
			);
			$settings[ $Setting->get_id() ] = $Setting;
		}
		return $settings;
	}
}
