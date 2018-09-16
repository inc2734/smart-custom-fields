<?php
/**
 * Smart_Custom_Fields_Controller_Base
 * Version    : 1.4.0
 * Author     : inc2734
 * Created    : April 27, 2015
 * Modified   : June 4, 2016
 * License    : GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
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
	 * @param string $hook
	 */
	public function admin_enqueue_scripts( $hook ) {
		do_action( SCF_Config::PREFIX . 'before-editor-enqueue-scripts' );
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
		do_action( SCF_Config::PREFIX . 'after-editor-enqueue-scripts' );

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
	 * @param WP_Post|WP_User|WP_Term|stdClass $object
	 * @param array                            $callback_args custom field setting information
	 */
	public function display_meta_box( $object, $callback_args ) {
		$groups = $callback_args['args'];
		$tables = $this->get_tables( $object, $groups );

		printf( '<div class="%s">', esc_attr( SCF_Config::PREFIX . 'meta-box' ) );
		$index = 0;
		foreach ( $tables as $group_key => $Group ) {
			$is_repeatable = $Group->is_repeatable();
			if ( $is_repeatable && $index === 0 ) {
				printf(
					'<div class="%s">',
					esc_attr( SCF_Config::PREFIX . 'meta-box-repeat-tables' )
				);
				$this->display_tr( $object, $is_repeatable, $Group->get_fields() );
			}
			$this->display_tr( $object, $is_repeatable, $Group->get_fields(), $index );

			// If in the loop, count up the index.
			// If exit the loop, reset the count.
			if ( $is_repeatable &&
				 isset( $tables[ $group_key + 1 ] ) &&
				 $tables[ $group_key + 1 ]->get_name() === $Group->get_name() ) {
				$index ++;
			} else {
				$index = 0;
			}
			if ( $is_repeatable && $index === 0 ) {
				printf( '</div>' );
			}
		}
		printf( '</div>' );
		wp_nonce_field( SCF_Config::NAME . '-fields', SCF_Config::PREFIX . 'fields-nonce' );
	}

	/**
	 * Saving posted data
	 *
	 * @param array                            $data
	 * @param WP_Post|WP_User|WP_Term|stdClass $object
	 */
	protected function save( $data, $object ) {
		check_admin_referer(
			SCF_Config::NAME . '-fields',
			SCF_Config::PREFIX . 'fields-nonce'
		);

		$Meta = new Smart_Custom_Fields_Meta( $object );
		$Meta->save( $_POST );
	}

	/**
	 * Generating array for displaying the custom fields
	 *
	 * @param WP_Post|WP_User|WP_Term|stdClass $object
	 * @param array                            $groups Settings from custom field settings page
	 * @return array $tables Array for displaying a table for custom fields
	 */
	protected function get_tables( $object, $groups ) {
		$Meta = new Smart_Custom_Fields_Meta( $object );

		$repeat_multiple_data = SCF::get_repeat_multiple_data( $object );
		$tables               = array();
		foreach ( $groups as $Group ) {
			// If in the loop, Added groupgs by the amount of the loop.
			// Added only one if setting is repetition but not loop (Ex, new registration)
			if ( $Group->is_repeatable() === true ) {
				$loop_count = 1;
				$fields     = $Group->get_fields();
				foreach ( $fields as $Field ) {
					$field_name = $Field->get( 'name' );
					$meta       = $Meta->get( $field_name );
					if ( is_array( $meta ) ) {
						$meta_count = count( $meta );
						// When the same name of the custom field is a multiple (checbox or loop)
						if ( $meta_count > 1 ) {
							// checkbox
							if ( $Field->get_attribute( 'allow-multiple-data' ) ) {
								if ( is_array( $repeat_multiple_data ) && isset( $repeat_multiple_data[ $field_name ] ) ) {
									$repeat_multiple_data_count = count( $repeat_multiple_data[ $field_name ] );
									if ( $loop_count < $repeat_multiple_data_count ) {
										$loop_count = $repeat_multiple_data_count;
									}
								}
							}
							// other than checkbox
							else {
								if ( $loop_count < $meta_count ) {
									$loop_count = $meta_count;
								}
							}
						}
					}
				}
				if ( $loop_count >= 1 ) {
					for ( $i = $loop_count; $i > 0; $i -- ) {
						$tables[] = $Group;
					}
					continue;
				}
			}
			$tables[] = $Group;
		}
		return $tables;
	}

	/**
	 * Getting the multi-value field meta data.
	 *
	 * @param WP_Post|WP_User|WP_Term|stdClass $object
	 * @param Smart_Custom_Fields_Field_Base   $Field
	 * @param int                              $index
	 * @return array
	 */
	public function get_multiple_data_field_value( $object, $Field, $index ) {
		$Meta       = new Smart_Custom_Fields_Meta( $object );
		$field_name = $Field->get( 'name' );

		if ( is_null( $index ) ) {
			return SCF::get_default_value( $Field );
		}

		if ( ! $Meta->is_saved_the_key( $field_name ) ) {
			return SCF::get_default_value( $Field );
		}

		$value = $Meta->get( $field_name );

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
	 * @param WP_Post|WP_User|WP_Term|stdClass $object
	 * @param Smart_Custom_Fields_Field_Base   $Field
	 * @param int                              $index
	 * @return string
	 */
	public function get_single_data_field_value( $object, $Field, $index ) {
		$Meta       = new Smart_Custom_Fields_Meta( $object );
		$field_name = $Field->get( 'name' );

		if ( is_null( $index ) ) {
			return SCF::get_default_value( $Field, true );
		}

		if ( $Meta->is_saved_the_key( $field_name ) ) {
			$value = $Meta->get( $field_name );
			if ( isset( $value[ $index ] ) ) {
				return $value[ $index ];
			}
			return '';
		}
		return SCF::get_default_value( $Field, true );
	}

	/**
	 * Displaying tr element for table of custom fields
	 *
	 * @param WP_Post|WP_User|WP_Term|stdClass $object
	 * @param bool                             $is_repeat
	 * @param array                            $fields
	 * @param int, null                        $index
	 */
	protected function display_tr( $object, $is_repeat, $fields, $index = null ) {
		$Meta = new Smart_Custom_Fields_Meta( $object );

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

		foreach ( $fields as $Field ) {
			$display_name = $Field->get_attribute( 'display-name' );
			$field_type   = $Field->get_attribute( 'type' ); // gets the field type for use in aditional CSS classes
			$layout       = $Field->get_attribute( 'layout' ); // get layout type
			$field_name   = $Field->get( 'name' );
			$field_label  = $Field->get( 'label' );
			if ( ! $field_label ) {
				$field_label = $field_name;
			}

			// When multi-value field
			if ( $Field->get_attribute( 'allow-multiple-data' ) ) {
				$value = $this->get_multiple_data_field_value( $object, $Field, $index );
			}
			// When non multi-value field
			else {
				$value = $this->get_single_data_field_value( $object, $Field, $index );
			}

			$instruction = $Field->get( 'instruction' );
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

			$notes = $Field->get( 'notes' );
			if ( ! empty( $notes ) ) {
				$notes = sprintf(
					'<p class="description">%s</p>',
					esc_html( $notes )
				);
			}

			$form_field = $Field->get_field( $index, $value );

			// if the layout type is full-width, it hides the "Table Header" (th)
			$table_th = $layout != 'full-width' ? '<th>' . esc_html( $field_label ) . '</th>' : '';
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
