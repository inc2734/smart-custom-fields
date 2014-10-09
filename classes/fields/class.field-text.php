<?php
/**
 * Smart_Custom_Fields_Field_Text
 * Version    : 1.0.0
 * Author     : Takashi Kitajima
 * Created    : October 7, 2014
 * Modified   :
 * License    : GPLv2
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
class Smart_Custom_Fields_Field_Text extends Smart_Custom_Fields_Field_Base {

	/**
	 * init
	 * @return array ( name, label, optgroup )
	 */
	protected function init() {
		return array(
			'name'     => 'text',
			'label'    => __( 'Text', 'smart-custom-fields' ),
			'optgroup' => 'basic-fields',
		);
	}

	/**
	 * get_field
	 * @param array $field フィールドの情報
	 * @param int $index インデックス番号
	 * @param mixed $value 保存されている値（check のときだけ配列）
	 */
	public function get_field( $field, $index, $value ) {
		$name = $this->get_name_attribute( $field['name'], $index );
		$disabled = $this->get_disable_attribute( $index );
		return sprintf(
			'<input type="text" name="%s" value="%s" class="widefat" %s />',
			esc_attr( $name ),
			esc_attr( $value ),
			disabled( true, $disabled, false )
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
				<input type="text"
					name="<?php echo esc_attr( $this->get_field_name( $group_key, $field_key, 'default' ) ); ?>"
					class="widefat"
					value="<?php echo esc_attr( $this->get_field_value( 'default' ) ); ?>" />
			</td>
		</tr>
		<tr>
			<th><?php esc_html_e( 'Notes', 'smart-custom-fields' ); ?></th>
			<td>
				<input type="text"
					name="<?php echo esc_attr( $this->get_field_name( $group_key, $field_key, 'notes' ) ); ?>"
					class="widefat"
					value="<?php echo esc_attr( $this->get_field_value( 'notes' ) ); ?>"
				/>
			</td>
		</tr>
		<?php
	}
}