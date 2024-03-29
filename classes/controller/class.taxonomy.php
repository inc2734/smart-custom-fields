<?php
/**
 * @package smart-custom-fields
 * @author inc2734
 * @license GPL-2.0+
 */

/**
 * Smart_Custom_Fields_Controller_Taxonomy class.
 */
class Smart_Custom_Fields_Controller_Taxonomy extends Smart_Custom_Fields_Controller_Base {

	/**
	 * __construct
	 */
	public function __construct() {
		parent::__construct();

		$requested_taxonomy = filter_input( INPUT_POST, 'taxonomy' );
		$requested_taxonomy = $requested_taxonomy ? $requested_taxonomy : filter_input( INPUT_GET, 'taxonomy' );
		if ( $requested_taxonomy ) {
			add_action( $requested_taxonomy . '_edit_form_fields', array( $this, 'edit_form_fields' ) );
		}

		add_action( 'edited_terms', array( $this, 'update' ), 10, 2 );
		add_action( 'delete_term', array( $this, 'delete' ), 10, 4 );
	}

	/**
	 * Loading resources for term edit page.
	 *
	 * @param string $hook The current admin page.
	 */
	public function admin_enqueue_scripts( $hook ) {
		parent::admin_enqueue_scripts( $hook );
		wp_enqueue_style(
			SCF_Config::PREFIX . 'taxonomy',
			SMART_CUSTOM_FIELDS_URL . '/css/taxonomy.css',
			array(),
			filemtime( SMART_CUSTOM_FIELDS_PATH . '/css/taxonomy.css' )
		);
	}

	/**
	 * Displaying custom fields in term edit page.
	 *
	 * @param object $term Term object.
	 */
	public function edit_form_fields( $term ) {
		$settings      = SCF::get_settings( $term );
		$callback_args = array();
		foreach ( $settings as $setting ) {
			$callback_args['args'] = $setting->get_groups();
			?>
			<table class="form-table">
				<tr>
					<th scope="row"><?php echo esc_html( $setting->get_title() ); ?></th>
					<td><?php $this->display_meta_box( $term, $callback_args ); ?></td>
				</tr>
			</table>
			<?php
		}
	}

	/**
	 * Saving meta data from custom fields in term edit page.
	 *
	 * @param int    $term_id  Term ID.
	 * @param string $taxonomy Taxonomy slug.
	 */
	public function update( $term_id, $taxonomy ) {
		if ( ! current_user_can( 'manage_categories' ) ) {
			return;
		}

		if ( ! filter_input( INPUT_POST, SCF_Config::NAME, FILTER_DEFAULT, FILTER_REQUIRE_ARRAY ) ) {
			return;
		}

		$term = get_term( $term_id, $taxonomy );
		$this->save( filter_input_array( INPUT_POST ), $term );
	}

	/**
	 * Delete meta data.
	 *
	 * @param int    $term_id          Term ID.
	 * @param int    $term_taxonomy_id Term taxonomy ID.
	 * @param string $taxonomy         Taxonomy slug.
	 * @param object $deleted_term     Copy of the already-deleted term.
	 */
	public function delete( $term_id, $term_taxonomy_id, $taxonomy, $deleted_term ) {
		$meta = new Smart_Custom_Fields_Meta( $deleted_term );
		$meta->delete();
	}
}
