<?php
/**
 * Smart_Custom_Fields_Field_Textarea
 * Version    : 1.0.0
 * Author     : Takashi Kitajima
 * Created    : October 7, 2014
 * Modified   :
 * License    : GPLv2
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
class Smart_Custom_Fields_Field_Textarea extends Smart_Custom_Fields_Field_Base {

	/**
	 * @return array
	 */
	protected function init() {
		return array(
			'type'         => 'textarea',
			'display-name' => __( 'Textarea', 'smart-custom-fields' ),
			'optgroup'     => 'basic-fields',
		);
	}

	/**
	 * @return array
	 */
	protected function options() {
		return array(
			'default' => '',
			'notes'   => '',
		);
	}

	/**
	 * get_field
	 * @param int $index インデックス番号
	 * @param mixed $value 保存されている値（check のときだけ配列）
	 */
	public function get_field( $index, $value ) {
		$name = $this->get_field_name_in_editor( $index );
		$disabled = $this->get_disable_attribute( $index );
		return sprintf(
			'<textarea name="%s" rows="5" class="widefat" %s>%s</textarea>',
			esc_attr( $name ),
			disabled( true, $disabled, false ),
			$value
		);
	}

	/**
	 * display_field_options
	 * @param int $group_key
	 * @param int $field_key
	 */
	public function display_field_options( $group_key, $field_key ) {
		?>
		<tr>
			<th><?php esc_html_e( 'Default', 'smart-custom-fields' ); ?></th>
			<td>
				<textarea
					name="<?php echo esc_attr( $this->get_field_name_in_setting( $group_key, $field_key, 'default' ) ); ?>"
					class="widefat"
					rows="5"><?php echo esc_textarea( "\n" . $this->get( 'default' ) ); ?></textarea>
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