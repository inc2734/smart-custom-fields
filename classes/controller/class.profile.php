<?php
/**
 * Smart_Custom_Fields_Controller_Profile
 * Version    : 1.0.1
 * Author     : inc2734
 * Created    : March 16, 2015
 * Modified   : April 26, 2015
 * License    : GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
class Smart_Custom_Fields_Controller_Profile extends Smart_Custom_Fields_Controller_Base {

	/**
	 * __construct
	 */
	public function __construct() {
		parent::__construct();
		add_action( 'show_user_profile', array( $this, 'user_profile' ) );
		add_action( 'edit_user_profile', array( $this, 'user_profile' ) );
		add_action( 'personal_options_update', array( $this, 'update' ) );
		add_action( 'edit_user_profile_update', array( $this, 'update' ) );
	}

	/**
	 * Loading resources for profile edit page
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
	 * Displaying custom fields
	 *
	 * @param WP_User $user
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
	 * Saving meta data from custom fields in profile edit page.
	 *
	 * @param int $user_id
	 */
	public function update( $user_id ) {
		if ( ! current_user_can( 'edit_user', $user_id ) ) {
			return;
		}
		if ( ! isset( $_POST[ SCF_Config::NAME ] ) ) {
			return;
		}

		$this->save( $_POST, get_userdata( $user_id ) );
	}
}
