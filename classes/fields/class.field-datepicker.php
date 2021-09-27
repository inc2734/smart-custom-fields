<?php
/**
 * @package smart-custom-fields
 * @author inc2734
 * @license GPL-2.0+
 */

/**
 * Smart_Custom_Fields_Field_Datepicker class.
 */
class Smart_Custom_Fields_Field_Datepicker extends Smart_Custom_Fields_Field_Base {

	/**
	 * Set the required items.
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
	 * Set the non required items.
	 *
	 * @return array
	 */
	protected function options() {
		return array(
			'date_format' => '',
			'max_date'    => '',
			'min_date'    => '',
			'default'     => '',
			'instruction' => '',
			'notes'       => '',
		);
	}

	/**
	 * Loading resources for editor.
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
	 * Loading resources for editor for custom field settings page.
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
			plugins_url( SCF_Config::NAME ) . '/js/settings-datepicker.js',
			array( 'jquery', 'jquery-ui-datepicker' ),
			filemtime( plugin_dir_path( dirname( __FILE__ ) . '/../../js/settings-datepicker.js' ) ),
			true
		);
	}

	/**
	 * Getting the field.
	 *
	 * @param int    $index Field index.
	 * @param string $value The value.
	 * @return string
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
	 * Displaying the option fields in custom field settings page.
	 *
	 * @param int $group_key Group key.
	 * @param int $field_key Field key.
	 */
	public function display_field_options( $group_key, $field_key ) {
		$this->display_label_option( $group_key, $field_key );
		$this->display_name_option( $group_key, $field_key );
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
					<?php
					printf(
						esc_html( 'Prease see %sdateFormat%s', 'smart-custom-fields' ),
						'<a href="http://api.jqueryui.com/datepicker/#option-dateFormat" target="_blank">',
						'</a>'
					);
					?>
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
					<?php
					printf(
						esc_html( 'Prease see %smaxData%s', 'smart-custom-fields' ),
						'<a href="http://api.jqueryui.com/datepicker/#option-maxDate" target="_blank">',
						'</a>'
					);
					?>
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
					<?php
					printf(
						esc_html( 'Prease see %sminData%s', 'smart-custom-fields' ),
						'<a href="http://api.jqueryui.com/datepicker/#option-minDate" target="_blank">',
						'</a>'
					);
					?>
				</span>
			</td>
		</tr>
		<tr>
			<th><?php esc_html_e( 'Instruction', 'smart-custom-fields' ); ?></th>
			<td>
				<textarea name="<?php echo esc_attr( $this->get_field_name_in_setting( $group_key, $field_key, 'instruction' ) ); ?>"
					class="widefat" rows="5"><?php echo esc_attr( $this->get( 'instruction' ) ); ?></textarea>
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
	 * Return datepicker option with json_encode.
	 *
	 * @return string
	 */
	protected function get_data_js() {
		$js = array(
			'showMonthAfterYear' => true,
			'changeYear'         => true,
			'changeMonth'        => true,
		);

		// If locale is Japanese, change in Japanese notation
		if ( get_locale() === 'ja' ) {
			$js = array_merge(
				$js,
				array(
					'yearSuffix'      => '年',
					'dateFormat'      => 'yy-mm-dd',
					'dayNames'        => array(
						'日曜日',
						'月曜日',
						'火曜日',
						'水曜日',
						'木曜日',
						'金曜日',
						'土曜日',
					),
					'dayNamesMin'     => array(
						'日',
						'月',
						'火',
						'水',
						'木',
						'金',
						'土',
					),
					'dayNamesShort'   => array(
						'日曜',
						'月曜',
						'火曜',
						'水曜',
						'木曜',
						'金曜',
						'土曜',
					),
					'monthNames'      => array(
						'1月',
						'2月',
						'3月',
						'4月',
						'5月',
						'6月',
						'7月',
						'8月',
						'9月',
						'10月',
						'11月',
						'12月',
					),
					'monthNamesShort' => array(
						'1月',
						'2月',
						'3月',
						'4月',
						'5月',
						'6月',
						'7月',
						'8月',
						'9月',
						'10月',
						'11月',
						'12月',
					),
				)
			);
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
