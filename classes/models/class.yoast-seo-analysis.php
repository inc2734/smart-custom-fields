<?php
/**
 * Smart_Custom_Fields_Yoast_SEO_Analysis
 * Version    : 1.0.0
 * Author     : robssanches
 * Created    : July 11, 2018
 * Modified   : July 11, 2018
 * License    : GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
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
	 *
	 * @param string $hook
	 */
	public function admin_enqueue_scripts( $hook ) {
		wp_enqueue_script(
			SCF_Config::PREFIX . 'yoast-seo-analysis',
			plugins_url( SCF_Config::NAME ) . '/js/yoast-seo-analysis.js',
			null,
			filemtime( plugin_dir_path( dirname( __FILE__ ) . '/../../js/yoast-seo-analysis.js' ) ),
			false
		);
	}
}
