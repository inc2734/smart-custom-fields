<?php
/**
 * Smart_Custom_Fields_Group
 * Version    : 1.0.0
 * Author     : inc2734
 * Created    : September 23, 2014
 * Modified   : February 27, 2015
 * License    : GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
class Smart_Custom_Fields_Group {

	/**
	 * Group name
	 *
	 * @var string
	 */
	protected $name = null;

	/**
	 * Array of field objects
	 *
	 * @var array
	 */
	protected $fields = array();

	/**
	 * Whether repeating group
	 *
	 * @var bool
	 */
	protected $repeat = false;

	/**
	 * __construct
	 *
	 * @param string $group_name
	 * @param bool   $repeat
	 * @param array  $_fields
	 */
	public function __construct( $group_name = null, $repeat = false, array $_fields = array() ) {
		$this->name   = $group_name;
		$this->repeat = ( $repeat === true ) ? true : false;
		$fields       = array();
		foreach ( $_fields as $field_attributes ) {
			$Field = SCF::get_form_field_instance( $field_attributes['type'] );
			if ( ! is_a( $Field, 'Smart_Custom_Fields_Field_Base' ) ) {
				continue;
			}
			foreach ( $field_attributes as $key => $value ) {
				$Field->set( $key, $value );
			}

			if ( ! empty( $Field ) ) {
				$fields[ $Field->get( 'name' ) ] = $Field;
			}
		}
		$this->fields = $fields;
	}

	/**
	 * Getting group name
	 *
	 * @return string
	 */
	public function get_name() {
		if ( is_numeric( $this->name ) ) {
			return;
		}
		return $this->name;
	}

	/**
	 * Getting fields that saved in this settings page
	 *
	 * @return array
	 */
	public function get_fields() {
		return $this->fields;
	}

	/**
	 * Getting the field
	 *
	 * @param string $field_name
	 * @return Smart_Custom_Fields_Field_Base|null
	 */
	public function get_field( $field_name ) {
		$fields = $this->get_fields();
		if ( isset( $fields[ $field_name ] ) ) {
			return $fields[ $field_name ];
		}
	}

	/**
	 * Whether repeating group
	 *
	 * @return bool
	 */
	public function is_repeatable() {
		return $this->repeat;
	}

	/**
	 *  Displaying "hide" if $key isn't empty
	 *
	 * @param string $key
	 */
	private function add_hide_class( $key ) {
		if ( ! $key ) {
			echo 'hide';
		}
	}

	/**
	 * Displaying the option fields in custom field settings page ( Common )
	 *
	 * @param int $group_key
	 */
	public function display_options( $group_key ) {
		?>
		<div class="<?php echo esc_attr( SCF_Config::PREFIX . 'group-repeat' ); ?>">
			<div class="<?php echo esc_attr( SCF_Config::PREFIX . 'icon-handle' ); ?>"></div>
			<label>
				<input type="checkbox"
					name="<?php echo esc_attr( SCF_Config::NAME . '[' . $group_key . '][repeat]' ); ?>"
					value="true"
					<?php checked( $this->is_repeatable(), true ); ?>
				/>
				<?php esc_html_e( 'Repeat', 'smart-custom-fields' ); ?>
			</label>
		</div>
		<table class="<?php echo esc_attr( SCF_Config::PREFIX . 'group-names' ); ?> <?php $this->add_hide_class( $this->is_repeatable() ); ?>">
			<tr>
				<th><?php esc_html_e( 'Group name', 'smart-custom-fields' ); ?><span class="<?php echo esc_attr( SCF_Config::PREFIX . 'require' ); ?>">*</span></th>
				<td>
					<input type="text"
						name="<?php echo esc_attr( SCF_Config::NAME . '[' . $group_key . '][group-name]' ); ?>"
						size="30"
						class="<?php echo esc_attr( SCF_Config::PREFIX . 'group-name' ); ?>"
						value="<?php echo esc_attr( $this->get_name() ); ?>"
					/>
				</td>
			</tr>
		</table>
		<?php
	}
}
