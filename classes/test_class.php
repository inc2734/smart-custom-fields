<?php
// 設定ページのモデル
class Smart_Custom_Fields_Setting {

	/**
	 * @var string
	 */
	protected $id;

	/**
	 * @var title
	 */
	protected $title;

	/**
	 * @var array
	 */
	protected $groups = array();

	/**
	 * @param int $post_id
	 */
	public function __construct( $id, $title ) {
		$this->id    = $id;
		$this->title = $title;
		$post_meta = get_post_meta(
			$this->get_id(),
			SCF_Config::PREFIX . 'setting',
			true
		);
		if ( is_array( $post_meta ) ) {
			foreach ( $post_meta as $group ) {
				$group = shortcode_atts( array(
					'group-name' => '',
					'repeat'     => false,
					'fields'     => array(),
				), $group );
				$this->add_group(
					$group['group-name'],
					$group['repeat'],
					$group['fields']
				);
			}
		}
	}

	/**
	 * post_id を取得
	 * @return string
	 */
	public function get_id() {
		return $this->id;
	}

	/**
	 * post_title を取得
	 * @return string
	 */
	public function get_title() {
		return $this->title;
	}

	/**
	 * この設定ページに保存されている各グループを取得
	 * @return array
	 */
	public function get_groups() {
		return $this->groups;
	}

	/**
	 * グループを追加
	 * @param string グループ名
	 * @param bool 繰り返し可能かどうか
	 * @param array $_fields フィールドオブジェクトの配列
	 */
	public function add_group( $group_name = null, $repeat = false, array $fields = array() ) {
		$Group = $this->new_group( $group_name, $repeat, $fields );
		$this->groups[] = $Group;
	}

	/**
	 * グループを先頭に追加
	 * @param string グループ名
	 * @param bool 繰り返し可能かどうか
	 * @param array $_fields フィールドオブジェクトの配列
	 */
	public function add_group_unshift( $group_name = null, $repeat = false, array $fields = array() ) {
		$Group = $this->new_group( $group_name, $repeat, $fields );
		array_unshift( $this->groups, $Group );
	}

	protected function new_group( $group_name, $repeat, $fields ) {
		return new Smart_Custom_Fields_Group( $group_name, $repeat, $fields );
	}
}

// 設定ページの各グループ
class Smart_Custom_Fields_Group {

	/**
	 * グループ名
	 * @var string
	 */
	protected $name = null;

	/**
	 * フィールドオブジェクトの配列
	 * @var array
	 */
	protected $fields = array();

	/**
	 * 繰り返しグループかどうか
	 * @var bool
	 */
	protected $repeat = false;

	/**
	 * @param string $group_name グループ名
	 * @param bool $repeat 繰り返しグループかどうか
	 * @param array $_fields フィールドオブジェクトの配列
	 */
	public function __construct( $group_name = null, $repeat = false, array $_fields = array() ) {
		$this->name   = $group_name;
		$this->repeat = $repeat;
		$fields = array();
		foreach ( $_fields as $field_attributes ) {
			$Field = SCF::get_form_field_instance( $field_attributes['type'] );
			if ( !is_a( $Field, 'Smart_Custom_Fields_Field_Base' ) ) {
				continue;
			}
			foreach ( $field_attributes as $key => $value ) {
				$Field->set( $key, $value );
			}
			
			if ( !empty( $Field ) ) {
				$fields[] = $Field;
			}
		}
		$this->fields = $fields;
	}

	/**
	 * グループ名を返す
	 * @return string
	 */
	public function get_name() {
		if ( is_numeric( $this->name ) ) {
			return;
		}
		return $this->name;
	}

	/**
	 * この設定ページに保存されている各フィールドを取得
	 * @return array
	 */
	public function get_fields() {
		return $this->fields;
	}

	/**
	 * 繰り返しグループかどうか
	 * @return bool
	 */
	public function is_repeatable() {
		return $this->repeat;
	}

	/**
	 * フィールドを追加
	 * @string $type フィールドタイプ
	 * @param array $options 設定値
	 */
	/*
	public function add_field( $type, array $options ) {
		$Field = SCF::get_form_field_instance( $type );
		if ( $Field ) {
			foreach ( $options as $key => $value ) {
				$Field->set( $key, $value );
			}
		}
	}
	*/

	/**
	 * add_hide_class
	 * @param string $key 値があれば hide を表示
	 */
	private function add_hide_class( $key ) {
		if ( !$key ) {
			echo 'hide';
		}
	}

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