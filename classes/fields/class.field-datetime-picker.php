<?php
/**
 * Class file for Smart_Custom_Fields_Field_Datetime_picker.
 *
 * @author Toshihiro Kanai <i@miruc.co>
 * @package Smart_Custom_Fields
 */

/**
 * Class Smart_Custom_Fields_Field_Datetime_Picker.
 *
 * @since 4.x
 */
class Smart_Custom_Fields_Field_Datetime_Picker extends Smart_Custom_Fields_Field_Base {

	/**
	 * Set the required items
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
			'type'         => 'datetime_picker',
			'display-name' => __( 'Datetime picker', 'smart-custom-fields' ),
			'optgroup'     => 'other-fields',
		);
	}

	/**
	 * Set the non required items
	 *
	 * @return array
	 */
	protected function options() {
		return array(
			'date_format' => '',
			'max_date'    => '',
			'min_date'    => '',
			'time_24hr'   => '',
			'default'     => '',
			'instruction' => '',
			'notes'       => '',
		);
	}

	/**
	 * Loading resources for editor
	 */
	public function editor_enqueue_scripts() {
		wp_enqueue_style(
			'flatpickr-style',
			'//cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css',
			array()
		);
		wp_enqueue_script(
			'flatpickr-script',
			'//cdn.jsdelivr.net/npm/flatpickr',
			array(),
			true,
			true
		);
		wp_enqueue_script(
			SCF_Config::PREFIX . 'flatpickr-script',
			plugins_url( '../../js/settings-datetime-picker.js', __FILE__ ),
			array( 'flatpickr-script' ),
			false,
			true
		);
		$locale = $this->get_locale_name();
		if ( $locale ) {
			wp_enqueue_script(
				"flatpickr-lang-${locale}",
				"//npmcdn.com/flatpickr/dist/l10n/${locale}.js",
				array(),
				false,
				true
			);
		}
	}

	/**
	 * Loading resources for editor for custom field settings page
	 */
	public function settings_enqueue_scripts() {
		wp_enqueue_style(
			'flatpickr-style',
			'//cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css',
			array()
		);
		wp_enqueue_script(
			'flatpickr-script',
			'//cdn.jsdelivr.net/npm/flatpickr',
			array(),
			false,
			true
		);
		wp_enqueue_script(
			SCF_Config::PREFIX . 'flatpickr-script',
			plugins_url( '../../js/settings-datetime-picker.js', __FILE__ ),
			array( 'flatpickr-script' ),
			false,
			true
		);
		$locale = $this->get_locale_name();
		if ( $locale ) {
			wp_enqueue_script(
				"flatpickr-lang-${locale}",
				"//npmcdn.com/flatpickr/dist/l10n/${locale}.js",
				array(),
				false,
				true
			);
		}
	}

	/**
	 * Get the field
	 *
	 * @param int    $index Index number.
	 * @param string $value Value.
	 * @return string HTML content.
	 */
	public function get_field( $index, $value ) {
		$name     = $this->get_field_name_in_editor( $index );
		$disabled = $this->get_disable_attribute( $index );
		$data_js  = $this->get_data_js();

		return '<input
				type="text"
				name="' . esc_attr( $name ) . '"
				value="' . esc_attr( $value ) . '"
				class="' . esc_attr( SCF_Config::PREFIX . 'datetime_picker' ) . '"
				data-js=\'' . $data_js . '\'
				' . disabled( true, $disabled, false ) . '/>';
	}

	/**
	 * Displaying the option fields in custom field settings page
	 *
	 * @param int $group_key Group key.
	 * @param int $field_key Field key.
	 */
	public function display_field_options( $group_key, $field_key ) {
		$this->display_label_option( $group_key, $field_key );
		$this->display_name_option( $group_key, $field_key );
		?>
		<tr>
			<th><?php esc_html_e( 'Date Format', 'smart-custom-fields' ); ?></th>
			<td>
				<input type="text"
					name="<?php echo esc_attr( $this->get_field_name_in_setting( $group_key, $field_key, 'date_format' ) ); ?>"
					class="widefat"
					value="<?php echo esc_attr( $this->get( 'date_format' ) ); ?>"
				/><br />
				<span class="<?php echo esc_attr( SCF_Config::PREFIX ); ?>notes">
					<?php esc_html_e( 'e.g. Y-m-d', 'smart-custom-fields' ); ?>
					<?php
					printf(
						/* translators: 1: Opening of a tag, 2: Closing a tag. */
						esc_html__( '%1$sSee here%2$s for more information.', 'smart-custom-fields' ),
						'<a href="https://flatpickr.js.org/options/" target="_blank">',
						'</a>'
					);
					esc_html_e(
						'This datetime picker currently does not include the timezone support, therefore you need to include some information on the instruction field below to enforce everyone to use the same timezone. The value returned by this field is always a plain text of date string.',
						'smart-custom-fields'
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
					<?php esc_html_e( 'String or Date.', 'smart-custom-fields' ); ?>
					<?php
					printf(
						/* translators: 1: Opening of a tag, 2: Closing a tag. */
						esc_html__( '%1$sSee here%2$s for more information.', 'smart-custom-fields' ),
						'<a href="https://flatpickr.js.org/examples/#mindate-and-maxdate" target="_blank">',
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
					<?php esc_html_e( 'String or Date.', 'smart-custom-fields' ); ?>
					<?php
					printf(
						/* translators: 1: Opening of a tag, 2: Closing a tag. */
						esc_html__( '%1$sSee here%2$s for more information.', 'smart-custom-fields' ),
						'<a href="https://flatpickr.js.org/examples/#mindate-and-maxdate" target="_blank">',
						'</a>'
					);
					?>
				</span>
			</td>
		</tr>
		<tr>
			<th><?php esc_html_e( '24 Hour', 'smart-custom-fields' ); ?></th>
			<td>
				<input type="text"
					name="<?php echo esc_attr( $this->get_field_name_in_setting( $group_key, $field_key, 'time_24hr' ) ); ?>"
					class="widefat"
					value="<?php echo esc_attr( $this->get( 'time_24hr' ) ); ?>"
				/><br />
				<span class="<?php echo esc_attr( SCF_Config::PREFIX ); ?>notes">
					<?php esc_html_e( 'Use 24 hour or not. Provide boolean value, true or false (Default false).', 'smart-custom-fields' ); ?>
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
	 * Return content of data-js to pass data to front-end.
	 *
	 * @return false|mixed|string
	 */
	private function get_data_js() {
		$data = array();

		if ( $this->get_locale_name() ) {
			$data['locale'] = $this->get_locale_name();
		}

		if ( $this->get( 'date_format' ) ) {
			$data['dateFormat'] = $this->get( 'date_format' );
		}

		if ( $this->get( 'max_date' ) ) {
			$data['maxDate'] = $this->get( 'max_date' );
		}

		if ( $this->get( 'min_date' ) ) {
			$data['minDate'] = $this->get( 'min_date' );
		}

		if ( $this->get( 'time_24hr' ) ) {
			$data['time_24hr'] = $this->get( 'time_24hr' );
		}

		$data = apply_filters( SCF_Config::PREFIX . 'datetime_picker_data', $data );

		return json_encode( $data );
	}

	/**
	 * Return locale name for flatpickr.
	 *
	 * @return false|string $locale
	 */
	private function get_locale_name() {

		/**
		 * The locale list is hardcoded here. Because of this, when flatpickr adds support for new language it's required to update the list.
		 *
		 * The list is from: https://github.com/flatpickr/flatpickr/blob/master/src/l10n/index.ts
		 * Or at https://github.com/flatpickr/flatpickr/tree/master/src/l10n ,
		 * run the following script and copy the result:
		 * const list = document.querySelectorAll('.files .content > span > a'); const items = []; for (const item of list) { items.push(item.innerHTML.split('.')[0]) }; console.log(items)
		 */
		$supported_locales = array(
			'ar',
			'at',
			'be',
			'bg',
			'bn',
			'cat',
			'cs',
			'cy',
			'da',
			'de',
			'eo',
			'es',
			'et',
			'fa',
			'fi',
			'fo',
			'fr',
			'gr',
			'he',
			'hi',
			'hr',
			'hu',
			'id',
			'it',
			'ja',
			'km',
			'ko',
			'kz',
			'lt',
			'lv',
			'mk',
			'mn',
			'ms',
			'my',
			'nl',
			'no',
			'pa',
			'pl',
			'pt',
			'ro',
			'ru',
			'si',
			'sk',
			'sl',
			'sq',
			'sr',
			'sv',
			'th',
			'tr',
			'uk',
			'vn',
			'zh',
		);

		$wp_locale = get_locale();
		if ( strpos( $wp_locale, '_' ) ) {
			$_user_lang = explode( $wp_locale, '_' );
			$user_lang  = $_user_lang[0];
		} else {
			$user_lang = $wp_locale;
		}

		if ( in_array( $user_lang, $supported_locales, true ) ) {
			return $user_lang;
		}
		return false;
	}
}
