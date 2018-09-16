<?php
/**
 * Smart_Custom_Fields_Field_Message
 * Version    : 1.0.0
 * Author     : robssanches, inc2734
 * Created    : June 2, 2018
 * Modified   : June 2, 2018
 * License    : GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
class Smart_Custom_Fields_Field_Message extends Smart_Custom_Fields_Field_Base {

	/**
	 * Set the required items
	 *
	 * @return array
	 */
	protected function init() {
		return array(
			'type'                => 'message',
			'display-name'        => __( 'Message', 'smart-custom-fields' ),
			'optgroup'            => 'basic-fields',
			'allow-multiple-data' => false,
			'layout'              => 'full-width',
		);
	}

	/**
	 * Set the non required items
	 *
	 * @return array
	 */
	protected function options() {
		return array(
			'default' => '',
			'notes'   => '',
		);
	}

	/**
	 * Getting the field
	 *
	 * @param int    $index
	 * @param string $value
	 * @return string html
	 */
	public function get_field( $index, $value ) {
		$name     = $this->get_field_name_in_editor( $index );
		$disabled = $this->get_disable_attribute( $index );
		return sprintf(
			'<div id="%s" class="widefat" %s>%s</div>',
			esc_attr( $name ),
			disabled( true, $disabled, false ),
			$value
		);
	}

	/**
	 * Displaying the option fields in custom field settings page
	 *
	 * @param int $group_key
	 * @param int $field_key
	 */
	public function display_field_options( $group_key, $field_key ) {
		$this->display_name_option( $group_key, $field_key );
		?>
		<tr>
			<th><?php esc_html_e( 'Message', 'smart-custom-fields' ); ?></th>
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
