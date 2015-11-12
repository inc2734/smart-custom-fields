<?php
/**
 * Smart_Custom_Fields_Field_Base
 * Version    : 1.1.0
 * Author     : inc2734
 * Created    : October 7, 2014
 * Modified   : February 28, 2015
 * License    : GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
abstract class Smart_Custom_Fields_Field_Base {

	/**
	 * このフィールドの内部属性値
	 * @var array
	 */
	protected $attributes = array(
		'type'                => '', // eg. text
		'display-name'        => '', // eg. Text
		'optgroup'            => 'other-fields',
		'allow-multiple-data' => false,
	);

	/**
	 * このフィールドの設定項目
	 * @var array
	 */
	protected $options = array(
		'name'  => '', // name 属性
		'label' => '', // カスタムフィールド入力画面で表示するラベル
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
	 * 必須項目の設定
	 *
	 * @return array
	 */
	abstract protected function init();

	/**
	 * 設定項目の設定
	 *
	 * @return array
	 */
	abstract protected function options();

	/**
	 * フィールド初期化直後に実行する処理
	 */
	protected function after_loaded() {
	}

	/**
	 * 投稿画面にフィールドを表示
	 *
	 * @param int $index インデックス番号
	 * @param mixed $value 保存されている値（check のときだけ配列）
	 * @return string html
	 */
	abstract public function get_field( $index, $value );

	/**
	 * 設定画面でこのフィールドのタイプ選択にこのフィールドのタイプを追加
	 *
	 * @param array $attributes その optgroup に属するフィールドのリスト
	 * @return array
	 */
	public function field_select( $attributes ) {
		$attributes[$this->get_attribute( 'type' )] = $this->get_attribute( 'display-name' );
		return $attributes;
	}

	/**
	 * 設定画面にフィールドを表示（共通項目）
	 * 
	 * @param int $group_key
	 * @param int $field_key
	 */
	public function display_options( $group_key, $field_key ) {
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
		$fields = SCF::get_form_field_instances();
		foreach ( $fields as $Field ) {
			if ( $Field->get_attribute( 'type' ) === $this->get_attribute( 'type' ) ) {
				foreach ( $this->options as $key => $value ) {
					$Field->set( $key, $value );
				}
			}
			$Field->_display_field_options( $group_key, $field_key );
		}
	}

	/**
	 * 設定画面にフィールドを表示（オリジナル項目）
	 *
	 * @param int $group_key
	 * @param int $field_key
	 */
	abstract protected function display_field_options( $group_key, $field_key );
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
	 * 投稿画面で表示するカスタムフィールドの name 属性値を返す
	 * 
	 * @param string $name 定義されたフィールドの name
	 * @param string $index 添字
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
	 * 投稿画面で表示するカスタムフィールドを disabled にするかどうか
	 * $index が null 以外のときは全てユーザーが保存したデータなので null のときのみ true を返すこと
	 * 
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
	 * 設定画面で使用する name 属性値を返す
	 * 
	 * @param int $group_key
	 * @param int $field_key
	 * @param string $name
	 * @return string
	 */
	public function get_field_name_in_setting( $group_key, $field_key, $name ) {
		return sprintf(
			'%s[%d][fields][%d][%s]',
			SCF_Config::NAME,
			$group_key,
			$field_key,
			$name
		);
	}

	/**
	 * 設定値を返す
	 *
	 * @param string $key 取得したいデータのキー
	 * @return mixed
	 */
	public function get( $key ) {
		if ( array_key_exists( $key, $this->options ) ) {
			return $this->options[$key];
		}
	}

	/**
	 * 設定値を設定
	 * 
	 * @param string $key 取得したいデータのキー
	 * @param mixed $value 取得したいデータ
	 */
	public function set( $key, $value ) {
		if ( array_key_exists( $key, $this->options ) ) {
			$this->options[$key] = $value;
		}
	}

	/**
	 * 属性値を返す
	 *
	 * @param string $key 取得したいデータのキー
	 * @return mixed
	 */
	public function get_attribute( $key ) {
		if ( array_key_exists( $key, $this->attributes ) ) {
			return $this->attributes[$key];
		}
	}
}
