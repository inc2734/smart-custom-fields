<?php
/**
 * Smart_Custom_Fields_Field_Wysiwyg
 * Version    : 1.0.1
 * Author     : Takashi Kitajima
 * Created    : October 7, 2014
 * Modified   : October 10, 2014
 * License    : GPLv2
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
class Smart_Custom_Fields_Field_Wysiwyg extends Smart_Custom_Fields_Field_Base {

	/**
	 * init
	 * @return array ( name, label, optgroup )
	 */
	protected function init() {
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
		return array(
			'name'     => 'wysiwyg',
			'label'    => __( 'Wysiwyg', 'smart-custom-fields' ),
			'optgroup' => 'content-fields',
		);
	}

	/**
	 * admin_enqueue_scripts
	 * @param string $hook
	 */
	public function admin_enqueue_scripts( $hook ) {
		if ( in_array( $hook, array( 'post-new.php', 'post.php' ) ) ) {
			add_action( 'after_wp_tiny_mce', array( $this, 'after_wp_tiny_mce' ) );
		}
	}

	public function after_wp_tiny_mce() {
		printf(
			'<script type="text/javascript" src="%s"></script>',
			plugin_dir_url( __FILE__ ) . '../../js/editor-wysiwyg.js'
		);
	}

	/**
	 * after_loaded
	 */
	protected function after_loaded() {
		add_action( 'admin_footer', array( $this, 'admin_footer' ) );
	}
	public function admin_footer() {
		?>
		<div style="display:none;">
			<?php wp_editor( '', SCF_Config::PREFIX . 'wysiwyg-base' ); ?>
		</div>
		<?php
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
			'<div class="wp-editor-wrap">
				<div class="wp-media-buttons">%s</div>
				<div class="wp-editor-container">
					<textarea name="%s" rows="8" class="widefat smart-cf-wp-editor" %s>%s</textarea>
				</div>
			</div>',
			$this->media_buttons(),
			esc_attr( $name ),
			disabled( true, $disabled, false ),
			wp_richedit_pre( $value )
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
				<textarea
					name="<?php echo esc_attr( $this->get_field_name( $group_key, $field_key, 'default' ) ); ?>"
					class="widefat"
					rows="5"><?php echo esc_textarea( "\n" . $this->get_field_value( 'default' ) ); ?></textarea>
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
	
	protected function media_buttons( $editor_id = 'content' ) {
		$img = '<span class="wp-media-buttons-icon"></span> ';
		return sprintf( '<a href="#" class="button insert-media add_media" data-editor="%s" title="%s">%s</a>',
			esc_attr( $editor_id ),
			esc_attr__( 'Add Media' ),
			$img . __( 'Add Media' )
		);
	}
}