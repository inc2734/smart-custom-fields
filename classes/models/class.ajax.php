<?php
/**
 * Smart_Custom_Fields_Ajax
 * Version    : 1.2.0
 * Author     : inc2734
 * Created    : April 27, 2015
 * Modified   : December 12, 2015
 * License    : GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
class Smart_Custom_Fields_Ajax {

	/**
	 * Hooking the process that it want to fire when the ajax request.
	 */
	public function __construct() {
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			add_action( 'delete_term', array( $this, 'delete_term' ), 10, 4 );
		}
	}

	/**
	 * Deleting term meta
	 *
	 * @param int    $term_id
	 * @param int    $term_taxonomy_id
	 * @param string $taxonomy
	 * @param object $deleted_term
	 */
	public function delete_term( $term_id, $term_taxonomy_id, $taxonomy, $deleted_term ) {
		$Meta = new Smart_Custom_Fields_Meta( $deleted_term );
		$Meta->delete_term_meta_for_wp43();
	}
}
