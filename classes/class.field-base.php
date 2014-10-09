<?php
/**
 * Smart_Custom_Fields_Field_Base
 * Version    : 1.0.0
 * Author     : Takashi Kitajima
 * Created    : October 7, 2014
 * Modified   :
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
	 * $field
	 */
	protected $field = array();

	/**
	 * __construct
	 * @param array $options
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
		add_filter( SCF_Config::PREFIX . 'add-fields', array( $this, 'add_fields' ) );
		add_filter( SCF_Config::PREFIX . 'field-select-' . $settings['optgroup'], array( $this, 'field_select' ) );
		add_action( SCF_Config::PREFIX . 'field-options', array( $this, '_display_field_options' ), 10, 3 );
	}

	/**
	 * init
	 * @return array ( name, label, optgroup )
	 */
	abstract protected function init();

	/**
	 * get_field
	 * @param array $field フィールドの情報
	 * @param int $index インデックス番号
	 * @param mixed $value 保存されている値（check のときだけ配列）
	 * @return string html
	 */
	abstract public function get_field( $field, $index, $value );

	/**
	 * add_fields
	 * @param array $fields
	 * @return array $fields
	 */
	public function add_fields( $fields ) {
		$fields[$this->name] = $this;
		return $fields;
	}

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
	
	public function get_choices( $choices ) {
		return explode( "\n", $choices );
	}
}