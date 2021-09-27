<?php
/**
 * @package smart-custom-fields
 * @author inc2734
 * @license GPL-2.0+
 */

/**
 *  Smart_Custom_Fields_Field_Base class.
 */
abstract class Smart_Custom_Fields_Field_Base {

	/**
	 * Internal attribute value of this field
	 *
	 * @var array
	 */
	protected $attributes = array(
		'type'                => '', // eg. text
		'display-name'        => '', // eg. Text
		'optgroup'            => 'other-fields',
		'allow-multiple-data' => false,
		'layout'              => 'default', // or "full-width" (new attribute to choose layout type)
	);

	/**
	 * Options of this field
	 *
	 * @var array
	 */
	protected $options = array(
		'name'  => '',
		'label' => '',
	);

	/**
	 * __construct
	 */
	public function __construct() {
		$attributes = array_merge( $this->attributes, $this->init() );
		$options    = array_merge( $this->options, $this->options() );
		if ( empty( $attributes['type'] ) || empty( $attributes['display-name'] ) ) {
			exit( 'This field object is invalid. Field object must have type and display-name attributes.' );
		}
		if ( empty( $attributes['optgroup'] ) ) {
			$attributes['optgroup'] = 'basic-fields';
		}
		$this->attributes = $attributes;
		$this->options    = $options;
		add_filter(
			SCF_Config::PREFIX . 'field-select-' . $attributes['optgroup'],
			array( $this, 'field_select' )
		);
		$this->after_loaded();

		SCF::add_form_field_instance( $this );
	}

	/**
	 * Set the required items
	 *
	 * @return array
	 */
	abstract protected function init();

	/**
	 * Set the non required items
	 *
	 * @return array
	 */
	abstract protected function options();

	/**
	 * Processing to be executed immediately after the field initialization
	 */
	protected function after_loaded() {
	}

	/**
	 * Getting the field.
	 *
	 * @param int   $index Field index.
	 * @param mixed $value Field value.
	 * @return string
	 */
	abstract public function get_field( $index, $value );

	/**
	 * Adding the type of this field to fields selection in custom field settings page.
	 *
	 * @param array $attributes List of fields that belong to the optgroup.
	 * @return array
	 */
	public function field_select( $attributes ) {
		$attributes[ $this->get_attribute( 'type' ) ] = $this->get_attribute( 'display-name' );
		return $attributes;
	}

	/**
	 * Displaying the option fields in custom field settings page ( Common ).
	 *
	 * @param int $group_key Group key.
	 * @param int $field_key Field key.
	 */
	public function display_options( $group_key, $field_key ) {
		$fields = SCF::get_form_field_instances();
		foreach ( $fields as $field ) {
			if ( $field->get_attribute( 'type' ) === $this->get_attribute( 'type' ) ) {
				foreach ( $this->options as $key => $value ) {
					$field->set( $key, $value );
				}
			}
			$field->_display_field_options( $group_key, $field_key );
		}
	}

	/**
	 * Displaying the name in custom field settings page.
	 *
	 * @param int $group_key Group key.
	 * @param int $field_key Field key.
	 */
	protected function display_name_option( $group_key, $field_key ) {
		?>
		<tr>
			<th><?php esc_html_e( 'Name', 'smart-custom-fields' ); ?><span class="<?php echo esc_attr( SCF_Config::PREFIX . 'require' ); ?>">*</span></th>
			<td>
				<input type="text"
					name="<?php echo esc_attr( $this->get_field_name_in_setting( $group_key, $field_key, 'name' ) ); ?>"
					size="30"
					class="<?php echo esc_attr( SCF_Config::PREFIX . 'field-name' ); ?>"
					value="<?php echo esc_attr( $this->get( 'name' ) ); ?>"
				/>
			</td>
		</tr>
		<?php
	}

	/**
	 * Displaying the label in custom field settings page.
	 *
	 * @param int $group_key Group key.
	 * @param int $field_key Field key.
	 */
	protected function display_label_option( $group_key, $field_key ) {
		?>
		<tr>
			<th><?php esc_html_e( 'Label', 'smart-custom-fields' ); ?></th>
			<td>
				<input type="text"
					name="<?php echo esc_attr( $this->get_field_name_in_setting( $group_key, $field_key, 'label' ) ); ?>"
					size="30"
					class="<?php echo esc_attr( SCF_Config::PREFIX . 'field-label' ); ?>"
					value="<?php echo esc_attr( $this->get( 'label' ) ); ?>"
				/>
			</td>
		</tr>
		<?php
	}

	/**
	 * Displaying the option fields in custom field settings page ( original ).
	 *
	 * @param int $group_key Group key.
	 * @param int $field_key Field key.
	 */
	abstract protected function display_field_options( $group_key, $field_key );

	/**
	 * Displaying the option fields in custom field settings page ( original ).
	 *
	 * @param int $group_key Group key.
	 * @param int $field_key Field key.
	 */
	public function _display_field_options( $group_key, $field_key ) {
		?>
		<tr class="<?php echo esc_attr( SCF_Config::PREFIX . 'field-options' ); ?> <?php echo esc_attr( SCF_Config::PREFIX . 'field-options-' . $this->get_attribute( 'type' ) ); ?> hide">
			<td colspan="2">
				<table>
					<?php $this->display_field_options( $group_key, $field_key ); ?>
				</table>
			</td>
		</tr>
		<?php
	}

	/**
	 * Getting the name attribute in editor page.
	 *
	 * @param string $index Field index.
	 * @return string
	 */
	protected function get_field_name_in_editor( $index ) {
		return sprintf(
			'%s[%s][%s]',
			SCF_Config::NAME,
			$this->get( 'name' ),
			$index
		);
	}

	/**
	 * Whether to disabled.
	 * Return true only when the null because data that all users have saved when $index is not null
	 *
	 * @param string $index Field index.
	 * @return bool
	 */
	protected function get_disable_attribute( $index ) {
		$disabled = false;
		if ( is_null( $index ) ) {
			$disabled = true;
		}
		return $disabled;
	}

	/**
	 * Getting the name attribute in custom field settings page.
	 *
	 * @param int    $group_key Group key.
	 * @param int    $field_key Field key.
	 * @param string $name      Field name.
	 * @return string
	 */
	public function get_field_name_in_setting( $group_key, $field_key, $name ) {
		return sprintf(
			'%s[%s][fields][%s][%s]',
			SCF_Config::NAME,
			$group_key,
			$field_key,
			$name
		);
	}

	/**
	 * Getting saved option value.
	 *
	 * @param string $key Key of options of this field.
	 * @return mixed
	 */
	public function get( $key ) {
		if ( array_key_exists( $key, $this->options ) ) {
			return $this->options[ $key ];
		}
	}

	/**
	 * Set option value.
	 *
	 * @param string $key   Key of options of this field.
	 * @param mixed  $value Value of options of this field.
	 */
	public function set( $key, $value ) {
		if ( array_key_exists( $key, $this->options ) ) {
			$this->options[ $key ] = $value;
		}
	}

	/**
	 * Getting the attribute value.
	 *
	 * @param string $key Internal attribute key.
	 * @return mixed
	 */
	public function get_attribute( $key ) {
		if ( array_key_exists( $key, $this->attributes ) ) {
			return $this->attributes[ $key ];
		}
	}
}
