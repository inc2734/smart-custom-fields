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
			plugins_url( SCF_Config::NAME ) . '/js/yoast-seo-analysis.js',
			null,
			filemtime( plugin_dir_path( dirname( __FILE__ ) . '/../../js/yoast-seo-analysis.js' ) ),
			false
		);
	}
}
