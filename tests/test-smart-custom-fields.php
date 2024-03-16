<?php
class Smart_Custom_Fields_Test extends WP_UnitTestCase {

	/**
	 * @var array
	 */
	protected $post_ids;

	/**
	 * Set up.
	 */
	public function set_up() {
		parent::set_up();

		$this->post_ids = $this->factory->post->create_many(
			5,
			array(
				'post_type' => SCF_Config::NAME,
			)
		);

		foreach ( $this->post_ids as $post_id ) {
			update_post_meta( $post_id, SCF_Config::PREFIX . 'repeat-multiple-data', 'dummy' );
		}

		for ( $i = 1; $i <= 5; $i++ ) {
			update_option( SCF_Config::PREFIX . $i, 'dummy' );
		}

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
	 * @group uninstall
	 */
	public function test_uninstall__post() {
		Smart_Custom_Fields::uninstall();
		$posts = get_posts(
			array(
				'post_type'      => SCF_Config::NAME,
				'posts_per_page' => -1,
				'post_status'    => 'any',
			)
		);
		$this->assertEquals( 0, count( $posts ) );
	}

	/**
	 * @group uninstall
	 */
	public function test_uninstall__repeat_multiple_data() {
		Smart_Custom_Fields::uninstall();

		// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		global $wpdb;
		$var = $wpdb->get_var(
			$wpdb->prepare(
				"
				SELECT count( * ) FROM $wpdb->postmeta
					WHERE meta_key = %s
				",
				SCF_Config::PREFIX . 'repeat-multiple-data'
			)
		);
		// phpcs:enable

		$this->assertEquals( 0, $var );
	}

	/**
	 * @group uninstall
	 */
	public function test_uninstall__option() {
		Smart_Custom_Fields::uninstall();

		// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		global $wpdb;
		$var = $wpdb->get_var(
			$wpdb->prepare(
				"
				SELECT count( * ) FROM $wpdb->options
					WHERE option_name LIKE %s
				",
				SCF_Config::PREFIX . '%'
			)
		);
		// phpcs:enable

		$this->assertEquals( 0, $var );
	}
}
