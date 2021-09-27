<?php
/**
 * @package smart-custom-fields
 * @author inc2734
 * @license GPL-2.0+
 */

/**
 * Smart_Custom_Fields_Group class.
 */
class Smart_Custom_Fields_Group {

	/**
	 * Group name.
	 *
	 * @var string
	 */
	protected $name = null;

	/**
	 * Array of field objects.
	 *
	 * @var array
	 */
	protected $fields = array();

	/**
	 * Whether repeating group.
	 *
	 * @var bool
	 */
	protected $repeat = false;

	/**
	 * __construct
	 *
	 * @param string $group_name Gruop name.
	 * @param bool   $repeat     If repeat, set true.
	 * @param array  $_fields    Fields.
	 */
	public function __construct( $group_name = null, $repeat = false, array $_fields = array() ) {
		$this->name   = $group_name;
		$this->repeat = true === $repeat ? true : false;
		$fields       = array();
		foreach ( $_fields as $field_attributes ) {
			$field = SCF::get_form_field_instance( $field_attributes['type'] );
			if ( ! is_a( $field, 'Smart_Custom_Fields_Field_Base' ) ) {
				continue;
			}
			foreach ( $field_attributes as $key => $value ) {
				$field->set( $key, $value );
			}

			if ( ! empty( $field ) ) {
				$fields[ $field->get( 'name' ) ] = $field;
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
	 * Getting the field.
	 *
	 * @param string $field_name Field name.
	 * @return Smart_Custom_Fields_Field_Base|null
	 */
	public function get_field( $field_name ) {
		$fields = $this->get_fields();
		if ( isset( $fields[ $field_name ] ) ) {
			return $fields[ $field_name ];
		}
	}

	/**
	 * Whether repeating group.
	 *
	 * @return bool
	 */
	public function is_repeatable() {
		return $this->repeat;
	}

	/**
	 *  Displaying "hide" if $repeatable is empty.
	 *
	 * @param string $repeatable Repeatable.
	 */
	private function add_hide_class( $repeatable ) {
		if ( ! $repeatable ) {
			echo 'hide';
		}
	}

	/**
	 * Displaying the option fields in custom field settings page ( Common ).
	 *
	 * @param int $group_key Group key.
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
