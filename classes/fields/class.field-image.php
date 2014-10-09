<?php
/**
 * Smart_Custom_Fields_Field_Image
 * Version    : 1.0.0
 * Author     : Takashi Kitajima
 * Created    : October 7, 2014
 * Modified   :
 * License    : GPLv2
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
class Smart_Custom_Fields_Field_Image extends Smart_Custom_Fields_Field_Base {

	/**
	 * init
	 * @return array ( name, label, optgroup )
	 */
	protected function init() {
		return array(
			'name'     => 'image',
			'label'    => __( 'Image', 'smart-custom-fields' ),
			'optgroup' => 'content-fields',
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

		$btn_remove = sprintf(
			'<span class="btn-remove-image hide">%s</span>',
			esc_html__( 'Delete', 'smart-custom-fields' )
		);

		$hide_class = 'hide';
		$image = $btn_remove;
		if ( $value ) {
			$image_src = wp_get_attachment_image_src( $value, 'full' );
			if ( is_array( $image_src ) && isset( $image_src[0] ) ) {
				$image_src = $image_src[0];
				$image = sprintf(
					'<img src="%s" alt="" />%s',
					esc_url( $image_src ),
					$btn_remove
				);
				$hide_class = '';
			}
		}

		return sprintf(
			'<span class="button btn-add-image">%s</span><br />
			<span class="%s %s">%s</span>
			<input type="hidden" name="%s" value="%s" %s />',
			esc_html__( 'Image Select', 'smart-custom-fields' ),
			esc_attr( SCF_Config::PREFIX . 'upload-image' ),
			esc_attr( $hide_class ),
			$image,
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