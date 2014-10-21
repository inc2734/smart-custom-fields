<?php
/**
 * Smart_Custom_Fields_Field_Relation
 * Version    : 1.0.2
 * Author     : Takashi Kitajima
 * Created    : October 7, 2014
 * Modified   : October 21, 2014
 * License    : GPLv2
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
class Smart_Custom_Fields_Field_Relation extends Smart_Custom_Fields_Field_Base {

	/**
	 * init
	 * @return array ( name, label, optgroup, allow-multiple-data )
	 */
	protected function init() {
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
		add_action( 'wp_ajax_smart-cf-relational-posts-search', array( $this, 'relational_posts_search' ) );
		return array(
			'name'     => 'relation',
			'label'    => __( 'Relation', 'smart-custom-fields' ),
			'optgroup' => 'other-fields',
			'allow-multiple-data' => true,
		);
	}

	/**
	 * admin_enqueue_scripts
	 * @param string $hook
	 */
	public function admin_enqueue_scripts( $hook ) {
		if ( in_array( $hook, array( 'post-new.php', 'post.php' ) ) ) {
			wp_enqueue_script(
				SCF_Config::PREFIX . 'editor-relation',
				plugin_dir_url( __FILE__ ) . '../../js/editor-relation.js',
				array( 'jquery' ),
				null,
				true
			);
			wp_localize_script( SCF_Config::PREFIX . 'editor-relation', 'smart_cf_relation', array(
				'endpoint' => admin_url( 'admin-ajax.php' ),
				'action'   => SCF_Config::PREFIX . 'relational-posts-search',
				'nonce'    => wp_create_nonce( SCF_Config::NAME . '-relation' )
			) );
		}
	}

	/**
	 * relational_posts_search
	 */
	public function relational_posts_search() {
		check_ajax_referer( SCF_Config::NAME . '-relation', 'nonce' );
		$_posts = array();
		if ( isset( $_POST['post_types'], $_POST['click_count' ] ) ) {
			$post_type = explode( ',', $_POST['post_types'] );
			$posts_per_page = get_option( 'posts_per_page' );
			$offset = $_POST['click_count'] * $posts_per_page;
			$_posts = get_posts( array(
				'post_type'      => $post_type,
				'offset'         => $offset,
				'order'          => 'ASC',
				'orderby'        => 'ID',
				'posts_per_page' => $posts_per_page,
			) );
		}
		header( 'Content-Type: application/json; charset=utf-8' );
		echo json_encode( $_posts );
		die();
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
		$post_type = $this->get( 'post-type', $field );
		if ( !$post_type ) {
			$post_type = array( 'post' );
		}
		$posts_per_page = get_option( 'posts_per_page' );

		// 選択肢
		$choices_posts  = get_posts( array(
			'post_type' => $post_type,
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
		if ( !empty( $value ) && is_array( $value ) ) {
			foreach ( $value as $post_id ) {
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
				disabled( true, $disabled, false )
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
			implode( ',', $post_type ),
			esc_html__( 'Load more', 'smart-custom-fields' ),
			esc_attr( $name ),
			disabled( true, $disabled, false ),
			implode( '', $hidden ),
			SCF_Config::PREFIX . 'relation-right',
			implode( '', $selected_li )
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
			<th><?php esc_html_e( 'Post Types', 'smart-custom-fields' ); ?></th>
			<td>
				<?php
				$post_types = get_post_types( array(
					'show_ui'  => true,
				), 'objects' );
				unset( $post_types['attachment'] );
				unset( $post_types[SCF_Config::NAME] );
				?>
				<?php foreach ( $post_types as $post_type => $post_type_object ) : ?>
				<?php
				$save_post_type = $this->get( 'post-type', $this->field );
				$checked = ( is_array( $save_post_type ) && in_array( $post_type, $save_post_type ) ) ? 'checked="checked"' : ''; ?>
				<input type="checkbox"
					name="<?php echo esc_attr( $this->get_field_name( $group_key, $field_key, 'post-type' ) ); ?>[]"
					value="<?php echo esc_attr( $post_type ); ?>"
					 <?php echo $checked; ?> /><?php echo esc_html( $post_type_object->labels->singular_name ); ?>
				<?php endforeach; ?>
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