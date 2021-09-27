<?php
/**
 * @package smart-custom-fields
 * @author inc2734
 * @license GPL-2.0+
 */

/**
 * Smart_Custom_Fields_Field_Related_Terms class.
 */
class Smart_Custom_Fields_Field_Related_Terms extends Smart_Custom_Fields_Field_Base {

	/**
	 * Set the required items.
	 *
	 * @return array
	 */
	protected function init() {
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
		add_action( 'wp_ajax_smart-cf-relational-terms-search', array( $this, 'relational_terms_search' ) );
		add_filter( 'smart-cf-validate-get-value', array( $this, 'validate_get_value' ), 10, 2 );
		return array(
			'type'                => 'taxonomy',
			'display-name'        => __( 'Related Terms', 'smart-custom-fields' ),
			'optgroup'            => 'other-fields',
			'allow-multiple-data' => true,
		);
	}

	/**
	 * Set the non required items.
	 *
	 * @return array
	 */
	protected function options() {
		return array(
			'taxonomy'    => '',
			'limit'       => 0,
			'instruction' => '',
			'notes'       => '',
		);
	}

	/**
	 * Loading resources.
	 */
	public function admin_enqueue_scripts() {
		wp_enqueue_script(
			SCF_Config::PREFIX . 'editor-relation-common',
			plugins_url( SCF_Config::NAME ) . '/js/editor-relation-common.js',
			array( 'jquery' ),
			filemtime( plugin_dir_path( dirname( __FILE__ ) . '/../../js/editor-relation-common.js' ) ),
			true
		);

		wp_enqueue_script(
			SCF_Config::PREFIX . 'editor-relation-taxonomies',
			plugins_url( SCF_Config::NAME ) . '/js/editor-relation-taxonomies.js',
			array( 'jquery' ),
			filemtime( plugin_dir_path( dirname( __FILE__ ) . '/../../js/editor-relation-taxonomies.js' ) ),
			true
		);

		wp_localize_script(
			SCF_Config::PREFIX . 'editor-relation-taxonomies',
			'smart_cf_relation_taxonomies',
			array(
				'endpoint' => admin_url( 'admin-ajax.php' ),
				'action'   => SCF_Config::PREFIX . 'relational-terms-search',
				'nonce'    => wp_create_nonce( SCF_Config::NAME . '-relation-taxonomies' ),
			)
		);
	}

	/**
	 * Process that loading post when clicking post load button.
	 */
	public function relational_terms_search() {
		check_ajax_referer( SCF_Config::NAME . '-relation-taxonomies', 'nonce' );
		$_terms = array();
		$args   = array();
		if ( isset( $_POST['taxonomies'] ) ) {
			$taxonomies = explode( ',', $_POST['taxonomies'] );
			$args       = array(
				'order'        => 'ASC',
				'orderby'      => 'ID',
				'number'       => '',
				'hide_empty'   => false,
				'hierarchical' => false,
			);

			if ( isset( $_POST['click_count'] ) ) {
				$number = get_option( 'posts_per_page' );
				$offset = $_POST['click_count'] * $number;
				$args   = array_merge(
					$args,
					array(
						'offset' => $offset,
						'number' => $number,
					)
				);
			}

			if ( isset( $_POST['search'] ) ) {
				$args = array_merge(
					$args,
					array(
						'search' => $_POST['search'],
					)
				);
			}
			$_terms = get_terms( $taxonomies, $args );
		}
		header( 'Content-Type: application/json; charset=utf-8' );
		echo json_encode( $_terms );
		die();
	}

	/**
	 * Getting the field.
	 *
	 * @param int    $index Field index.
	 * @param string $value The value.
	 * @return string
	 */
	public function get_field( $index, $value ) {
		$name       = $this->get_field_name_in_editor( $index );
		$disabled   = $this->get_disable_attribute( $index );
		$taxonomies = $this->get( 'taxonomy' );
		$limit      = $this->get( 'limit' );
		if ( ! $taxonomies ) {
			$taxonomies = array( 'category' );
		}
		if ( ! preg_match( '/^\d+$/', $limit ) ) {
			$limit = '';
		}
		$number = get_option( 'posts_per_page' );

		// choicse
		$choices_terms = get_terms(
			$taxonomies,
			array(
				'order'        => 'ASC',
				'orderby'      => 'ID',
				'hide_empty'   => false,
				'hierarchical' => false,
				'number'       => $number,
			)
		);
		$choices_li    = array();
		foreach ( $choices_terms as $_term ) {
			$term_name = $_term->name;
			if ( empty( $term_name ) ) {
				$term_name = '&nbsp;';
			}
			$choices_li[] = sprintf(
				'<li data-id="%d">%s</li>',
				$_term->term_id,
				$term_name
			);
		}

		// selected
		$selected_terms = array();
		if ( ! empty( $value ) && is_array( $value ) ) {
			foreach ( $value as $term_id ) {
				$term_name = get_term( $term_id )->name;
				if ( empty( $term_name ) ) {
					$term_name = '&nbsp;';
				}
				$selected_terms[ $term_id ] = $term_name;
			}
		}
		$selected_li = array();
		$hidden      = array();
		foreach ( $selected_terms as $term_id => $term_name ) {
			$selected_li[] = sprintf(
				'<li data-id="%d"><span class="%s"></span>%s<span class="relation-remove">-</li></li>',
				$term_id,
				esc_attr( SCF_Config::PREFIX . 'icon-handle dashicons dashicons-menu' ),
				$term_name
			);
			$hidden[]      = sprintf(
				'<input type="hidden" name="%s" value="%d" %s />',
				esc_attr( $name . '[]' ),
				$term_id,
				disabled( true, $disabled, false )
			);
		}

		$hide_class = '';
		if ( count( $choices_li ) < $number ) {
			$hide_class = 'hide';
		}

		return sprintf(
			'<div class="%s" data-taxonomies="%s" data-limit="%d">
				<div class="%s">
					<input type="text" class="widefat search-input search-input-terms" name="search-input" placeholder="%s" />
				</div>
				<div class="%s">
					<ul>%s</ul>
					<p class="load-relation-items load-relation-terms %s">%s</p>
					<input type="hidden" name="%s" %s />
					%s
				</div>
			</div>
			<div class="%s"><ul>%s</ul></div>
			<div class="clear"></div>',
			SCF_Config::PREFIX . 'relation-left',
			implode( ',', $taxonomies ),
			$limit,
			SCF_Config::PREFIX . 'search',
			esc_attr__( 'Search...', 'smart-custom-fields' ),
			SCF_Config::PREFIX . 'relation-children-select',
			implode( '', $choices_li ),
			$hide_class,
			esc_html__( 'Load more', 'smart-custom-fields' ),
			esc_attr( $name ),
			disabled( true, $disabled, false ),
			implode( '', $hidden ),
			SCF_Config::PREFIX . 'relation-right',
			implode( '', $selected_li )
		);
	}

	/**
	 * Displaying the option fields in custom field settings page.
	 *
	 * @param int $group_key Group key.
	 * @param int $field_key Field key.
	 */
	public function display_field_options( $group_key, $field_key ) {
		$this->display_label_option( $group_key, $field_key );
		$this->display_name_option( $group_key, $field_key );
		?>
		<tr>
			<th><?php esc_html_e( 'Taxonomies', 'smart-custom-fields' ); ?></th>
			<td>
				<?php
				$tasonomies = get_taxonomies(
					array(
						'show_ui' => true,
					),
					'objects'
				);
				?>
				<?php foreach ( $tasonomies as $taxonomy => $taxonomy_object ) : ?>
					<?php
					$save_taxonomies = $this->get( 'taxonomy' );
					$checked         = is_array( $save_taxonomies ) && in_array( $taxonomy, $save_taxonomies, true )
						? 'checked="checked"'
						: '';
					?>
				<input type="checkbox"
					name="<?php echo esc_attr( $this->get_field_name_in_setting( $group_key, $field_key, 'taxonomy' ) ); ?>[]"
					value="<?php echo esc_attr( $taxonomy ); ?>"
					<?php echo $checked; ?> /><?php echo esc_html( $taxonomy_object->labels->singular_name ); ?>
				<?php endforeach; ?>
			</td>
		</tr>
		<tr>
			<th><?php esc_html_e( 'Selectable number', 'smart-custom-fields' ); ?></th>
			<td>
				<input type="number"
					name="<?php echo esc_attr( $this->get_field_name_in_setting( $group_key, $field_key, 'limit' ) ); ?>"
					value="<?php echo esc_attr( $this->get( 'limit' ) ); ?>" min="1" step="1" />
			</td>
		</tr>
		<tr>
			<th><?php esc_html_e( 'Instruction', 'smart-custom-fields' ); ?></th>
			<td>
				<textarea name="<?php echo esc_attr( $this->get_field_name_in_setting( $group_key, $field_key, 'instruction' ) ); ?>"
					class="widefat" rows="5"><?php echo esc_attr( $this->get( 'instruction' ) ); ?></textarea>
			</td>
		</tr>
		<tr>
			<th><?php esc_html_e( 'Notes', 'smart-custom-fields' ); ?></th>
			<td>
				<input type="text"
					name="<?php echo esc_attr( $this->get_field_name_in_setting( $group_key, $field_key, 'notes' ) ); ?>"
					class="widefat"
					value="<?php echo esc_attr( $this->get( 'notes' ) ); ?>"
				/>
			</td>
		</tr>
		<?php
	}

	/**
	 * Validating when displaying meta data.
	 *
	 * @param array  $value      The value.
	 * @param string $field_type Field type.
	 * @return array
	 */
	public function validate_get_value( $value, $field_type ) {
		if ( $field_type === $this->get_attribute( 'type' ) ) {
			$validated_value = array();
			foreach ( $value as $term ) {
				$validated_value[] = $term;
			}
			$value = $validated_value;
		}
		return $value;
	}
}
