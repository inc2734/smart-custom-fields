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
	 * メタデータの識別用
	 * @var string
	 */
	protected $type = 'user';

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
		$settings = SCF::get_settings( $user->roles[0], $user->ID );
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
		$this->save( $_POST, $user_data->roles[0], $user_id );
	}

	/**
	 * メタデータを保存
	 * 
	 * @param int $user_id
	 * @param string $name
	 * @param mixed $value
	 */
	protected function add_meta( $user_id, $name, $value ) {
		do_action( SCF_Config::PREFIX . '-before-save-profile', $user_id, $name, $value );
		$is_valid = apply_filters( SCF_Config::PREFIX . '-validate-save-profile', true, $user_id, $name, $value );
		if ( $is_valid ) {
			add_user_meta( $user_id, $name, $value );
		}
		do_action( SCF_Config::PREFIX . '-after-save-profile', $user_id, $name, $value );
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

	/**
	 * display_meta_box 用のロールを返す
	 *
	 * @param WP_User $object
	 * @return string
	 */
	protected function get_type_for_display_meta_box( $object ) {
		if ( !empty( $object->roles[0] ) ) {
			return $object->roles[0];
		}
	}
}