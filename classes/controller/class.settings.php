<?php
/**
 * Smart_Custom_Fields_Controller_Settings
 * Version    : 1.1.0
 * Author     : Takashi Kitajima
 * Created    : September 23, 2014
 * Modified   : February 27, 2015
 * License    : GPLv2
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
class Smart_Custom_Fields_Controller_Settings {

	/**
	 * フィールド選択のセレクトボックスの選択肢用
	 * @var array
	 */
	private $optgroups = array();

	/**
	 * __construct
	 */
	public function __construct() {
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
		add_action( 'save_post', array( $this, 'save_post' ) );
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );

		$this->optgroups = array(
			'basic-fields' => array(
				'label'   => esc_attr__( 'Basic fields', 'smart-custom-fields' ),
				'options' => array(),
			),
			'select-fields' => array(
				'label'   => esc_attr__( 'Select fields', 'smart-custom-fields' ),
				'options' => array(),
			),
			'content-fields' => array(
				'label'   => esc_attr__( 'Content fields', 'smart-custom-fields' ),
				'options' => array(),
			),
			'other-fields' => array(
				'label'   => esc_attr__( 'Other fields', 'smart-custom-fields' ),
				'options' => array(),
			),
		);
	}

	/**
	 * CSS、JSの読み込み
	 */
	public function admin_enqueue_scripts() {
		do_action( SCF_Config::PREFIX . 'before-settings-enqueue-scripts' );
		wp_enqueue_style(
			SCF_Config::PREFIX . 'settings',
			plugins_url( SCF_Config::NAME ) . '/css/settings.css'
		);
		wp_enqueue_script(
			SCF_Config::PREFIX . 'settings',
			plugins_url( SCF_Config::NAME ) . '/js/settings.js',
			array( 'jquery' ),
			null,
			true
		);
		wp_localize_script( SCF_Config::PREFIX . 'settings', 'smart_cf_settings', array(
			'duplicate_alert' => esc_html__( 'Same name exists!', 'smart-custom-fields' ),
		) );
		wp_enqueue_script( 'jquery-ui-sortable' );
		do_action( SCF_Config::PREFIX . 'after-settings-enqueue-scripts' );
	}

	/**
	 * 投稿画面にカスタムフィールドを表示
	 */
	public function add_meta_boxes() {
		add_meta_box(
			SCF_Config::PREFIX . 'meta-box',
			__( 'Custom Fields', 'smart-custom-fields' ),
			array( $this, 'display_meta_box' ),
			SCF_Config::NAME
		);
		add_meta_box(
			SCF_Config::PREFIX . 'meta-box-condition',
			__( 'Display conditions', 'smart-custom-fields' ),
			array( $this, 'display_meta_box_condition' ),
			SCF_Config::NAME,
			'side'
		);
	}

	/**
	 * $key が空でなければ hide を表示
	 * 
	 * @param string $key
	 */
	private function add_hide_class( $key ) {
		if ( !$key ) {
			echo 'hide';
		}
	}

	/**
	 * 投稿画面にカスタムフィールドを表示
	 */
	public function display_meta_box() {
		$Setting = SCF::add_setting( get_the_ID(), get_the_title() );
		$Setting->add_group_unshift();
		$groups  = $Setting->get_groups();
		?>
		<div class="<?php echo esc_attr( SCF_Config::PREFIX . 'fields-wrapper' ); ?>">
			<div class="<?php echo esc_attr( SCF_Config::PREFIX . 'groups' ); ?>">
			<?php foreach ( $groups as $group_key => $Group ) : ?>
				<?php
				$fields = $Group->get_fields();
				array_unshift( $fields, SCF::get_form_field_instance( 'text' ) );
				?>
				<div class="<?php echo esc_attr( SCF_Config::PREFIX . 'group' ); ?> <?php $this->add_hide_class( $group_key ); ?>">
					<div class="btn-remove-group"><span class="dashicons dashicons-no-alt"></span></div>
					<?php $Group->display_options( $group_key ); ?>

					<div class="<?php echo esc_attr( SCF_Config::PREFIX . 'fields' ); ?>">
						<?php foreach ( $fields as $field_key => $Field ) : ?>
						<div class="<?php echo esc_attr( SCF_Config::PREFIX . 'field' ); ?> <?php $this->add_hide_class( $field_key ); ?>">
							<?php
							$field_label = $Field->get( 'label' );
							if ( !$field_label ) {
								$field_label = $Field->get( 'name' );
								if ( !$field_label ) {
									$field_label = "&nbsp;";
								}
							}
							?>
							<div class="<?php echo esc_attr( SCF_Config::PREFIX . 'icon-handle' ); ?>"></div>
							<b class="btn-remove-field"><span class="dashicons dashicons-no-alt"></span></b>
							<div class="field-label"><?php echo esc_html( $field_label ); ?></div>
							<table class="<?php $this->add_hide_class( !$Field->get( 'name' ) ); ?>">
								<tr>
									<th><?php esc_html_e( 'Type', 'smart-custom-fields' ); ?><span class="<?php echo esc_attr( SCF_Config::PREFIX . 'require' ); ?>">*</span></th>
									<td>
										<select
											name="<?php echo esc_attr( $Field->get_field_name_in_setting( $group_key, $field_key, 'type' ) ); ?>"
											class="<?php echo esc_attr( SCF_Config::PREFIX . 'field-select' ); ?>" />
											<?php
											foreach ( $this->optgroups as $optgroup_name => $optgroup_values ) {
												$optgroup_fields = array();
												$optgroup_values['options'] = apply_filters(
													SCF_Config::PREFIX . 'field-select-' . $optgroup_name,
													$optgroup_values['options']
												);
												foreach ( $optgroup_values['options'] as $option_key => $option ) {
													$optgroup_fields[] = sprintf(
														'<option value="%s" %s>%s</option>',
														esc_attr( $option_key ),
														selected( $Field->get_attribute( 'type' ), $option_key, false ),
														esc_html( $option )
													);
												}
												printf(
													'<optgroup label="%s">%s</optgroup>',
													$optgroup_values['label'],
													implode( '', $optgroup_fields )
												);
											}
											?>
										</select>
									</td>
								</tr>
								<?php $Field->display_options( $group_key, $field_key ); ?>
							</table>
						</div>
						<?php endforeach; ?>
					</div>
					<div class="button btn-add-field <?php $this->add_hide_class( $Group->is_repeatable() ); ?>"><?php esc_html_e( 'Add Sub field', 'smart-custom-fields' ); ?></div>
				</div>
			<?php endforeach; ?>
			</div>
			<div class="button btn-add-group"><?php esc_html_e( 'Add Field', 'smart-custom-fields' ); ?></div>
		</div>
		<?php wp_nonce_field( SCF_Config::NAME . '-settings', SCF_Config::PREFIX . 'settings-nonce' ) ?>
		<?php
	}

	/**
	 * メタボックスの表示条件を設定するメタボックスを表示
	 */
	public function display_meta_box_condition() {
		$post_types = get_post_types( array(
			'show_ui'  => true,
		), 'objects' );
		unset( $post_types['attachment'] );
		unset( $post_types[SCF_Config::NAME] );

		$conditions = get_post_meta( get_the_ID(), SCF_Config::PREFIX . 'condition', true );
		$post_type_field = '';
		foreach ( $post_types as $post_type => $post_type_object ) {
			$current = ( is_array( $conditions ) && in_array( $post_type, $conditions ) ) ? $post_type : false;
			$post_type_field .= sprintf(
				'<label><input type="checkbox" name="%s" value="%s" %s /> %s</label>',
				esc_attr( SCF_Config::PREFIX . 'condition[]' ),
				esc_attr( $post_type ),
				checked( $current, $post_type, false ),
				esc_attr( $post_type_object->labels->singular_name )
			);
		}
		printf(
			'<p><b>%s</b>%s</p>',
			esc_html__( 'Post Types', 'smart-custom-fields' ),
			$post_type_field
		);

		$condition_post_ids = get_post_meta( get_the_ID(), SCF_Config::PREFIX . 'condition-post-ids', true );
		printf(
			'<p><b>%s</b><input type="text" name="%s" value="%s" class="widefat" /></p>',
			esc_html__( 'Post Ids ( Comma separated )', 'smart-custom-fields' ),
			esc_attr( SCF_Config::PREFIX . 'condition-post-ids' ),
			$condition_post_ids
		);
	}

	/**
	 * 設定を保存
	 *
	 * @param int $post_id
	 */
	public function save_post( $post_id ) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		if ( !isset( $_POST[SCF_Config::NAME] ) ) {
			return;
		}
		check_admin_referer(
			SCF_Config::NAME . '-settings',
			SCF_Config::PREFIX . 'settings-nonce'
		);

		$data = array();
		foreach ( $_POST[SCF_Config::NAME] as $group_key => $group_value ) {
			// $group_key = 0 は隠しフィールドなので保存不要
			if ( $group_key === 0 ) {
				continue;
			}
			if ( !empty( $group_value['fields'] ) && count( $group_value['fields'] ) > 1 ) {
				$fields = array();
				foreach ( $group_value['fields'] as $field_key => $field_value ) {
					// $field_key = 0 は隠しフィールドなので保存不要
					if ( $field_key === 0 ) {
						continue;
					}
					if ( !empty( $field_value['name'] ) ) {
						$fields[] = $field_value;
					}
				}
				if ( !$fields ) {
					continue;
				}

				if ( !empty( $group_value['repeat'] ) && $group_value['repeat'] === 'true' ) {
					$group_value['repeat'] = true;
				} else {
					$group_value['repeat'] = false;
				}

				// repeat が true でないときは name を空に
				// true のときで、name から空のときは index を代入
				if ( !( isset( $group_value['repeat'] ) && $group_value['repeat'] === true && !empty( $group_value['group-name'] ) ) ) {
					$group_value['group-name'] = $group_key;
				}

				$group_value['fields'] = $fields;
				$data[] = $group_value;
			}
		}
		update_post_meta( $post_id, SCF_Config::PREFIX . 'setting', $data );

		if ( !isset( $_POST[SCF_Config::PREFIX . 'condition'] ) ) {
			delete_post_meta( $post_id, SCF_Config::PREFIX . 'condition' );
		} else {
			update_post_meta( $post_id, SCF_Config::PREFIX . 'condition', $_POST[SCF_Config::PREFIX . 'condition'] );
		}

		if ( !isset( $_POST[SCF_Config::PREFIX . 'condition-post-ids'] ) ) {
			delete_post_meta( $post_id, SCF_Config::PREFIX . 'condition-post-ids' );
		} else {
			update_post_meta( $post_id, SCF_Config::PREFIX . 'condition-post-ids', $_POST[SCF_Config::PREFIX . 'condition-post-ids'] );
		}
	}
}
