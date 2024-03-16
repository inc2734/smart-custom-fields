<?php
/**
 * @package smart-custom-fields
 * @author inc2734
 * @license GPL-2.0+
 */

/**
 * Smart_Custom_Fields_Yoast_SEO_Analysis class.
 */
class Smart_Custom_Fields_Yoast_SEO_Analysis {

	/**
	 * __construct
	 */
	public function __construct() {
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
	}

	/**
	 * Loading resources.
	 */
	public function admin_enqueue_scripts() {
		wp_enqueue_script(
			SCF_Config::PREFIX . 'yoast-seo-analysis',
			SMART_CUSTOM_FIELDS_URL . '/js/yoast-seo-analysis.js',
			array(),
			filemtime( SMART_CUSTOM_FIELDS_PATH . '/js/yoast-seo-analysis.js' ),
			false
		);
	}
}
