<?php
/**
 * @package smart-custom-fields
 * @author inc2734
 * @license GPL-2.0+
 */

/**
 * Smart_Custom_Fields_Field_Wysiwyg class.
 */
class Smart_Custom_Fields_Field_Wysiwyg extends Smart_Custom_Fields_Field_Base {

	/**
	 * Set the required items.
	 *
	 * @return array
	 */
	protected function init() {
		add_action(
			SCF_Config::PREFIX . 'before-editor-enqueue-scripts',
			array( $this, 'editor_enqueue_scripts' )
		);
		add_filter( 'smart-cf-validate-get-value', array( $this, 'validate_get_value' ), 10, 2 );
		return array(
			'type'         => 'wysiwyg',
			'display-name' => __( 'Wysiwyg', 'smart-custom-fields' ),
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
			'default'     => '',
			'instruction' => '',
			'notes'       => '',
		);
	}

	/**
	 * Loading js after loading TinyMCE in editor page.
	 */
	public function editor_enqueue_scripts() {
		add_action( 'after_wp_tiny_mce', array( $this, 'after_wp_tiny_mce' ) );
	}

	/**
	 * Add script for wysiwyg.
	 */
	public function after_wp_tiny_mce() {
		printf(
			'<script type="text/javascript" src="%s"></script>',
			plugins_url( SCF_Config::NAME ) . '/js/editor-wysiwyg.js'
		);
	}

	/**
	 * Processing to be executed immediately after the field initialization.
	 * If not exec this, taxonomy and profile wysiwyg has js error.
	 */
	protected function after_loaded() {
		add_action( 'admin_footer', array( $this, 'admin_footer' ) );
	}

	/**
	 * Add dummy editor.
	 */
	public function admin_footer() {
		?>
		<div style="display:none;">
			<?php wp_editor( '', SCF_Config::PREFIX . 'wysiwyg-base' ); ?>
		</div>
		<?php
	}

	/**
	 * Getting the field.
	 *
	 * @param int    $index Field index.
	 * @param string $value The value.
	 * @return string
	 */
	public function get_field( $index, $value ) {
		$name       = $this->get_field_name_in_editor( $index );
		$wysiwyg_id = str_replace( array( '[', ']', '-' ), '_', $name );
		$disabled   = $this->get_disable_attribute( $index );
		if ( function_exists( 'format_for_editor' ) ) {
			$value = format_for_editor( $value );
		} else {
			$value = wp_richedit_pre( $value );
		}
		return sprintf(
			'<div class="wp-core-ui wp-editor-wrap tmce-active">
				<div class="wp-editor-tools hide-if-no-js">
					<div class="wp-media-buttons">%1$s</div>
					<div class="wp-editor-tabs">
						<button type="button" id="%2$s-tmce" class="smart-cf-switch-editor wp-switch-editor switch-tmce" data-wp-editor-id="%2$s">%6$s</button>
						<button type="button" id="%2$s-html" class="smart-cf-switch-editor wp-switch-editor switch-html" data-wp-editor-id="%2$s">%7$s</button>
					</div>
				</div>
				<div class="wp-editor-container">
					<div id="qt_%2$s_toolbar" class="quicktags-toolbar"></div>
					<textarea name="%3$s" rows="8" class="widefat wp-editor-area smart-cf-wp-editor" %4$s>%5$s</textarea>
				</div>
			</div>',
			$this->media_buttons( $wysiwyg_id ),
			esc_attr( $wysiwyg_id ),
			esc_attr( $name ),
			disabled( true, $disabled, false ),
			$value,
			esc_html__( 'Visual', 'smart-custom-fields' ),
			esc_html__( 'Text', 'smart-custom-fields' )
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
			<th><?php esc_html_e( 'Default', 'smart-custom-fields' ); ?></th>
			<td>
				<textarea
					name="<?php echo esc_attr( $this->get_field_name_in_setting( $group_key, $field_key, 'default' ) ); ?>"
					class="widefat"
					rows="5"><?php echo esc_textarea( "\n" . $this->get( 'default' ) ); ?></textarea>
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

	/**
	 * Return the media button.
	 *
	 * @param string $editor_id Editor id.
	 * @return string
	 */
	protected function media_buttons( $editor_id = 'content' ) {
		$img = '<span class="wp-media-buttons-icon"></span> ';
		return sprintf(
			'<a href="#" class="button insert-media add_media" data-editor="%s" title="%s">%s</a>',
			esc_attr( $editor_id ),
			// phpcs:ignore WordPress.WP.I18n.MissingArgDomain
			esc_attr__( 'Add Media' ),
			// phpcs:ignore WordPress.WP.I18n.MissingArgDomain
			$img . __( 'Add Media' )
		);
	}

	/**
	 * Validating when displaying meta data.
	 *
	 * @param mixed  $value      The value.
	 * @param string $field_type Field type.
	 * @return string|array
	 */
	public function validate_get_value( $value, $field_type ) {
		if ( $field_type === $this->get_attribute( 'type' ) ) {
			if ( is_array( $value ) ) {
				$validated_value = array();
				foreach ( $value as $k => $v ) {
					$validated_value[ $k ] = $this->add_the_content_filter( $v );
				}
				$value = $validated_value;
			} else {
				$value = $this->add_the_content_filter( $value );
			}
		}
		return $value;
	}

	/**
	 * Hooking functions that is hooked to the_content.
	 *
	 * @param string $value The value.
	 * @return string
	 */
	protected function add_the_content_filter( $value ) {
		if ( has_filter( 'the_content', 'wptexturize' ) ) {
			$value = wptexturize( $value );
		}
		if ( has_filter( 'the_content', 'convert_smilies' ) ) {
			$value = convert_smilies( $value );
		}
		if ( has_filter( 'the_content', 'convert_chars' ) ) {
			$value = convert_chars( $value );
		}
		if ( has_filter( 'the_content', 'wpautop' ) ) {
			$value = wpautop( $value );
		}
		if ( has_filter( 'the_content', 'shortcode_unautop' ) ) {
			$value = shortcode_unautop( $value );
		}
		if ( has_filter( 'the_content', 'prepend_attachment' ) ) {
			$value = prepend_attachment( $value );
		}
		return $value;
	}
}
