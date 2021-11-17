<?php
/**
 * @package smart-custom-fields
 * @author inc2734
 * @license GPL-2.0+
 */

/**
 * Smart_Custom_Fields_Controller_Base class.
 */
class Smart_Custom_Fields_Controller_Base {

	/**
	 * Array of the form field objects
	 *
	 * @var array
	 */
	protected $fields = array();

	/**
	 * __construct
	 */
	public function __construct() {
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
	}

	/**
	 * Loading resources for edit page.
	 *
	 * @param string $hook The current admin page.
	 */
	public function admin_enqueue_scripts( $hook ) {
		do_action( SCF_Config::PREFIX . 'before-editor-enqueue-scripts', $hook );
		wp_enqueue_style(
			SCF_Config::PREFIX . 'editor',
			plugins_url( SCF_Config::NAME ) . '/css/editor.css'
		);
		wp_enqueue_media();
		wp_enqueue_script(
			SCF_Config::PREFIX . 'editor',
			plugins_url( SCF_Config::NAME ) . '/js/editor.js',
			array( 'jquery' ),
			null,
			true
		);
		wp_localize_script(
			SCF_Config::PREFIX . 'editor',
			'smart_cf_uploader',
			array(
				'image_uploader_title' => esc_html__( 'Image setting', 'smart-custom-fields' ),
				'file_uploader_title'  => esc_html__( 'File setting', 'smart-custom-fields' ),
			)
		);
		do_action( SCF_Config::PREFIX . 'after-editor-enqueue-scripts', $hook );

		if ( ! user_can_richedit() ) {
			wp_enqueue_script(
				'tinymce',
				includes_url( '/js/tinymce/tinymce.min.js' ),
				array(),
				null,
				true
			);
		}
	}

	/**
	 * Display custom fields in edit page.
	 *
	 * @param WP_Post|WP_User|WP_Term|stdClass $object        Object meta object.
	 * @param array                            $callback_args Custom field setting information.
	 */
	public function display_meta_box( $object, $callback_args ) {
		$groups = $callback_args['args'];
		$tables = $this->get_tables( $object, $groups );

		printf( '<div class="%s">', esc_attr( SCF_Config::PREFIX . 'meta-box' ) );
		$index = 0;
		foreach ( $tables as $group_key => $group ) {
			$is_repeatable = $group->is_repeatable();
			if ( $is_repeatable && 0 === $index ) {
				printf(
					'<div class="%s">',
					esc_attr( SCF_Config::PREFIX . 'meta-box-repeat-tables' )
				);
				$this->display_tr( $object, $is_repeatable, $group->get_fields() );
			}
			$this->display_tr( $object, $is_repeatable, $group->get_fields(), $index );

			// If in the loop, count up the index.
			// If exit the loop, reset the count.
			if (
				$is_repeatable
				&& isset( $tables[ $group_key + 1 ] )
				&& $group->get_name() === $tables[ $group_key + 1 ]->get_name()
			) {
				$index ++;
			} else {
				$index = 0;
			}
			if ( $is_repeatable && 0 === $index ) {
				printf( '</div>' );
			}
		}
		printf( '</div>' );
		wp_nonce_field( SCF_Config::NAME . '-fields', SCF_Config::PREFIX . 'fields-nonce' );
	}

	/**
	 * Saving posted data.
	 *
	 * @param array                            $data   Data.
	 * @param WP_Post|WP_User|WP_Term|stdClass $object Object meta object.
	 */
	protected function save( $data, $object ) {
		check_admin_referer(
			SCF_Config::NAME . '-fields',
			SCF_Config::PREFIX . 'fields-nonce'
		);

		$meta = new Smart_Custom_Fields_Meta( $object );
		$meta->save( $data );
	}

	/**
	 * Generating array for displaying the custom fields.
	 *
	 * @param WP_Post|WP_User|WP_Term|stdClass $object Object meta object.
	 * @param array                            $groups Settings from custom field settings page.
	 * @return array Array for displaying a table for custom fields.
	 */
	protected function get_tables( $object, $groups ) {
		$meta = new Smart_Custom_Fields_Meta( $object );

		$repeat_multiple_data = SCF::get_repeat_multiple_data( $object );
		$tables               = array();
		foreach ( $groups as $group ) {
			// If in the loop, Added groupgs by the amount of the loop.
			// Added only one if setting is repetition but not loop (Ex, new registration)
			if ( true === $group->is_repeatable() ) {
				$loop_count = 1;
				$fields     = $group->get_fields();
				foreach ( $fields as $field ) {
					$field_name = $field->get( 'name' );
					$meta_value = $meta->get( $field_name );
					if ( is_array( $meta_value ) ) {
						$meta_count = count( $meta_value );
						// When the same name of the custom field is a multiple (checbox or loop)
						if ( $meta_count > 1 ) {
							// checkbox
							if ( $field->get_attribute( 'allow-multiple-data' ) ) {
								if ( is_array( $repeat_multiple_data ) && isset( $repeat_multiple_data[ $field_name ] ) ) {
									$repeat_multiple_data_count = count( $repeat_multiple_data[ $field_name ] );
									if ( $loop_count < $repeat_multiple_data_count ) {
										$loop_count = $repeat_multiple_data_count;
									}
								}
							} else {
								// other than checkbox
								if ( $loop_count < $meta_count ) {
									$loop_count = $meta_count;
								}
							}
						}
					}
				}
				if ( $loop_count >= 1 ) {
					for ( $i = $loop_count; $i > 0; $i -- ) {
						$tables[] = $group;
					}
					continue;
				}
			}
			$tables[] = $group;
		}
		return $tables;
	}

	/**
	 * Getting the multi-value field meta data.
	 *
	 * @param WP_Post|WP_User|WP_Term|stdClass $object Object meta object.
	 * @param Smart_Custom_Fields_Field_Base   $field  Field object.
	 * @param int                              $index  Index of value.
	 * @return array
	 */
	public function get_multiple_data_field_value( $object, $field, $index ) {
		$meta       = new Smart_Custom_Fields_Meta( $object );
		$field_name = $field->get( 'name' );

		if ( is_null( $index ) ) {
			return SCF::get_default_value( $field );
		}

		if ( ! $meta->is_saved_the_key( $field_name ) ) {
			return SCF::get_default_value( $field );
		}

		$value = $meta->get( $field_name );

		// in the loop
		$repeat_multiple_data = SCF::get_repeat_multiple_data( $object );
		if ( is_array( $repeat_multiple_data ) && isset( $repeat_multiple_data[ $field_name ] ) ) {
			$now_num = 0;
			if ( is_array( $repeat_multiple_data[ $field_name ] ) && isset( $repeat_multiple_data[ $field_name ][ $index ] ) ) {
				$now_num = $repeat_multiple_data[ $field_name ][ $index ];
			}

			// The index is starting point to refer to the total of the previous number than me ($index)
			$_temp = array_slice( $repeat_multiple_data[ $field_name ], 0, $index );
			$sum   = array_sum( $_temp );
			$start = $sum;

			if ( $now_num ) {
				$value = array_slice( $value, $start, $now_num );
			} else {
				$value = array();
			}
		}
		return $value;
	}

	/**
	 * Getting the non multi-value field meta data.
	 *
	 * @param WP_Post|WP_User|WP_Term|stdClass $object Object meta object.
	 * @param Smart_Custom_Fields_Field_Base   $field  Field object.
	 * @param int                              $index  Index of value.
	 * @return string
	 */
	public function get_single_data_field_value( $object, $field, $index ) {
		$meta       = new Smart_Custom_Fields_Meta( $object );
		$field_name = $field->get( 'name' );

		if ( is_null( $index ) ) {
			return SCF::get_default_value( $field, true );
		}

		if ( $meta->is_saved_the_key( $field_name ) ) {
			$value = $meta->get( $field_name );
			if ( isset( $value[ $index ] ) ) {
				return $value[ $index ];
			}
			return '';
		}
		return SCF::get_default_value( $field, true );
	}

	/**
	 * Displaying tr element for table of custom fields.
	 *
	 * @param WP_Post|WP_User|WP_Term|stdClass $object    Object meta object.
	 * @param bool                             $is_repeat If repeat, return true.
	 * @param array                            $fields    Fields.
	 * @param int|null                         $index     Field index.
	 */
	protected function display_tr( $object, $is_repeat, $fields, $index = null ) {
		$btn_repeat = '';
		if ( $is_repeat ) {
			$btn_repeat  = sprintf(
				'<span class="%s"></span>',
				esc_attr( SCF_Config::PREFIX . 'icon-handle dashicons dashicons-menu' )
			);
			$btn_repeat .= '<span class="btn-add-repeat-group dashicons dashicons-plus-alt ' . SCF_Config::PREFIX . 'repeat-btn"></span>';
			$btn_repeat .= ' <span class="btn-remove-repeat-group dashicons dashicons-dismiss ' . SCF_Config::PREFIX . 'repeat-btn"></span>';
		}

		$style = '';
		if ( is_null( $index ) ) {
			$style = 'style="display: none;"';
		}

		printf(
			'<div class="%s" %s>%s',
			esc_attr( SCF_Config::PREFIX . 'meta-box-table' ),
			$style,
			$btn_repeat
		);

		foreach ( $fields as $field ) {
			$field_type  = $field->get_attribute( 'type' ); // gets the field type for use in aditional CSS classes
			$layout      = $field->get_attribute( 'layout' ); // get layout type
			$field_name  = $field->get( 'name' );
			$field_label = $field->get( 'label' );
			if ( ! $field_label ) {
				$field_label = $field_name;
			}

			if ( $field->get_attribute( 'allow-multiple-data' ) ) {
				// When multi-value field
				$value = $this->get_multiple_data_field_value( $object, $field, $index );
			} else {
				// When non multi-value field
				$value = $this->get_single_data_field_value( $object, $field, $index );
			}

			$instruction = $field->get( 'instruction' );
			if ( ! empty( $instruction ) ) {
				if ( apply_filters( SCF_Config::PREFIX . 'instruction-apply-html', false ) === true ) {
					$instruction_html = $instruction;
				} else {
					$instruction_html = esc_html( $instruction );
				}
				$instruction = sprintf(
					'<div class="instruction">%s</div>',
					$instruction_html
				);
			}

			$notes = $field->get( 'notes' );
			if ( ! empty( $notes ) ) {
				$notes = sprintf(
					'<p class="description">%s</p>',
					esc_html( $notes )
				);
			}

			$form_field = $field->get_field( $index, $value );

			// if the layout type is full-width, it hides the "Table Header" (th)
			$table_th = 'full-width' !== $layout ? '<th>' . esc_html( $field_label ) . '</th>' : '';
			printf(
				'<table class="%1$sfield-type-%6$s %1$slayout-type-%7$s"><tr>
					%2$s
					<td>
						%3$s
						%4$s
						%5$s
					</td>
				</tr></table>',
				SCF_Config::PREFIX,
				$table_th,
				$instruction,
				$form_field,
				$notes,
				$field_type,
				$layout
			);
		}
		echo '</div>';
	}
}
