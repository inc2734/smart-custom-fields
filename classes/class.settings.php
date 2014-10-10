<?php
/**
 * Smart_Custom_Fields_Settings
 * Version    : 1.0.1
 * Author     : Takashi Kitajima
 * Created    : September 23, 2014
 * Modified   : October 10, 2014
 * License    : GPLv2
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
class Smart_Custom_Fields_Settings {

	/**
	 * __construct
	 */
	public function __construct() {
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
		add_action( 'save_post', array( $this, 'save_post' ) );
		add_action( 'init', array( $this, 'register_post_type' ) );
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
	}

	/**
	 * admin_enqueue_scripts
	 */
	public function admin_enqueue_scripts() {
		if ( get_post_type() === SCF_Config::NAME ) {
			wp_enqueue_style(
				SCF_Config::PREFIX . 'settings',
				plugin_dir_url( __FILE__ ) . '../css/settings.css'
			);
			wp_enqueue_script(
				SCF_Config::PREFIX . 'settings',
				plugin_dir_url( __FILE__ ) . '../js/settings.js',
				array( 'jquery' ),
				null,
				true
			);
			wp_localize_script( SCF_Config::PREFIX . 'settings', 'smart_cf_settings', array(
				'duplicate_alert' => esc_html__( 'Same name exists!', 'smart-custom-fields' ),
			) );
			wp_enqueue_script( 'jquery-ui-sortable' );
		}
	}

	/**
	 * register_post_type
	 */
	public function register_post_type() {
		register_post_type(
			SCF_Config::NAME,
			array(
				'label'                => 'Smart Custom Fields',
				'labels'               => array(
				),
				'public'               => false,
				'show_ui'              => true,
				'capability_type'      => 'page',
				'supports'             => array( 'title' ),
				'menu_position'        => 80,
			)
		);
	}

	/**
	 * add_meta_boxes
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
	 * get
	 * @param string $key 取得したいデータのキー
	 * @param array $data データ配列
	 * @return mixed
	 */
	private function get( $key, array $data ) {
		if ( isset( $data[$key] ) ) {
			return $data[$key];
		}
	}

	/**
	 * add_hide_class
	 * @param string $key 値があれば hide を表示
	 */
	private function add_hide_class( $key ) {
		if ( !$key ) {
			echo 'hide';
		}
	}

	/**
	 * display_meta_box
	 */
	public function display_meta_box() {
		$default = array(
			array(
				'group-name'  => '',
				'fields'  => array(),
			),
		);
		$settings = get_post_meta( get_the_ID(), SCF_Config::PREFIX . 'setting', true );
		$settings = wp_parse_args( $settings, $default );
		?>
		<div class="<?php echo esc_attr( SCF_Config::PREFIX . 'fields-wrapper' ); ?>">
			<div class="<?php echo esc_attr( SCF_Config::PREFIX . 'groups' ); ?>">
			<?php foreach ( $settings as $group_key => $group ) : ?>
				<?php
				$group_name = '';
				if ( !is_numeric( $group['group-name'] ) ) {
					$group_name = $group['group-name'];
				}
				array_unshift( $group['fields'], array() );
				?>
				<div class="<?php echo esc_attr( SCF_Config::PREFIX . 'group' ); ?> <?php $this->add_hide_class( $group_key ); ?>">
					<div class="btn-remove-group"><b>x</b></div>
					<div class="<?php echo esc_attr( SCF_Config::PREFIX . 'group-repeat' ); ?>">
						<label>
							<input type="checkbox"
								name="<?php echo esc_attr( SCF_Config::NAME . '[' . $group_key . '][repeat]' ); ?>"
								value="true"
								<?php checked( $this->get( 'repeat', $group ), true ); ?>
							/>
							<?php esc_html_e( 'Repeat', 'smart-custom-fields' ); ?>
						</label>
					</div>
					<table class="<?php echo esc_attr( SCF_Config::PREFIX . 'group-names' ); ?> <?php $this->add_hide_class( $this->get( 'repeat', $group ) ); ?>">
						<tr>
							<th><?php esc_html_e( 'Group name', 'smart-custom-fields' ); ?><span class="<?php echo esc_attr( SCF_Config::PREFIX . 'require' ); ?>">*</span></th>
							<td>
								<input type="text"
									name="<?php echo esc_attr( SCF_Config::NAME . '[' . $group_key . '][group-name]' ); ?>"
									size="30"
									class="<?php echo esc_attr( SCF_Config::PREFIX . 'group-name' ); ?>"
									value="<?php echo esc_attr( $group_name ); ?>"
								/>
							</td>
						</tr>
					</table>

					<div class="<?php echo esc_attr( SCF_Config::PREFIX . 'fields' ); ?>">
						<?php foreach ( $group['fields'] as $field_key => $field ) : ?>
						<div class="<?php echo esc_attr( SCF_Config::PREFIX . 'field' ); ?> <?php $this->add_hide_class( $field_key ); ?>">
							<?php
							$field_label = $this->get( 'label', $field );
							if ( !$field_label ) {
								$field_label = $this->get( 'name', $field );
							}
							?>
							<div class="btn-remove-field"><span><?php echo esc_html( $field_label ); ?></span><b>x</b></div>
							<table class="<?php $this->add_hide_class( !$this->get( 'name', $field ) ); ?>">
								<tr>
									<th><?php esc_html_e( 'Name', 'smart-custom-fields' ); ?><span class="<?php echo esc_attr( SCF_Config::PREFIX . 'require' ); ?>">*</span></th>
									<td>
										<input type="text"
											name="<?php echo esc_attr( SCF_Config::NAME . '[' . $group_key . '][fields][' . $field_key . '][name]' ); ?>"
											size="30"
											class="<?php echo esc_attr( SCF_Config::PREFIX . 'field-name' ); ?>"
											value="<?php echo esc_attr( $this->get( 'name', $field ) ); ?>"
										/>
									</td>
								</tr>
								<tr>
									<th><?php esc_html_e( 'Label', 'smart-custom-fields' ); ?></th>
									<td>
										<input type="text"
											name="<?php echo esc_attr( SCF_Config::NAME . '[' . $group_key . '][fields][' . $field_key . '][label]' ); ?>"
											size="30"
											class="<?php echo esc_attr( SCF_Config::PREFIX . 'field-label' ); ?>"
											value="<?php echo esc_attr( $this->get( 'label', $field ) ); ?>"
										/>
									</td>
								</tr>
								<tr>
									<th><?php esc_html_e( 'Type', 'smart-custom-fields' ); ?><span class="<?php echo esc_attr( SCF_Config::PREFIX . 'require' ); ?>">*</span></th>
									<td>
										<select
											name="<?php echo esc_attr( SCF_Config::NAME . '[' . $group_key . '][fields][' . $field_key . '][type]' ); ?>"
											class="<?php echo esc_attr( SCF_Config::PREFIX . 'field-select' ); ?>" />
											<?php
											$optgroups = array(
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
											foreach ( $optgroups as $optgroup_name => $optgroup_values ) {
												$optgroup_fields = array();
												$optgroup_values['options'] = apply_filters(
													SCF_Config::PREFIX . 'field-select-' . $optgroup_name,
													$optgroup_values['options']
												);
												foreach ( $optgroup_values['options'] as $option_key => $option ) {
													$optgroup_fields[] = sprintf(
														'<option value="%s" %s>%s</option>',
														esc_attr( $option_key ),
														selected( $this->get( 'type', $field ), $option_key, false ),
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
								<?php do_action( SCF_Config::PREFIX . 'field-options', $group_key, $field_key, $field ); ?>
							</table>
						</div>
						<?php endforeach; ?>
					</div>
					<div class="button btn-add-field <?php $this->add_hide_class( $this->get( 'repeat', $group ) ); ?>"><?php esc_html_e( 'Add Sub field', 'smart-custom-fields' ); ?></div>
				</div>
			<?php endforeach; ?>
			</div>
			<div class="button btn-add-group"><?php esc_html_e( 'Add Field', 'smart-custom-fields' ); ?></div>
		</div>
		<?php wp_nonce_field( SCF_Config::NAME . '-settings', SCF_Config::PREFIX . 'settings-nonce' ) ?>
		<?php
	}

	/**
	 * display_meta_box_condition
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
	 * save_post
	 */
	public function save_post( $post_id ) {
		if ( !isset( $_POST[SCF_Config::PREFIX . 'settings-nonce'] ) ) {
			return;
		}
		if ( !wp_verify_nonce( $_POST[SCF_Config::PREFIX . 'settings-nonce'], SCF_Config::NAME . '-settings' ) ) {
			return;
		}
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		if ( get_post_type() !== SCF_Config::NAME ) {
			return;
		}
		if ( !isset( $_POST[SCF_Config::NAME] ) ) {
			return;
		}

		$data = array();
		foreach ( $_POST[SCF_Config::NAME] as $group_key => $group_value ) {
			if ( !empty( $group_value['fields'] ) && count( $group_value['fields'] ) > 1 ) {
				$fields = array();
				foreach ( $group_value['fields'] as $field_value ) {
					if ( !empty( $field_value['name'] ) ) {
						$fields[] = $field_value;
					}
				}
				if ( !$fields )
					continue;

				if ( !empty( $group_value['repeat'] ) && $group_value['repeat'] === 'true' ) {
					$group_value['repeat'] = true;
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