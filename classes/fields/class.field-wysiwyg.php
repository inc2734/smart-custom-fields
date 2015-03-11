<?php
/**
 * Smart_Custom_Fields_Field_Wysiwyg
 * Version    : 1.1.0
 * Author     : Takashi Kitajima
 * Created    : October 7, 2014
 * Modified   : February 27, 2015
 * License    : GPLv2
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
class Smart_Custom_Fields_Field_Wysiwyg extends Smart_Custom_Fields_Field_Base {

	/**
	 * 必須項目の設定
	 *
	 * @return array
	 */
	protected function init() {
		add_action(
			SCF_Config::PREFIX . 'before-editor-enqueue-scripts',
			array( $this, 'editor_enqueue_scripts' )
		);
		return array(
			'type'         => 'wysiwyg',
			'display-name' => __( 'Wysiwyg', 'smart-custom-fields' ),
			'optgroup'     => 'content-fields',
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
	 * TinyMCE 読み込み後にオリジナルの JS を読み込み
	 */
	public function editor_enqueue_scripts() {
		add_action( 'after_wp_tiny_mce', array( $this, 'after_wp_tiny_mce' ) );
	}

	/**
	 * JS の読み込み
	 */
	public function after_wp_tiny_mce() {
		printf(
			'<script type="text/javascript" src="%s"></script>',
			plugins_url( SCF_Config::NAME ) . '/js/editor-wysiwyg.js'
		);
	}

	/**
	 * フィールド初期化直後に実行する処理
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
	
	/**
	 * メディアボタンを返す
	 *
	 * @param string $editor_id
	 * @return string
	 */
	protected function media_buttons( $editor_id = 'content' ) {
		$img = '<span class="wp-media-buttons-icon"></span> ';
		return sprintf( '<a href="#" class="button insert-media add_media" data-editor="%s" title="%s">%s</a>',
			esc_attr( $editor_id ),
			esc_attr__( 'Add Media' ),
			$img . __( 'Add Media' )
		);
	}
}
