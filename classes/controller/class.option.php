<?php
/**
 * Smart_Custom_Fields_Controller_Option
 * Version    : 1.0.0
 * Author     : inc2734
 * Created    : May 29, 2014
 * Modified   :
 * License    : GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
class Smart_Custom_Fields_Controller_Option extends Smart_Custom_Fields_Controller_Base {

	/**
	 * __construct
	 */
	public function __construct() {
		parent::__construct();
		add_action( SCF_Config::PREFIX . 'custom-options-page', array( $this, 'save_option' ) );
		add_action( SCF_Config::PREFIX . 'custom-options-page', array( $this, 'custom_options_page' ) );
	}

	/**
	 * Loading resources for term edit page
	 *
	 * @param string $hook
	 */
	public function admin_enqueue_scripts( $hook ) {
		parent::admin_enqueue_scripts( $hook );
		wp_enqueue_style(
			SCF_Config::PREFIX . 'option',
			plugins_url( SCF_Config::NAME ) . '/css/option.css'
		);
	}

	/**
	 * Displaying custom fields in custom options page
	 *
	 * @param stdClass $Option
	 */
	public function custom_options_page( $Option ) {
		$settings = SCF::get_settings( $Option );
		if ( ! $settings ) {
			return;
		}
		?>
		<form method="post" action="">
			<?php foreach ( $settings as $Setting ) : ?>
				<?php $callback_args['args'] = $Setting->get_groups(); ?>
				<table class="form-table">
					<tr>
						<th scope="row"><?php echo esc_html( $Setting->get_title() ); ?></th>
						<td><?php $this->display_meta_box( $Option, $callback_args ); ?></td>
					</tr>
				</table>
			<?php endforeach; ?>
			<p class="submit">
				<input type="submit" name="submit" id="submit" class="button button-primary" value="<?php esc_attr_e( 'Save settings', 'smart-custom-fields' ); ?>">
			</p>
		</form>
		<?php
	}

	/**
	 * Saving meta data from custom fields in custom options page
	 *
	 * @param stdClass $Option
	 */
	public function save_option( $Option ) {
		if ( ! isset( $_POST[ SCF_Config::NAME ] ) ) {
			return;
		}

		$this->save( $_POST, $Option );
	}
}
