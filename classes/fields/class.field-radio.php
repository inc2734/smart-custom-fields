<?php
/**
 * Smart_Custom_Fields_Field_Radio
 * Version    : 1.2.0
 * Author     : inc2734
 * Created    : October 7, 2014
 * Modified   : April 24, 2015
 * License    : GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
class Smart_Custom_Fields_Field_Radio extends Smart_Custom_Fields_Field_Base {

	/**
	 * 必須項目の設定
	 *
	 * @return array
	 */
	protected function init() {
		return array(
			'type'         => 'radio',
			'display-name' => __( 'Radio', 'smart-custom-fields' ),
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
			'choices'         => '',
			'radio_direction' => 'horizontal', // or vertical
			'default'         => '',
			'notes'           => '',
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
		$name      = $this->get_field_name_in_editor( $index );
		$disabled  = $this->get_disable_attribute( $index );
		$choices   = SCF::choices_eol_to_array( $this->get( 'choices' ) );
		$direction = $this->get( 'radio_direction' );

		$form_field = sprintf(
			'<input type="hidden" name="%s" value="" %s />',
			esc_attr( $name ),
			disabled( true, $disabled, false )
		);
		foreach ( $choices as $choice ) {
			$choice = trim( $choice );
			$form_field .= sprintf(
				'<span class="%s"><label><input type="radio" name="%s" value="%s" %s %s /> %s</label></span>',
				esc_attr( SCF_Config::PREFIX . 'item-' . $direction ),
				esc_attr( $name ),
				esc_attr( $choice ),
				checked( $value, $choice, false ),
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
			<th><?php esc_html_e( 'Display Direction', 'smart-custom-fields' ); ?></th>
			<td>
				<?php
				$directions = array(
					'horizontal' => __( 'horizontal', 'smart-custom-fields' ),
					'vertical'   => __( 'vertical'  , 'smart-custom-fields' ),
				);
				foreach ( $directions as $key => $value ) {
					printf(
						'<label><input type="radio" name="%s" value="%s" %s /> %s</label>&nbsp;&nbsp;&nbsp;',
						esc_attr( $this->get_field_name_in_setting( $group_key, $field_key, 'radio_direction' ) ),
						esc_attr( $key ),
						checked( $this->get( 'radio_direction' ), $key, false ),
						esc_html( $value )
					);
				}
				?>
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
