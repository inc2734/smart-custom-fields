<?php
/**
 * Smart_Custom_Fields_Field_Check
 * Version    : 1.1.0
 * Author     : Takashi Kitajima
 * Created    : October 7, 2014
 * Modified   : February 27, 2015
 * License    : GPLv2
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
class Smart_Custom_Fields_Field_Check extends Smart_Custom_Fields_Field_Base {

	/**
	 * 必須項目の設定
	 *
	 * @return array
	 */
	protected function init() {
		return array(
			'type'                => 'check',
			'display-name'        => __( 'Check', 'smart-custom-fields' ),
			'optgroup'            => 'select-fields',
			'allow-multiple-data' => true,
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
		$name     = $this->get_field_name_in_editor( $index );
		$disabled = $this->get_disable_attribute( $index );
		$choices  = SCF::choices_eol_to_array( $this->get( 'choices' ) );

		$form_field = sprintf(
			'<input type="hidden" name="%s" value="" %s />',
			esc_attr( $name ),
			disabled( true, $disabled, false )
		);
		foreach ( $choices as $choice ) {
			$choice = trim( $choice );
			$checked = ( is_array( $value ) && in_array( $choice, $value ) ) ? 'checked="checked"' : '' ;
			$form_field .= sprintf(
				'<label><input type="checkbox" name="%s" value="%s" %s %s /> %s</label>',
				esc_attr( $name . '[]' ),
				esc_attr( $choice ),
				$checked,
				disabled( true, $disabled, false ),
				esc_html( $choice )
			);
		}
		return $form_field;
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
				<textarea
					name="<?php echo esc_attr( $this->get_field_name_in_setting( $group_key, $field_key, 'default' ) ); ?>"
					class="widefat"
					rows="5" /><?php echo esc_textarea( "\n" . $this->get( 'default' ) ); ?></textarea>
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
