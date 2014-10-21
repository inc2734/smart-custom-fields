<?php
/**
 * Smart_Custom_Fields_Field_Colorpicker
 * Version    : 1.0.0
 * Author     : Takashi Kitajima
 * Created    : October 21, 2014
 * Modified   :
 * License    : GPLv2
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
class Smart_Custom_Fields_Field_Colorpicker extends Smart_Custom_Fields_Field_Base {

	/**
	 * init
	 * @return array ( name, label, optgroup )
	 */
	protected function init() {
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
		return array(
			'name'     => 'colorpicker',
			'label'    => __( 'Color picker', 'smart-custom-fields' ),
			'optgroup' => 'other-fields',
		);
	}

	/**
	 * admin_enqueue_scripts
	 * @param string $hook
	 */
	public function admin_enqueue_scripts( $hook ) {
		if ( in_array( $hook, array( 'post-new.php', 'post.php' ) ) ) {
			wp_enqueue_style( 'wp-color-picker' );
			wp_enqueue_script(
				SCF_Config::PREFIX . 'colorpicker',
				plugins_url( '../../js/editor-colorpicker.js', __FILE__ ),
				array( 'jquery', 'wp-color-picker' ),
				false,
				true
			);
		}
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
			'<input type="text" name="%s" value="%s" class="%s" %s />',
			esc_attr( $name ),
			esc_attr( $value ),
			esc_attr( SCF_Config::PREFIX . 'colorpicker' ),
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