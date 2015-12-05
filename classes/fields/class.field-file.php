<?php
/**
 * Smart_Custom_Fields_Field_File
 * Version    : 1.1.0
 * Author     : inc2734
 * Created    : October 7, 2014
 * Modified   : February 27, 2015
 * License    : GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
class Smart_Custom_Fields_Field_File extends Smart_Custom_Fields_Field_Base {

	/**
	 * Set the required items
	 *
	 * @return array
	 */
	protected function init() {
		return array(
			'type'         => 'file',
			'display-name' => __( 'File', 'smart-custom-fields' ),
			'optgroup'     => 'content-fields',
		);
	}

	/**
	 * Set the non required items
	 *
	 * @return array
	 */
	protected function options() {
		return array(
			'notes' => '',
		);
	}

	/**
	 * Getting the field
	 *
	 * @param int $index
	 * @param string $value
	 * @return string html
	 */
	public function get_field( $index, $value ) {
		$name     = $this->get_field_name_in_editor( $index );
		$disabled = $this->get_disable_attribute( $index );

		$btn_remove = sprintf(
			'<span class="btn-remove-file hide">%s</span>',
			esc_html__( 'Delete', 'smart-custom-fields' )
		);

		$hide_class = 'hide';
		$image = $btn_remove;
		if ( $value ) {
			$image_src = wp_get_attachment_image_src( $value, 'thumbnail', true );
			if ( is_array( $image_src ) && isset( $image_src[0] ) ) {
				$image_src = $image_src[0];
				$image = sprintf(
					'<a href="%s" target="_blank"><img src="%s" alt="" /></a>%s',
					wp_get_attachment_url( $value ),
					esc_url( $image_src ),
					$btn_remove
				);
				$hide_class = '';
			}
		}

		return sprintf(
			'<span class="button btn-add-file">%s</span><br />
			<span class="%s %s">%s</span>
			<input type="hidden" name="%s" value="%s" %s />',
			esc_html__( 'File Select', 'smart-custom-fields' ),
			esc_attr( SCF_Config::PREFIX . 'upload-file' ),
			esc_attr( $hide_class ),
			$image,
			esc_attr( $name ),
			esc_attr( $value ),
			disabled( true, $disabled, false )
		);
	}

	/**
	 * Displaying the option fields in custom field settings page
	 *
	 * @param int $group_key
	 * @param int $field_key
	 */
	public function display_field_options( $group_key, $field_key ) {
		?>
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
