<?php
/**
 * Smart_Custom_Fields_Ajax
 * Version    : 1.1.0
 * Author     : Takashi Kitajima
 * Created    : April 27, 2015
 * Modified   : 
 * License    : GPLv2
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
class Smart_Custom_Fields_Ajax {

	/**
	 * Ajax リクエストのときだけ発火させたい処理をフックさせる
	 */
	public function __construct() {
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			add_action( 'delete_term', array( $this, 'delete_term' ), 10, 4 );
		}
	}

	/**
	 * タームのメタ情報を削除
	 *
	 * @param int $term_id
	 * @param int $term_taxonomy_id
	 * @param string $taxonomy
	 * @param object $deleted_term
	 */
	public function delete_term( $term_id, $term_taxonomy_id, $taxonomy, $deleted_term ) {
		$Meta = new Smart_Custom_Fields_Meta( $deleted_term );
		$Meta->delete();
	}
}
