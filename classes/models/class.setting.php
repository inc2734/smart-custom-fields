<?php
/**
 * Smart_Custom_Fields_Setting
 * Version    : 1.0.0
 * Author     : Takashi Kitajima
 * Created    : September 23, 2014
 * Modified   : February 27, 2015
 * License    : GPLv2
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
class Smart_Custom_Fields_Setting {

	/**
	 * カスタムフィールド設定の Post ID
	 * @var string
	 */
	protected $id;

	/**
	 * カスタムフィールド設定のタイトル
	 * @var title
	 */
	protected $title;

	/**
	 * 保存されているグループオブジェクトの配列
	 * @var array
	 */
	protected $groups = array();

	/**
	 * __construct
	 * 
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
	 * Post ID を取得
	 * 
	 * @return string
	 */
	public function get_id() {
		return $this->id;
	}

	/**
	 * post_title を取得
	 *
	 * @return string
	 */
	public function get_title() {
		return $this->title;
	}

	/**
	 * この設定ページに保存されている各グループを取得
	 * 
	 * @return array
	 */
	public function get_groups() {
		return $this->groups;
	}

	/**
	 * グループを最後に追加。引数なしで空のグループを追加
	 * 
	 * @param string グループ名
	 * @param bool 繰り返し可能かどうか
	 * @param array $_fields フィールドオブジェクトの配列
	 */
	public function add_group( $group_name = null, $repeat = false, array $fields = array() ) {
		$Group = $this->new_group( $group_name, $repeat, $fields );
		$this->groups[] = $Group;
	}

	/**
	 * グループを先頭に追加。引数なしで空のグループを追加
	 *
	 * @param string グループ名
	 * @param bool 繰り返し可能かどうか
	 * @param array $_fields フィールドオブジェクトの配列
	 */
	public function add_group_unshift( $group_name = null, $repeat = false, array $fields = array() ) {
		$Group = $this->new_group( $group_name, $repeat, $fields );
		array_unshift( $this->groups, $Group );
	}

	/**
	 * グループを生成して返す
	 * 
	 * @param string グループ名
	 * @param bool 繰り返し可能かどうか
	 * @param array $_fields フィールドオブジェクトの配列
	 */
	protected function new_group( $group_name, $repeat, $fields ) {
		return new Smart_Custom_Fields_Group( $group_name, $repeat, $fields );
	}
}
