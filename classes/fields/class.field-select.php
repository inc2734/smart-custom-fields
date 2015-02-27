<?php
/**
 * Smart_Custom_Fields_Field_Select
 * Version    : 1.1.0
 * Author     : Takashi Kitajima
 * Created    : October 7, 2014
 * Modified   : February 27, 2015
 * License    : GPLv2
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
class Smart_Custom_Fields_Field_Select extends Smart_Custom_Fields_Field_Base {

	/**
	 * 必須項目の設定
	 *
	 * @return array
	 */
	protected function init() {
		return array(
			'type'         => 'select',
			'display-name' => __( 'Select', 'smart-custom-fields' ),
			'optgroup'     => 'select-fields',
		);
	}

	/**
	 * 設定項目の設定
	 *
	 * @return array
	 */
	protected function options() {
		return array(
			'choices' => '',
			'default' => '',
			'notes'   => '',
		);
	}

	/**
	 * 投稿画面にフィールドを表示
	 *
	 * @param int $index インデックス番号
	 * @param mixed $value 保存されている値（check のときだけ配列）
	 * @return string html
	 */
	public function get_field( $index, $value ) {
		$name = $this->get_field_name_in_editor( $index );
		$disabled = $this->get_disable_attribute( $index );
		$choices = SCF::choices_eol_to_array( $this->get( 'choices' ) );

		$form_field = '';
		foreach ( $choices as $choice ) {
			$choice = trim( $choice );
			$form_field .= sprintf( '<option value="%1$s" %2$s>%1$s</option>',
				esc_html( $choice ),
				selected( $value, $choice, false )
			);
		}
		return sprintf(
			'<select name="%s" %s>%s</select>',
			esc_attr( $name ),
			disabled( true, $disabled, false ),
			$form_field
		);
	}

	/**
	 * 設定画面にフィールドを表示（オリジナル項目）
	 *
	 * @param int $group_key
	 * @param int $field_key
	 */
	public function display_field_options( $group_key, $field_key ) {
		?>
		<tr>
			<th><?php esc_html_e( 'Choices', 'smart-custom-fields' ); ?></th>
			<td>
				<textarea
					name="<?php echo esc_attr( $this->get_field_name_in_setting( $group_key, $field_key, 'choices' ) ); ?>"
					class="widefat"
					rows="5" /><?php echo esc_textarea( "\n" . $this->get( 'choices' ) ); ?></textarea>
			</td>
		</tr>
		<tr>
			<th><?php esc_html_e( 'Default', 'smart-custom-fields' ); ?></th>
			<td>
				<input type="text"
					name="<?php echo esc_attr( $this->get_field_name_in_setting( $group_key, $field_key, 'default' ) ); ?>"
					class="widefat"
					value="<?php echo esc_attr( $this->get( 'default' ) ); ?>" />
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
