<?php
/**
 * @package smart-custom-fields
 * @author inc2734
 * @license GPL-2.0+
 */

/**
 * Smart_Custom_Fields_Ajax class.
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
	 * Deleting term meta.
	 *
	 * @param int    $term_id          Term ID.
	 * @param int    $term_taxonomy_id Term taxonomy ID.
	 * @param string $taxonomy         Taxonomy slug.
	 * @param object $deleted_term     Copy of the already-deleted term.
	 */
	public function delete_term( $term_id, $term_taxonomy_id, $taxonomy, $deleted_term ) {
		$meta = new Smart_Custom_Fields_Meta( $deleted_term );
		$meta->delete_term_meta_for_wp43();
	}
}
