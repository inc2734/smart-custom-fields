<?php
/**
 * @package smart-custom-fields
 * @author inc2734
 * @license GPL-2.0+
 */

/**
 * Smart_Custom_Fields_Field_Image class.
 */
class Smart_Custom_Fields_Field_Image extends Smart_Custom_Fields_Field_Base {

	/**
	 * Set the required items.
	 *
	 * @return array
	 */
	protected function init() {
		return array(
			'type'         => 'image',
			'display-name' => __( 'Image', 'smart-custom-fields' ),
			'optgroup'     => 'content-fields',
		);
	}

	/**
	 * Set the non required items.
	 *
	 * @return array
	 */
	protected function options() {
		return array(
			'instruction' => '',
			'notes'       => '',
			'size'        => 'full',
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

		$btn_remove = sprintf(
			'<span class="btn-remove-image hide">%s</span>',
			esc_html__( 'Delete', 'smart-custom-fields' )
		);

		$hide_class = 'hide';
		$image      = $btn_remove;
		if ( $value ) {
			// Usually, $value is attachment ID.
			// If a customized, for example, $value is not an ID,
			// Regarded the $value is file URL.
			if ( preg_match( '/^\d+$/', $value ) ) {
				$image_src = wp_get_attachment_image_src( $value, $this->get( 'size' ) );
				$image_alt = get_the_title( $value );
				if ( is_array( $image_src ) && isset( $image_src[0] ) ) {
					$image_src = $image_src[0];
				}
			} else {
				$image_url  = $value;
				$path       = str_replace( home_url(), '', $value );
				$image_path = ABSPATH . untrailingslashit( $path );
				if ( file_exists( $image_path ) ) {
					$wp_check_filetype = wp_check_filetype( $image_path );
					if ( ! empty( $wp_check_filetype['type'] ) ) {
						$image_src = $image_url;
					}
				}
			}

			if ( $image_src && ! is_array( $image_src ) ) {
				$image      = sprintf(
					'<img src="%s" alt="%s" />%s',
					esc_url( $image_src ),
					$image_alt,
					$btn_remove
				);
				$hide_class = '';
			}
		}

		return sprintf(
			'<span class="button btn-add-image">%s</span><br />
			<span class="%s %s" data-size="%s">%s</span>
			<input type="hidden" name="%s" value="%s" %s />',
			esc_html__( 'Image Select', 'smart-custom-fields' ),
			esc_attr( SCF_Config::PREFIX . 'upload-image' ),
			esc_attr( $hide_class ),
			esc_attr( $this->get( 'size' ) ),
			$image,
			esc_attr( $name ),
			esc_attr( $value ),
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
		<tr>
			<th><?php esc_html_e( 'Preview Size', 'smart-custom-fields' ); ?></th>
			<td>
				<select name="<?php echo esc_attr( $this->get_field_name_in_setting( $group_key, $field_key, 'size' ) ); ?>">
					<option value="full" <?php selected( $this->get( 'size' ), 'full' ); ?> >full</option>
					<?php foreach ( get_intermediate_image_sizes() as $size ) : ?>
						<option value="<?php echo esc_attr( $size ); ?>" <?php selected( $this->get( 'size' ), $size ); ?>><?php echo esc_html( $size ); ?></option>
					<?php endforeach; ?>
				</select>
			</td>
		</tr>
		<?php
	}
}
