<?php
/**
 * Smart_Custom_Fields_Controller_Profile
 * Version    : 1.0.0
 * Author     : Takashi Kitajima
 * Created    : March 16, 2015
 * Modified   : 
 * License    : GPLv2
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
class Smart_Custom_Fields_Controller_Profile extends Smart_Custom_Fields_Controller_Editor {

	/**
	 * __construct
	 */
	public function __construct() {
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
		add_action( 'show_user_profile', array( $this, 'user_profile' ) );
		add_action( 'edit_user_profile', array( $this, 'user_profile' ) );
		add_action( 'personal_options_update', array( $this, 'update' ) );
		add_action( 'edit_user_profile_update', array( $this, 'update' ) );
	}

	/**
	 * 投稿画面用の css、js、翻訳ファイルのロード
	 * 
	 * @param string $hook
	 */
	public function admin_enqueue_scripts( $hook ) {
		parent::admin_enqueue_scripts( $hook );
		wp_enqueue_style(
			SCF_Config::PREFIX . 'profile',
			plugins_url( SCF_Config::NAME ) . '/css/profile.css'
		);
	}

	/**
	 * user_profile
	 */
	public function user_profile( $user ) {
		printf( '<h3>%s</h3>', esc_html__( 'Custom Fields', 'smart-custom-fields' ) );
		$settings = SCF::get_settings( $user );
		foreach ( $settings as $Setting ) {
			$callback_args['args'] = $Setting->get_groups();
			?>
			<table class="form-table">
				<tr>
					<th scope="row"><?php echo esc_html( $Setting->get_title() ); ?></th>
					<td><?php $this->display_meta_box( $user, $callback_args ); ?></td>
				</tr>
			</table>
			<?php
		}
	}

	/**
	 * 投稿画面のカスタムフィールドからのメタデータを保存
	 * 
	 * @param int $user_id
	 */
	public function update( $user_id ) {
		if ( !current_user_can( 'edit_user', $user_id ) ) {
			return;
		}
		if ( !isset( $_POST[SCF_Config::NAME] ) ) {
			return;
		}

		$user_data = get_userdata( $user_id );
		$this->save( $_POST, get_userdata( $user_id ) );
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
			$meta_data = get_user_meta( $id );
			if ( empty( $meta_data ) ) {
				return array();
			}
			$this->meta_data = $meta_data;
		}
		return $this->meta_data;
	}

	/**
	 * 投稿ステータスを返す（ユーザーにステータスは無いので必ず 'auto-draft' を返すこと）
	 *
	 * @param int $user_id
	 * @return string 'auto-draft'
	 */
	protected function get_post_status( $user_id ) {
		return 'auto-draft';
	}
}