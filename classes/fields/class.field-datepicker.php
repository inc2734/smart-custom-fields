<?php
/**
 * Smart_Custom_Fields_Field_Datepicker
 * Version    : 1.1.0
 * Author     : Takashi Kitajima
 * Created    : January 17, 2015
 * Modified   : February 27, 2015
 * License    : GPLv2
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
class Smart_Custom_Fields_Field_Datepicker extends Smart_Custom_Fields_Field_Base {

	/**
	 * 必須項目の設定
	 *
	 * @return array
	 */
	protected function init() {
		add_action(
			SCF_Config::PREFIX . 'before-editor-enqueue-scripts',
			array( $this, 'editor_enqueue_scripts' )
		);
		add_action(
			SCF_Config::PREFIX . 'before-settings-enqueue-scripts',
			array( $this, 'settings_enqueue_scripts' )
		);
		return array(
			'type'         => 'datepicker',
			'display-name' => __( 'Date picker', 'smart-custom-fields' ),
			'optgroup'     => 'other-fields',
		);
	}

	/**
	 * 設定項目の設定
	 *
	 * @return array
	 */
	protected function options() {
		return array(
			'date_format' => '',
			'max_date'    => '',
			'min_date'    => '',
			'default'     => '',
			'notes'       => '',
		);
	}

	/**
	 * CSS、JSの読み込み
	 */
	public function editor_enqueue_scripts() {
		global $wp_scripts;
		$ui = $wp_scripts->query( 'jquery-ui-core' );
		wp_enqueue_style(
			'jquery.ui',
			'//ajax.googleapis.com/ajax/libs/jqueryui/' . $ui->ver . '/themes/smoothness/jquery-ui.min.css',
			array(),
			$ui->ver
		);
		wp_enqueue_script( 'jquery-ui-datepicker' );
		wp_enqueue_script(
			SCF_Config::PREFIX . 'editor-datepicker',
			plugins_url( '../../js/editor-datepicker.js', __FILE__ ),
			array( 'jquery', 'jquery-ui-datepicker' ),
			false,
			true
		);
	}

	/**
	 * CSS、JSの読み込み
	 */
	public function settings_enqueue_scripts() {
		global $wp_scripts;
		$ui = $wp_scripts->query( 'jquery-ui-core' );
		wp_enqueue_style(
			'jquery.ui',
			'//ajax.googleapis.com/ajax/libs/jqueryui/' . $ui->ver . '/themes/smoothness/jquery-ui.min.css',
			array(),
			$ui->ver
		);
		wp_enqueue_script( 'jquery-ui-datepicker' );
		wp_enqueue_script(
			SCF_Config::PREFIX . 'settings-datepicker',
			plugins_url( '../../js/settings-datepicker.js', __FILE__ ),
			array( 'jquery', 'jquery-ui-datepicker' ),
			false,
			true
		);
	}

	/**
	 * 投稿画面にフィールドを表示
	 *
	 * @param int $index インデックス番号
	 * @param mixed $value 保存されている値（check のときだけ配列）
	 * @return string html
	 */
	public function get_field( $index, $value ) {
		$name     = $this->get_field_name_in_editor( $index );
		$disabled = $this->get_disable_attribute( $index );
		$data_js  = $this->get_data_js();

		return sprintf(
			'<input type="text" name="%s" value="%s" class="%s" %s data-js=\'%s\' />',
			esc_attr( $name ),
			esc_attr( $value ),
			esc_attr( SCF_Config::PREFIX . 'datepicker' ),
			disabled( true, $disabled, false ),
			$data_js
		);
	}

	/**
	 * 設定画面にフィールドを表示（オリジナル項目）
	 *
	 * @param int $group_key
	 * @param int $field_key
	 */
	public function display_field_options( $group_key, $field_key ) {
		?>
		<tr>
			<th><?php esc_html_e( 'Default', 'smart-custom-fields' ); ?></th>
			<td>
				<input type="text"
					name="<?php echo esc_attr( $this->get_field_name_in_setting( $group_key, $field_key, 'default' ) ); ?>"
					class="widefat default-option"
					value="<?php echo esc_attr( $this->get( 'default' ) ); ?>"
					data-js='<?php echo $this->get_data_js(); ?>' />
			</td>
		</tr>
		<tr>
			<th><?php esc_html_e( 'Date Format', 'smart-custom-fields' ); ?></th>
			<td>
				<input type="text"
					name="<?php echo esc_attr( $this->get_field_name_in_setting( $group_key, $field_key, 'date_format' ) ); ?>"
					class="widefat"
					value="<?php echo esc_attr( $this->get( 'date_format' ) ); ?>"
				/><br />
				<span class="<?php echo esc_attr( SCF_Config::PREFIX ); ?>notes">
					<?php esc_html_e( 'e.g dd/mm/yy', 'smart-custom-fields' ); ?>
					<?php printf(
						esc_html( 'Prease see %sdateFormat%s', 'smart-custom-fields' ),
						'<a href="http://api.jqueryui.com/datepicker/#option-dateFormat" target="_blank">',
						'</a>'
					); ?>
				</span>
			</td>
		</tr>
		<tr>
			<th><?php esc_html_e( 'Max Date', 'smart-custom-fields' ); ?></th>
			<td>
				<input type="text"
					name="<?php echo esc_attr( $this->get_field_name_in_setting( $group_key, $field_key, 'max_date' ) ); ?>"
					class="widefat"
					value="<?php echo esc_attr( $this->get( 'max_date' ) ); ?>"
				/><br />
				<span class="<?php echo esc_attr( SCF_Config::PREFIX ); ?>notes">
					<?php esc_html_e( 'e.g +1m +1w', 'smart-custom-fields' ); ?>
					<?php printf(
						esc_html( 'Prease see %smaxData%s', 'smart-custom-fields' ),
						'<a href="http://api.jqueryui.com/datepicker/#option-maxDate" target="_blank">',
						'</a>'
					); ?>
				</span>
			</td>
		</tr>
		<tr>
			<th><?php esc_html_e( 'Min Date', 'smart-custom-fields' ); ?></th>
			<td>
				<input type="text"
					name="<?php echo esc_attr( $this->get_field_name_in_setting( $group_key, $field_key, 'min_date' ) ); ?>"
					class="widefat"
					value="<?php echo esc_attr( $this->get( 'min_date' ) ); ?>"
				/><br />
				<span class="<?php echo esc_attr( SCF_Config::PREFIX ); ?>notes">
					<?php esc_html_e( 'e.g +1m +1w', 'smart-custom-fields' ); ?>
					<?php printf(
						esc_html( 'Prease see %sminData%s', 'smart-custom-fields' ),
						'<a href="http://api.jqueryui.com/datepicker/#option-minDate" target="_blank">',
						'</a>'
					); ?>
				</span>
			</td>
		</tr>
		<tr>
			<th><?php esc_html_e( 'Notes', 'smart-custom-fields' ); ?></th>
			<td>
				<input type="text"
					name="<?php echo esc_attr( $this->get_field_name_in_setting( $group_key, $field_key, 'notes' ) ); ?>"
					class="widefat"
					value="<?php echo esc_attr( $this->get( 'notes' ) ); ?>"
				/>
			</td>
		</tr>
		<?php
	}

	/**
	 * 管理画面で設定された datepicker のオプションを json_encode して返す
	 *
	 * @return string json_encode された設定
	 */
	protected function get_data_js() {
		$js = array(
			'showMonthAfterYear' => true,
			'changeYear'         => true,
			'changeMonth'        => true,
		);
		// 日本語の場合は日本語表記に変更
		if ( get_locale() === 'ja' ) {
			$js = array_merge( $js, array(
				'yearSuffix'      => '年',
				'dateFormat'      => 'yy-mm-dd',
				'dayNames'        => array(
					'日曜日', '月曜日', '火曜日', '水曜日', '木曜日', '金曜日', '土曜日',
				),
				'dayNamesMin'     => array(
					'日', '月', '火', '水', '木', '金', '土',
				),
				'dayNamesShort'   => array(
					'日曜', '月曜', '火曜', '水曜', '木曜', '金曜', '土曜',
				),
				'monthNames'      => array(
					'1月', '2月', '3月', '4月', '5月', '6月', '7月', '8月', '9月', '10月', '11月', '12月',
				),
				'monthNamesShort' =>  array(
					'1月', '2月', '3月', '4月', '5月', '6月', '7月', '8月', '9月', '10月', '11月', '12月',
				)
			) );
		}
		if ( $this->get( 'date_format' ) ) {
			$js['dateFormat'] = $this->get( 'date_format' );
		}
		if ( $this->get( 'max_date' ) ) {
			$js['maxDate'] = $this->get( 'max_date' );
		}
		if ( $this->get( 'min_date' ) ) {
			$js['minDate'] = $this->get( 'min_date' );
		}
		return json_encode( $js );
	}
}
