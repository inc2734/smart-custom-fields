<?php
/**
 * @package smart-custom-fields
 * @author inc2734
 * @license GPL-2.0+
 */

/**
 * Smart_Custom_Fields_Field_Check class.
 */
class Smart_Custom_Fields_Field_Check extends Smart_Custom_Fields_Field_Base {

	/**
	 * Set the required items.
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
	 * Set the non required items.
	 *
	 * @return array
	 */
	protected function options() {
		return array(
			'choices'         => '',
			'check_direction' => 'horizontal', // or vertical
			'default'         => '',
			'instruction'     => '',
			'notes'           => '',
		);
	}

	/**
	 * Getting the field.
	 *
	 * @param int   $index Field index.
	 * @param array $value The value.
	 * @return string
	 */
	public function get_field( $index, $value ) {
		$name      = $this->get_field_name_in_editor( $index );
		$disabled  = $this->get_disable_attribute( $index );
		$choices   = SCF::choices_eol_to_array( $this->get( 'choices' ) );
		$direction = $this->get( 'check_direction' );

		$form_field = sprintf(
			'<input type="hidden" name="%s" value="" %s />',
			esc_attr( $name ),
			disabled( true, $disabled, false )
		);
		foreach ( $choices as $key => $choice ) {
			$choice = trim( $choice );
			if ( ! SCF::is_assoc( $choices ) ) {
				$key = $choice;
			}
			$form_field .= sprintf(
				'<span class="%1$s"><label><input type="checkbox" name="%2$s" value="%3$s" %4$s %5$s /> %6$s</label></span>',
				esc_attr( SCF_Config::PREFIX . 'item-' . $direction ),
				esc_attr( $name . '[]' ),
				esc_attr( $key ),
				checked( true, is_array( $value ) && in_array( $key, $value, true ), false ),
				disabled( true, $disabled, false ),
				esc_html( $choice )
			);
		}
		return $form_field;
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
			<th><?php esc_html_e( 'Choices', 'smart-custom-fields' ); ?></th>
			<td>
				<textarea
					name="<?php echo esc_attr( $this->get_field_name_in_setting( $group_key, $field_key, 'choices' ) ); ?>"
					class="widefat"
					rows="5" /><?php echo esc_textarea( "\n" . $this->get( 'choices' ) ); ?></textarea>
				<?php esc_html_e( 'If you want to separate the key and the value, enter as follows: key => value', 'smart-custom-fields' ); ?>
			</td>
		</tr>
		<tr>
			<th><?php esc_html_e( 'Display Direction', 'smart-custom-fields' ); ?></th>
			<td>
				<?php
				$directions = array(
					'horizontal' => __( 'horizontal', 'smart-custom-fields' ),
					'vertical'   => __( 'vertical', 'smart-custom-fields' ),
				);
				foreach ( $directions as $key => $value ) {
					printf(
						'<label><input type="radio" name="%s" value="%s" %s /> %s</label>&nbsp;&nbsp;&nbsp;',
						esc_attr( $this->get_field_name_in_setting( $group_key, $field_key, 'check_direction' ) ),
						esc_attr( $key ),
						checked( $this->get( 'check_direction' ), $key, false ),
						esc_html( $value )
					);
				}
				?>
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
