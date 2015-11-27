<?php
/**
 * Plugin name: Smart Custom Fields
 * Plugin URI: https://github.com/inc2734/smart-custom-fields/
 * Description: Smart Custom Fields is a simple plugin that management custom fields.
 * Version: 1.6.3
 * Author: inc2734
 * Author URI: http://2inc.org
 * Created: October 9, 2014
 * Modified: Novermber 21, 2015
 * Text Domain: smart-custom-fields
 * Domain Path: /languages
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
class Smart_Custom_Fields {

	/**
	 * __construct
	 */
	public function __construct() {
		require_once plugin_dir_path( __FILE__ ) . 'classes/class.config.php';
		add_action( 'plugins_loaded', array( $this, 'plugins_loaded' ) );
		register_uninstall_hook( __FILE__, array( __CLASS__, 'uninstall' ) );
	}

	/**
	 * 翻訳ファイルの読み込み
	 */
	public function plugins_loaded() {
		load_plugin_textdomain (
			'smart-custom-fields',
			false,
			dirname( plugin_basename( __FILE__ ) ) . '/languages'
		);

		add_action( 'after_setup_theme', array( $this, 'after_setup_theme' ) );
	}

	/**
	 * 各クラスの読み込み
	 */
	public function after_setup_theme() {
		do_action( SCF_Config::PREFIX . 'load' );
		require_once plugin_dir_path( __FILE__ ) . 'classes/models/class.meta.php';
		require_once plugin_dir_path( __FILE__ ) . 'classes/models/class.setting.php';
		require_once plugin_dir_path( __FILE__ ) . 'classes/models/class.group.php';
		require_once plugin_dir_path( __FILE__ ) . 'classes/models/class.abstract-field-base.php';
		require_once plugin_dir_path( __FILE__ ) . 'classes/models/class.revisions.php';
		require_once plugin_dir_path( __FILE__ ) . 'classes/models/class.ajax.php';
		require_once plugin_dir_path( __FILE__ ) . 'classes/class.scf.php';
		new Smart_Custom_Fields_Revisions();

		foreach ( glob( plugin_dir_path( __FILE__ ) . 'classes/fields/*.php' ) as $form_item ) {
			include_once $form_item;
			$basename  = basename( $form_item, '.php' );
			$classname = preg_replace( '/^class\.field\-(.+)$/', 'Smart_Custom_Fields_Field_$1', $basename );
			if ( class_exists( $classname ) ) {
				new $classname();
			}
		}
		do_action( SCF_Config::PREFIX . 'fields-loaded' );

		add_action( 'init'          , array( $this, 'register_post_type' ) );
		add_action( 'init'          , array( $this, 'ajax_request' ) );
		add_action( 'admin_menu'    , array( $this, 'admin_menu' ) );
		add_action( 'current_screen', array( $this, 'current_screen' ) );
	}

	/**
	 * アンインストール時の処理
	 */
	public static function uninstall() {
		$cf_posts = get_posts( array(
			'post_type'      => SCF_Config::NAME,
			'posts_per_page' => -1,
			'post_status'    => 'any',
		) );
		foreach ( $cf_posts as $post ) {
			wp_delete_post( $post->ID, true );
		}
		delete_post_meta_by_key( SCF_Config::PREFIX . 'repeat-multiple-data' );

		// option の smart-cf-xxx を削除
		global $wpdb;
		$wpdb->query(
			$wpdb->prepare(
				"
				DELETE FROM $wpdb->options
					WHERE option_name LIKE %s
				",
				SCF_Config::PREFIX . '%'
			)
		);
	}

	/**
	 * 各管理画面の実行
	 *
	 * @param WP_Screen $screen
	 */
	public function current_screen( $screen ) {
		// 一覧画面
		if ( $screen->id === 'edit-' . SCF_Config::NAME ) {
		}
		// 新規作成・編集画面
		elseif ( $screen->id === SCF_Config::NAME ) {
			require_once plugin_dir_path( __FILE__ ) . 'classes/controller/class.settings.php';
			new Smart_Custom_Fields_Controller_Settings();
		}
		// その他の新規作成・編集画面
		elseif ( in_array( $screen->id, get_post_types() ) ) {
			$post_id = $this->get_post_id_in_admin();
			$Post = new stdClass();
			$Post->ID        = $post_id;
			$Post->post_type = $screen->id;
			if ( SCF::get_settings( new WP_Post( $Post ) ) ) {
				require_once plugin_dir_path( __FILE__ ) . 'classes/controller/class.controller-base.php';
				require_once plugin_dir_path( __FILE__ ) . 'classes/controller/class.editor.php';
				new Smart_Custom_Fields_Revisions();
				new Smart_Custom_Fields_Controller_Editor();
			}
		}
		// プロフィール編集画面
		elseif ( in_array( $screen->id, array( 'profile', 'user-edit' ) ) ) {
			$user_id = $this->get_user_id_in_admin();
			$user_data = get_userdata( $user_id );
			$roles[0]  = false;
			if ( $user_data ) {
				$roles = $user_data->roles;
			}
			if ( SCF::get_settings( get_userdata( $user_id ) ) ) {
				require_once plugin_dir_path( __FILE__ ) . 'classes/controller/class.controller-base.php';
				require_once plugin_dir_path( __FILE__ ) . 'classes/controller/class.profile.php';
				new Smart_Custom_Fields_Controller_Profile();
			}
		}
		// タグ、カテゴリー、タクソノミー
		elseif ( $screen->taxonomy ) {
			$term_id = $this->get_term_id_in_admin();
			if ( $term_id ) {
				$term = get_term( $term_id, $screen->taxonomy );
				if ( SCF::get_settings( $term ) ) {
					require_once plugin_dir_path( __FILE__ ) . 'classes/controller/class.controller-base.php';
					require_once plugin_dir_path( __FILE__ ) . 'classes/controller/class.taxonomy.php';
					new Smart_Custom_Fields_Controller_Taxonomy();
				}
			}
		}
	}

	/**
	 * カスタム投稿タイプの登録。メニュー表示は別メソッドで実行
	 */
	public function register_post_type() {
		$labels = array(
			'name'               => __( 'Smart Custom Fields', 'smart-custom-fields' ),
			'menu_name'          => __( 'Smart Custom Fields', 'smart-custom-fields' ),
			'name_admin_bar'     => __( 'Smart Custom Fields', 'smart-custom-fields' ),
			'add_new'            => __( 'Add New', 'smart-custom-fields' ),
			'add_new_item'       => __( 'Add New', 'smart-custom-fields' ),
			'new_item'           => __( 'New Field', 'smart-custom-fields' ),
			'edit_item'          => __( 'Edit Field', 'smart-custom-fields' ),
			'view_item'          => __( 'View Field', 'smart-custom-fields' ),
			'all_items'          => __( 'All Fields', 'smart-custom-fields' ),
			'search_items'       => __( 'Search Fields', 'smart-custom-fields' ),
			'parent_item_colon'  => __( 'Parent Fields:', 'smart-custom-fields' ),
			'not_found'          => __( 'No Fields found.', 'smart-custom-fields' ),
			'not_found_in_trash' => __( 'No Fields found in Trash.', 'smart-custom-fields' )
		);
		register_post_type(
			SCF_Config::NAME,
			array(
				'label'           => 'Smart Custom Fields',
				'labels'          => $labels,
				'public'          => false,
				'show_ui'         => true,
				'capability_type' => 'page',
				'supports'        => array( 'title', 'page-attributes' ),
				'show_in_menu'    => false,
			)
		);
	}

	/**
	 * Ajax リクエストのときに発火させたい処理をフックさせる
	 */
	public function ajax_request() {
		$Ajax = new Smart_Custom_Fields_Ajax();
	}

	/**
	 * 管理画面にメニューを追加
	 */
	public function admin_menu() {
		add_menu_page(
			'Smart Custom Fields',
			'Smart Custom Fields',
			'manage_options',
			'edit.php?post_type=' . SCF_Config::NAME,
			false,
			false,
			'80.026'
		);
		add_submenu_page(
			'edit.php?post_type=' . SCF_Config::NAME,
			__( 'Add New', 'smart-custom-fields' ),
			__( 'Add New', 'smart-custom-fields' ),
			'manage_options',
			'post-new.php?post_type=' . SCF_Config::NAME
		);
	}

	/**
	 * 編集画面でその投稿のIDを取得
	 *
	 * @return int
	 */
	protected function get_post_id_in_admin() {
		$post_id = false;
		if ( !empty( $_GET['post'] ) ) {
			$post_id = $_GET['post'];
		} elseif ( !empty( $_POST['post_ID'] ) ) {
			$post_id = $_POST['post_ID'];
		}
		return $post_id;
	}

	/**
	 * プロフィール、ユーザー編集画面でそのユーザーのIDを取得
	 *
	 * @return int
	 */
	protected function get_user_id_in_admin() {
		$screen = get_current_screen();
		$user_id = false;
		if ( !empty( $_GET['user_id'] ) ) {
			$user_id = $_GET['user_id'];
		} elseif ( !empty( $_POST['user_id'] ) ) {
			$user_id = $_POST['user_id'];
		} elseif ( $screen->id === 'profile' ) {
			$current_user = wp_get_current_user();
			$user_id      = $current_user->ID;
		}
		return $user_id;
	}

	/**
	 * ターム編集画面でそのタームのIDを取得
	 *
	 * @return int
	 */
	protected function get_term_id_in_admin() {
		$term_id = false;
		if ( !empty( $_GET['tag_ID'] ) ) {
			$term_id = $_GET['tag_ID'];
		} elseif ( !empty( $_POST['tag_ID'] ) ) {
			$term_id = $_POST['tag_ID'];
		}
		return $term_id;
	}
}
new Smart_Custom_Fields();
