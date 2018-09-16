<?php
/**
 * Smart_Custom_Fields_Options_Page
 * Version    : 1.0.0
 * Author     : inc2734
 * Created    : May 29, 2014
 * Modified   :
 * License    : GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
class Smart_Custom_Fields_Options_Page {

	/**
	 * @var string
	 */
	protected $page_title;

	/**
	 * @var string
	 */
	protected $menu_title;

	/**
	 * @var string
	 */
	protected $capability;

	/**
	 * @var string
	 */
	protected $menu_slug;

	/**
	 * @var string
	 */
	protected $icon_url;

	/**
	 * @var int
	 */
	protected $position;

	/**
	 * @see https://developer.wordpress.org/reference/functions/add_menu_page/
	 * @param string $page_title
	 * @param string $menu_title
	 * @param string $capability
	 * @param string $menu_slug
	 * @param string $icon_url
	 * @param int    $position
	 */
	public function __construct( $page_title, $menu_title, $capability, $menu_slug, $icon_url = '', $position = null ) {
		$this->page_title = $page_title;
		$this->menu_title = $menu_title;
		$this->capability = $capability;
		$this->menu_slug  = $menu_slug;
		$this->icon_url   = $icon_url;
		$this->position   = $position;
		add_action( 'admin_menu', array( $this, 'add_options_page_menu' ) );
	}

	public function add_options_page_menu() {
		return add_menu_page(
			$this->page_title,
			$this->menu_title,
			$this->capability,
			$this->menu_slug,
			array( $this, 'display' ),
			$this->icon_url,
			$this->position
		);
	}

	public function display() {
		$Option = SCF::generate_option_object( $_GET['page'] );
		if ( ! $Option ) {
			return;
		}
		?>
		<div class="wrap">
			<h3><?php echo esc_html( $Option->menu_title ); ?></h3>
			<?php do_action( SCF_Config::PREFIX . 'custom-options-page', $Option ); ?>
		</div>
		<?php
	}
}
