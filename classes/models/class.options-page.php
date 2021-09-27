<?php
/**
 * @package smart-custom-fields
 * @author inc2734
 * @license GPL-2.0+
 */

/**
 * Smart_Custom_Fields_Options_Page class.
 */
class Smart_Custom_Fields_Options_Page {

	/**
	 * The text to be displayed in the title tags of the page when the menu is selected.
	 *
	 * @var string
	 */
	protected $page_title;

	/**
	 * The text to be used for the menu.
	 *
	 * @var string
	 */
	protected $menu_title;

	/**
	 * The capability required for this menu to be displayed to the user.
	 *
	 * @var string
	 */
	protected $capability;

	/**
	 * The slug name to refer to this menu by. Should be unique for this menu page and only include lowercase alphanumeric, dashes, and underscores characters to be compatible with sanitize_key().
	 *
	 * @var string
	 */
	protected $menu_slug;

	/**
	 * The URL to the icon to be used for this menu.
	 *
	 * @var string
	 */
	protected $icon_url;

	/**
	 * The position in the menu order this item should appear.
	 *
	 * @var int
	 */
	protected $position;

	/**
	 * __construct
	 *
	 * @see https://developer.wordpress.org/reference/functions/add_menu_page/
	 *
	 * @param string $page_title The text to be displayed in the title tags of the page when the menu is selected.
	 * @param string $menu_title The text to be used for the menu.
	 * @param string $capability The capability required for this menu to be displayed to the user.
	 * @param string $menu_slug  The slug name to refer to this menu by. Should be unique for this menu page and only include lowercase alphanumeric, dashes, and underscores characters to be compatible with sanitize_key().
	 * @param string $icon_url   The URL to the icon to be used for this menu.
	 * @param int    $position   The position in the menu order this item should appear.
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

	/**
	 * Add options page menu.
	 */
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

	/**
	 * Display option.
	 */
	public function display() {
		$option = SCF::generate_option_object( $_GET['page'] );
		if ( ! $option ) {
			return;
		}
		?>
		<div class="wrap">
			<h3><?php echo esc_html( $option->menu_title ); ?></h3>
			<?php do_action( SCF_Config::PREFIX . 'custom-options-page', $option ); ?>
		</div>
		<?php
	}
}
