<?php
/**
 * @package smart-custom-fields
 * @author inc2734
 * @license GPL-2.0+
 */

/**
 * Smart_Custom_Fields_Field_Colorpicker class.
 */
class Smart_Custom_Fields_Field_Colorpicker extends Smart_Custom_Fields_Field_Base {

	/**
	 * Set the required items.
	 *
	 * @return array
	 */
	protected function init() {
		add_action(
			SCF_Config::PREFIX . 'before-editor-enqueue-scripts',
			array( $this, 'editor_enqueue_scripts' )
		);
		add_action(
			SCF_Config::PREFIX . 'before-settings-enqueue-scripts',
			array( $this, 'settings_enqueue_scripts' )
		);
		return array(
			'type'         => 'colorpicker',
			'display-name' => __( 'Color picker', 'smart-custom-fields' ),
			'optgroup'     => 'other-fields',
		);
	}

	/**
	 * Set the non required items.
	 *
	 * @return array
	 */
	protected function options() {
		return array(
			'default'     => '',
			'instruction' => '',
			'notes'       => '',
		);
	}

	/**
	 * Loading resources for editor.
	 */
	public function editor_enqueue_scripts() {
		wp_enqueue_style( 'wp-color-picker' );

		wp_enqueue_script(
			SCF_Config::PREFIX . 'editor-colorpicker',
			plugins_url( SCF_Config::NAME ) . '/js/editor-colorpicker.js',
			array( 'jquery', 'wp-color-picker' ),
			filemtime( plugin_dir_path( dirname( __FILE__ ) . '/../../js/editor-colorpicker.js' ) ),
			true
		);
	}

	/**
	 * Loading resources for editor for custom field settings page.
	 */
	public function settings_enqueue_scripts() {
		wp_enqueue_style( 'wp-color-picker' );

		wp_enqueue_script(
			SCF_Config::PREFIX . 'settings-colorpicker',
			plugins_url( SCF_Config::NAME ) . '/js/settings-colorpicker.js',
			array( 'jquery', 'wp-color-picker' ),
			filemtime( plugin_dir_path( dirname( __FILE__ ) . '/../../js/settings-colorpicker.js' ) ),
			true
		);
	}

	/**
	 * Getting the field.
	 *
	 * @param int    $index Field index.
	 * @param string $value The value.
	 * @return string
	 */
	public function get_field( $index, $value ) {
		$name     = $this->get_field_name_in_editor( $index );
		$disabled = $this->get_disable_attribute( $index );
		return sprintf(
			'<input type="text" name="%s" value="%s" class="%s" %s />',
			esc_attr( $name ),
			esc_attr( $value ),
			esc_attr( SCF_Config::PREFIX . 'colorpicker' ),
			disabled( true, $disabled, false )
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
			<th><?php esc_html_e( 'Default', 'smart-custom-fields' ); ?></th>
			<td>
				<input type="text"
					name="<?php echo esc_attr( $this->get_field_name_in_setting( $group_key, $field_key, 'default' ) ); ?>"
					class="widefat default-option"
					value="<?php echo esc_attr( $this->get( 'default' ) ); ?>" />
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
}
