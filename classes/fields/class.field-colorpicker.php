<?php
/**
 * Smart_Custom_Fields_Field_Colorpicker
 * Version    : 1.1.0
 * Author     : Takashi Kitajima
 * Created    : October 21, 2014
 * Modified   : February 27, 2015
 * License    : GPLv2
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
class Smart_Custom_Fields_Field_Colorpicker extends Smart_Custom_Fields_Field_Base {

	/**
	 * 必須項目の設定
	 *
	 * @return array
	 */
	protected function init() {
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
		return array(
			'type'         => 'colorpicker',
			'display-name' => __( 'Color picker', 'smart-custom-fields' ),
			'optgroup'     => 'other-fields',
		);
	}

	/**
	 * 設定項目の設定
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
	 * CSS、JSの読み込み
	 *
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
	 * 投稿画面にフィールドを表示
	 *
	 * @param int $index インデックス番号
	 * @param mixed $value 保存されている値（check のときだけ配列）
	 * @return string html
	 */
	public function get_field( $index, $value ) {
		$name     = $this->get_field_name_in_editor( $index );
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
	 * 設定画面にフィールドを表示（オリジナル項目）
	 *
	 * @param int $group_key
	 * @param int $field_key
	 */
	public function display_field_options( $group_key, $field_key ) {
		?>
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