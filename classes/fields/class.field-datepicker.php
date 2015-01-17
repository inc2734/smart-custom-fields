<?php
/**
 * Smart_Custom_Fields_Field_Datepicker
 * Version    : 1.0.0
 * Author     : Takashi Kitajima
 * Created    : January 17, 2015
 * Modified   :
 * License    : GPLv2
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
class Smart_Custom_Fields_Field_Datepicker extends Smart_Custom_Fields_Field_Base {

	/**
	 * init
	 * @return array ( name, label, optgroup )
	 */
	protected function init() {
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
		return array(
			'name'     => 'datepicker',
			'label'    => __( 'Date picker', 'smart-custom-fields' ),
			'optgroup' => 'other-fields',
		);
	}

	/**
	 * admin_enqueue_scripts
	 * @param string $hook
	 */
	public function admin_enqueue_scripts( $hook ) {
		global $wp_scripts;
		if ( in_array( $hook, array( 'post-new.php', 'post.php' ) ) ) {
			$ui = $wp_scripts->query( 'jquery-ui-core' );
			wp_enqueue_style(
				'jquery.ui',
				'//ajax.googleapis.com/ajax/libs/jqueryui/' . $ui->ver . '/themes/smoothness/jquery-ui.min.css',
				array(),
				$ui->ver
			);
			wp_enqueue_script( 'jquery-ui-datepicker' );
			wp_enqueue_script(
				SCF_Config::PREFIX . 'datepicker',
				plugins_url( '../../js/editor-datepicker.js', __FILE__ ),
				array( 'jquery', 'jquery-ui-datepicker' ),
				false,
				true
			);
		}
	}

	/**
	 * get_field
	 * @param array $field フィールドの情報
	 * @param int $index インデックス番号
	 * @param mixed $value 保存されている値（check のときだけ配列）
	 */
	public function get_field( $field, $index, $value ) {
		$name = $this->get_name_attribute( $field['name'], $index );
		$disabled = $this->get_disable_attribute( $index );

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
		if ( !empty( $field['date_format'] ) ) {
			$js['dateFormat'] = $field['date_format'];
		}
		if ( !empty( $field['max_date'] ) ) {
			$js['maxDate'] = $field['max_date'];
		}
		if ( !empty( $field['min_date'] ) ) {
			$js['minDate'] = $field['min_date'];
		}
		$data_js = json_encode( $js );

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
	 * display_field_options
	 * @param int $group_key
	 * @param int $field_key
	 */
	public function display_field_options( $group_key, $field_key ) {
		?>
		<tr>
			<th><?php esc_html_e( 'Default', 'smart-custom-fields' ); ?></th>
			<td>
				<input type="text"
					name="<?php echo esc_attr( $this->get_field_name( $group_key, $field_key, 'default' ) ); ?>"
					class="widefat"
					value="<?php echo esc_attr( $this->get_field_value( 'default' ) ); ?>" />
			</td>
		</tr>
		<tr>
			<th><?php esc_html_e( 'Date Format', 'smart-custom-fields' ); ?></th>
			<td>
				<input type="text"
					name="<?php echo esc_attr( $this->get_field_name( $group_key, $field_key, 'date_format' ) ); ?>"
					class="widefat"
					value="<?php echo esc_attr( $this->get_field_value( 'date_format' ) ); ?>"
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
					name="<?php echo esc_attr( $this->get_field_name( $group_key, $field_key, 'max_date' ) ); ?>"
					class="widefat"
					value="<?php echo esc_attr( $this->get_field_value( 'max_date' ) ); ?>"
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
					name="<?php echo esc_attr( $this->get_field_name( $group_key, $field_key, 'min_date' ) ); ?>"
					class="widefat"
					value="<?php echo esc_attr( $this->get_field_value( 'min_date' ) ); ?>"
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
					name="<?php echo esc_attr( $this->get_field_name( $group_key, $field_key, 'notes' ) ); ?>"
					class="widefat"
					value="<?php echo esc_attr( $this->get_field_value( 'notes' ) ); ?>"
				/>
			</td>
		</tr>
		<?php
	}
}