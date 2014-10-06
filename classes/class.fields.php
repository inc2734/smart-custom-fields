<?php
/**
 * Smart_Custom_Fields_Fields
 * Version    : 1.0.0
 * Author     : Takashi Kitajima
 * Created    : September 23, 2014
 * Modified   :
 * License    : GPLv2
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
class Smart_Custom_Fields_Fields {
	/**
	 * text
	 * @param string $name name属性
	 * @param array $options
	 * @return string html
	 */
	public function text( $name, array $options = array() ) {
		$options = shortcode_atts( array(
			'value'    => '',
			'disabled' => false,
		), $options );
		return sprintf(
			'<input type="text" name="%s" value="%s" class="widefat" %s />',
			esc_attr( $name ),
			esc_attr( $options['value'] ),
			disabled( true, $options['disabled'], false )
		);
	}

	/**
	 * checkbox
	 * @param string $name name属性
	 * @param array $choices 選択肢
	 * @param array $options
	 * @return string html
	 */
	public function checkbox( $name, array $choices, array $options = array() ) {
		$options = shortcode_atts( array(
			'value'    => '',
			'disabled' => false,
		), $options );
		$form_field = sprintf(
			'<input type="hidden" name="%s" value="" %s />',
			esc_attr( $name ),
			disabled( true, $options['disabled'], false )
		);
		foreach ( $choices as $choice ) {
			$choice = trim( $choice );
			$checked = ( is_array( $options['value'] ) && in_array( $choice, $options['value'] ) ) ? 'checked="checked"' : '' ;
			$form_field .= sprintf(
				'<label><input type="checkbox" name="%s" value="%s" %s %s /> %s</label>',
				esc_attr( $name . '[]' ),
				esc_attr( $choice ),
				$checked,
				disabled( true, $options['disabled'], false ),
				esc_html( $choice )
			);
		}
		return $form_field;
	}

	/**
	 * radio
	 * @param string $name name属性
	 * @param array $choices 選択肢
	 * @param array $options
	 * @return string html
	 */
	public function radio( $name, array $choices, array $options = array() ) {
		$options = shortcode_atts( array(
			'value'    => '',
			'disabled' => false,
		), $options );
		$form_field = sprintf(
			'<input type="hidden" name="%s" value="" %s />',
			esc_attr( $name ),
			disabled( true, $options['disabled'], false )
		);
		foreach ( $choices as $choice ) {
			$choice = trim( $choice );
			$form_field .= sprintf(
				'<label><input type="radio" name="%s" value="%s" %s /> %s</label>',
				esc_attr( $name ),
				esc_attr( $choice ),
				checked( $options['value'], $choice, false ),
				esc_html( $choice )
			);
		}
		return $form_field;
	}

	/**
	 * select
	 * @param string $name name属性
	 * @param array $choices 選択肢
	 * @param array $options
	 * @return string html
	 */
	public function select( $name, array $choices, array $options = array() ) {
		$options = shortcode_atts( array(
			'value'    => '',
			'disabled' => false,
		), $options );
		$form_field = '';
		foreach ( $choices as $choice ) {
			$choice = trim( $choice );
			$form_field .= sprintf( '<option value="%1$s" %2$s>%1$s</option>',
				esc_html( $choice ),
				selected( $options['value'], $choice, false )
			);
		}
		$form_field = sprintf(
			'<select name="%s" %s>%s</select>',
			esc_attr( $name ),
			disabled( true, $options['disabled'], false ),
			$form_field
		);
		return $form_field;
	}

	/**
	 * textarea
	 * @param string $name name属性
	 * @param array $options
	 * @return string html
	 */
	public function textarea( $name, array $options = array() ) {
		$options = shortcode_atts( array(
			'value'    => '',
			'disabled' => false,
		), $options );
		return sprintf(
			'<textarea name="%s" rows="5" class="widefat" %s>%s</textarea>',
			esc_attr( $name ),
			disabled( true, $options['disabled'], false ),
			$options['value']
		);
	}

	/**
	 * wysiwyg
	 * @param string $name name属性
	 * @param array $options
	 * @return string html
	 */
	public function wysiwyg( $name, array $options = array() ) {
		$options = shortcode_atts( array(
			'value'    => '',
			'disabled' => false,
		), $options );
		return sprintf(
			'<div class="wp-editor-wrap">
				<div class="wp-media-buttons">%s</div>
				<div class="wp-editor-container">
					<textarea name="%s" rows="8" class="widefat smart-cf-wp-editor" %s>%s</textarea>
				</div>
			</div>',
			$this->media_buttons(),
			esc_attr( $name ),
			disabled( true, $options['disabled'], false ),
			wp_richedit_pre( $options['value'] )
		);
	}
	protected function media_buttons( $editor_id = 'content' ) {
		$img = '<span class="wp-media-buttons-icon"></span> ';
		return sprintf( '<a href="#" class="button insert-media add_media" data-editor="%s" title="%s">%s</a>',
			esc_attr( $editor_id ),
			esc_attr__( 'Add Media' ),
			$img . __( 'Add Media' )
		);
	}

	/**
	 * image
	 * @param string $name name属性
	 * @param array $options
	 * @return string html
	 */
	public function image( $name, array $options = array() ) {
		$options = shortcode_atts( array(
			'value'    => '',
			'disabled' => false,
		), $options );

		$btn_remove = sprintf(
			'<span class="btn-remove-image hide">%s</span>',
			esc_html__( 'Delete', 'smart-custom-fields' )
		);

		$hide_class = 'hide';
		$image = $btn_remove;
		if ( $options['value'] ) {
			$image_src = wp_get_attachment_image_src( $options['value'], 'full' );
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
			esc_attr( $options['value'] ),
			disabled( true, $options['disabled'], false )
		);
	}

	/**
	 * file
	 * @param string $name name属性
	 * @param array $options
	 * @return string html
	 */
	public function file( $name, array $options = array() ) {
		$options = shortcode_atts( array(
			'value'    => '',
			'disabled' => false,
		), $options );

		$btn_remove = sprintf(
			'<span class="btn-remove-file hide">%s</span>',
			esc_html__( 'Delete', 'smart-custom-fields' )
		);

		$hide_class = 'hide';
		$image = $btn_remove;
		if ( $options['value'] ) {
			$image_src = wp_get_attachment_image_src( $options['value'], 'thumbnail', true );
			if ( is_array( $image_src ) && isset( $image_src[0] ) ) {
				$image_src = $image_src[0];
				$image = sprintf(
					'<a href="%s" target="_blank"><img src="%s" alt="" /></a>%s',
					wp_get_attachment_url( $options['value'] ),
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
			esc_attr( $options['value'] ),
			disabled( true, $options['disabled'], false )
		);
	}

	/**
	 * relation
	 * @param string $name name属性
	 * @param array $options
	 * @return string html
	 */
	public function relation( $name, array $options = array() ) {
		$options = shortcode_atts( array(
			'value'     => array(),
			'post_type' => array( 'post' ),
			'disabled'  => false,
		), $options );
		$posts_per_page = get_option( 'posts_per_page' );

		// 選択肢
		$choices_posts  = get_posts( array(
			'post_type' => $options['post_type'],
			'order'     => 'ASC',
			'orderby'   => 'ID',
			'posts_per_page' => $posts_per_page,
		) );
		$choices_li = array();
		foreach ( $choices_posts as $_post ) {
			$post_title = get_the_title( $_post->ID );
			if ( empty( $post_title ) ) {
				$post_title = '&nbsp;';
			}
			$choices_li[] = sprintf( '<li data-id="%d">%s</li>', $_post->ID, $post_title );
		}

		// 選択済
		$selected_posts = array();
		if ( !empty( $options['value'] ) ) {
			foreach ( $options['value'] as $post_id ) {
				if ( get_post_status( $post_id ) !== 'publish' )
					continue;
				$post_title = get_the_title( $post_id );
				if ( empty( $post_title ) ) {
					$post_title = '&nbsp;';
				}
				$selected_posts[$post_id] = $post_title;
			}
		}
		$selected_li = array();
		$hidden = array();
		foreach ( $selected_posts as $post_id => $post_title ) {
			$selected_li[] = sprintf(
				'<li data-id="%d"><span class="%s"></span>%s<span class="relation-remove">-</li></li>',
				$post_id,
				esc_attr( SCF_Config::PREFIX . 'icon-handle' ),
				$post_title
			);
			$hidden[] = sprintf(
				'<input type="hidden" name="%s" value="%d" %s />',
				esc_attr( $name . '[]' ),
				$post_id,
				disabled( true, $options['disabled'], false )
			);
		}

		$hide_class = '';
		if ( count( $choices_li ) < $posts_per_page ) {
			$hide_class = 'hide';
		}

		return sprintf(
			'<div class="%s">
				<div class="%s">
					<ul>%s</ul>
					<p class="load-relation-posts %s" data-post-types="%s">%s</p>
					<input type="hidden" name="%s" %s />
					%s
				</div>
			</div>
			<div class="%s"><ul>%s</ul></div>',
			SCF_Config::PREFIX . 'relation-left',
			SCF_Config::PREFIX . 'relation-children-select',
			implode( '', $choices_li ),
			$hide_class,
			implode( ',', $options['post_type'] ),
			esc_html__( 'load more', 'smart-custom-fields' ),
			esc_attr( $name ),
			disabled( true, $options['disabled'], false ),
			implode( '', $hidden ),
			SCF_Config::PREFIX . 'relation-right',
			implode( '', $selected_li )
		);
	}

	public function get_choices( $choices ) {
		return explode( "\n", $choices );
	}
}
