<?php
/**
 * Smart_Custom_Fields_Controller_Taxonomy
 * Version    : 1.0.1
 * Author     : inc2734
 * Created    : April 26, 2015
 * Modified   : May 31, 2016
 * License    : GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
class Smart_Custom_Fields_Controller_Taxonomy extends Smart_Custom_Fields_Controller_Base {

	/**
	 * __construct
	 */
	public function __construct() {
		parent::__construct();

		add_action( $_REQUEST['taxonomy'] . '_edit_form_fields', array( $this, 'edit_form_fields' ) );
		add_action( 'edited_terms', array( $this, 'update' ), 10, 2 );
		add_action( 'delete_term', array( $this, 'delete' ), 10, 4 );
	}

	/**
	 * Loading resources for term edit page
	 *
	 * @param string $hook
	 */
	public function admin_enqueue_scripts( $hook ) {
		parent::admin_enqueue_scripts( $hook );
		wp_enqueue_style(
			SCF_Config::PREFIX . 'taxonomy',
			plugins_url( SCF_Config::NAME ) . '/css/taxonomy.css'
		);
	}

	/**
	 * Displaying custom fields in term edit page
	 *
	 * @param object $term
	 */
	public function edit_form_fields( $term ) {
		$settings = SCF::get_settings( $term );
		foreach ( $settings as $Setting ) {
			$callback_args['args'] = $Setting->get_groups();
			?>
			<table class="form-table">
				<tr>
					<th scope="row"><?php echo esc_html( $Setting->get_title() ); ?></th>
					<td><?php $this->display_meta_box( $term, $callback_args ); ?></td>
				</tr>
			</table>
			<?php
		}
	}

	/**
	 * Saving meta data from custom fields in term edit page
	 *
	 * @param int    $term_id
	 * @param string $taxonomy
	 */
	public function update( $term_id, $taxonomy ) {
		if ( ! current_user_can( 'manage_categories' ) ) {
			return;
		}
		if ( ! isset( $_POST[ SCF_Config::NAME ] ) ) {
			return;
		}

		$term = get_term( $term_id, $taxonomy );
		$this->save( $_POST, $term );
	}

	/**
	 * Delete meta data
	 *
	 * @param int    $term_id
	 * @param int    $term_taxonomy_id
	 * @param string $taxonomy
	 * @param object $deleted_term
	 */
	public function delete( $term_id, $term_taxonomy_id, $taxonomy, $deleted_term ) {
		$Meta = new Smart_Custom_Fields_Meta( $deleted_term );
		$Meta->delete();
	}
}
