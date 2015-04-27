<?php
/**
 * Smart_Custom_Fields_Controller_Taxonomy
 * Version    : 1.0.0
 * Author     : Takashi Kitajima
 * Created    : April 26, 2015
 * Modified   : 
 * License    : GPLv2
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
class Smart_Custom_Fields_Controller_Taxonomy extends Smart_Custom_Fields_Controller_Editor {

	/**
	 * タクソノミーの名前
	 * @var string
	 */
	protected $taxonomy;

	/**
	 * __construct
	 */
	public function __construct() {
		$this->taxonomy = $_REQUEST['taxonomy'];

		add_action( $this->taxonomy . '_edit_form_fields', array( $this, 'edit_form_fields' ) );
		add_action( 'admin_enqueue_scripts'              , array( $this, 'admin_enqueue_scripts' ) );
		add_action( 'edited_terms'                       , array( $this, 'update' ), 10, 2 );
		add_action( 'delete_term'                        , array( $this, 'delete' ), 10, 4 );
	}

	/**
	 * 投稿画面用の css、js、翻訳ファイルのロード
	 * 
	 * @param string $hook
	 */
	public function admin_enqueue_scripts( $hook ) {
		parent::admin_enqueue_scripts( $hook );
		wp_enqueue_style(
			SCF_Config::PREFIX . 'taxonomy',
			plugins_url( SCF_Config::NAME ) . '/css/taxonomy.css'
		);
	}

	/**
	 * カスタムフィールドを表示
	 *
	 * @param object $term
	 */
	public function edit_form_fields( $term ) {
		$settings = SCF::get_settings( $term );
		foreach ( $settings as $Setting ) {
			$callback_args['args'] = $Setting->get_groups();
			?>
			<table class="form-table">
				<tr>
					<th scope="row"><?php echo esc_html( $Setting->get_title() ); ?></th>
					<td><?php $this->display_meta_box( $term, $callback_args ); ?></td>
				</tr>
			</table>
			<?php
		}
	}

	/**
	 * 投稿画面のカスタムフィールドからのメタデータを保存
	 * 
	 * @param int $term_id
	 * @param string $taxonomy
	 */
	public function update( $term_id, $taxonomy ) {
		if ( !current_user_can( 'manage_categories' ) ) {
			return;
		}
		if ( !isset( $_POST[SCF_Config::NAME] ) ) {
			return;
		}

		$term = get_term( $term_id, $taxonomy );
		$this->save( $_POST, $term );
	}

	public function delete( $term_id, $term_taxonomy_id, $taxonomy, $deleted_term ) {
		$Meta = new Smart_Custom_Fields_Meta( $deleted_term );
		$Meta->delete();
	}

	/**
	 * メタデータの取得
	 * 
	 * @param int $id 投稿ID or ユーザーID
	 * @return array
	 */
	protected function get_all_meta( $id ) {
		$meta_data = $this->meta_data;
		if ( empty( $meta_data ) ) {
			$Meta = new Smart_Custom_Fields_Meta( get_term( $id, $this->taxonomy ) );
			$meta_data = $Meta->get();
			if ( empty( $meta_data ) ) {
				return array();
			}
			$this->meta_data = $meta_data;
		}
		return $this->meta_data;
	}

	/**
	 * 投稿ステータスを返す（タームにステータスは無いので必ず 'auto-draft' を返すこと）
	 *
	 * @param int $user_id
	 * @return string 'auto-draft'
	 */
	protected function get_post_status( $user_id ) {
		return 'auto-draft';
	}
}