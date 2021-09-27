<?php
/**
 * @package smart-custom-fields
 * @author inc2734
 * @license GPL-2.0+
 */

/**
 * Smart_Custom_Fields_Controller_Option class.
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
	 * Loading resources for term edit page.
	 *
	 * @param string $hook The current admin page.
	 */
	public function admin_enqueue_scripts( $hook ) {
		parent::admin_enqueue_scripts( $hook );
		wp_enqueue_style(
			SCF_Config::PREFIX . 'option',
			plugins_url( SCF_Config::NAME ) . '/css/option.css'
		);
	}

	/**
	 * Displaying custom fields in custom options page.
	 *
	 * @param stdClass $option Option object.
	 */
	public function custom_options_page( $option ) {
		$settings = SCF::get_settings( $option );
		if ( ! $settings ) {
			return;
		}

		$callback_args = [];
		?>
		<form method="post" action="">
			<?php foreach ( $settings as $setting ) : ?>
				<?php $callback_args['args'] = $setting->get_groups(); ?>
				<table class="form-table">
					<tr>
						<th scope="row"><?php echo esc_html( $setting->get_title() ); ?></th>
						<td><?php $this->display_meta_box( $option, $callback_args ); ?></td>
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
	 * Saving meta data from custom fields in custom options page.
	 *
	 * @param stdClass $option Option object.
	 */
	public function save_option( $option ) {
		if ( ! isset( $_POST[ SCF_Config::NAME ] ) ) {
			return;
		}

		$this->save( $_POST, $option );
	}
}
