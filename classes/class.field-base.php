<?php
/**
 * Smart_Custom_Fields_Field_Base
 * Version    : 1.0.2
 * Author     : Takashi Kitajima
 * Created    : October 7, 2014
 * Modified   : October 21, 2014
 * License    : GPLv2
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
abstract class Smart_Custom_Fields_Field_Base {

	/**
	 * $name
	 */
	protected $name;

	/**
	 * $label
	 */
	protected $label;

	/**
	 * $allow_multiple_data
	 */
	protected $allow_multiple_data = false;

	/**
	 * $field
	 */
	protected $field = array();

	/**
	 * __construct
	 */
	public function __construct() {
		$settings = $this->init();
		if ( !empty( $settings['name'] ) ) {
			$this->name = $settings['name'];
		}
		if ( !empty( $settings['label'] ) ) {
			$this->label = $settings['label'];
		}
		if ( !$this->name || !$this->label ) {
			exit;
		}
		if ( empty( $settings['optgroup'] ) ) {
			$settings['optgroup'] = 'basic-fields';
		}
		if ( isset( $settings['allow-multiple-data'] ) && $settings['allow-multiple-data'] === true ) {
			$this->allow_multiple_data = true;
		}
		add_filter( SCF_Config::PREFIX . 'field-select-' . $settings['optgroup'], array( $this, 'field_select' ) );
		add_action( SCF_Config::PREFIX . 'field-options', array( $this, '_display_field_options' ), 10, 3 );
		$this->after_loaded();

		SCF::add_field_instance( $this );
	}

	/**
	 * init
	 * @return array ( name, label, optgroup, allow-multiple-data )
	 */
	abstract protected function init();

	/**
	 * after_loaded
	 */
	protected function after_loaded() {
	}

	/**
	 * get_field
	 * @param array $field フィールドの情報
	 * @param int $index インデックス番号
	 * @param mixed $value 保存されている値（check のときだけ配列）
	 * @return string html
	 */
	abstract public function get_field( $field, $index, $value );

	/**
	 * field_select
	 * @param array $options その optgroup に属するフィールドのリスト
	 * @return array $options
	 */
	public function field_select( $options ) {
		$options[$this->name] = $this->label;
		return $options;
	}

	/**
	 * display_field_options
	 * @param int $group_key
	 * @param int $field_key
	 */
	abstract protected function display_field_options( $group_key, $field_key );
	public function _display_field_options( $group_key, $field_key, $field ) {
		$this->field = $field;
		?>
		<tr class="<?php echo esc_attr( SCF_Config::PREFIX . 'field-options' ); ?> <?php echo esc_attr( SCF_Config::PREFIX . 'field-options-' . $this->name ); ?> hide">
			<td colspan="2">
				<table>
					<?php $this->display_field_options( $group_key, $field_key ); ?>
				</table>
			</td>
		</tr>
		<?php
	}

	/**
	 * get_name_attribute
	 * @param string $name 定義されたフィールドの name
	 * @param string $index 添字
	 * @return string
	 */
	protected function get_name_attribute( $name, $index ) {
		return SCF_Config::NAME . '[' . $name . '][_' . $index . ']';
	}

	/**
	 * get_disable_attribute
	 * @param string $index 添字
	 * @return bool $disabled
	 */
	protected function get_disable_attribute( $index ) {
		$disabled = false;
		if ( is_null( $index ) ) {
			$disabled = true;
		}
		return $disabled;
	}

	/**
	 * get_field_name
	 * フィールド設定画面で使用する name 属性を返す
	 */
	protected function get_field_name( $group_key, $field_key, $name ) {
		return sprintf(
			'%s[%d][fields][%d][%s]',
			SCF_Config::NAME,
			$group_key,
			$field_key,
			$name
		);
	}

	/**
	 * get_field_value
	 * フィールド設定画面で使用する value を返す
	 */
	protected function get_field_value( $name ) {
		return $this->get( $name, $this->field );
	}

	/**
	 * get
	 * @param string $key 取得したいデータのキー
	 * @param array $data データ配列
	 * @return mixed
	 */
	protected function get( $key, array $data ) {
		if ( isset( $data[$key] ) ) {
			return $data[$key];
		}
	}
	
	/**
	 * get_name
	 * @return string
	 */
	public function get_name() {
		return $this->name;
	}

	/**
	 * get_allow_multiple_data
	 */
	public function allow_multiple_data() {
		return $this->allow_multiple_data;
	}
	
	/**
	 * get_choices
	 * @param string $choices
	 * @return array
	 */
	public function get_choices( $choices ) {
		return explode( "\n", $choices );
	}
}