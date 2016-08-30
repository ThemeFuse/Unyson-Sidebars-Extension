<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

class FW_Option_Type_Sidebar_Picker extends FW_Option_Type {

	/**
	 * Option's unique type, used in option array in 'type' key
	 * @return string
	 */
	public function get_type() {
		return 'sidebar-picker';
	}

	protected function _enqueue_static( $id, $option, $data ) {
		$uri = fw_get_framework_directory_uri( '/extensions/sidebars/includes/option-type/' . $this->get_type() );

		wp_enqueue_style(
			'fw-option-type' . $this->get_type(),
			$uri . '/static/css/style.css',
			array(),
			fw()->manifest->get_version()
		);
		wp_enqueue_script(
			'fw-option-type' . $this->get_type(),
			$uri . '/static/js/sidebar-picker.js',
			array( 'jquery', 'fw-events' ),
			fw()->manifest->get_version(),
			true
		);

		fw()->backend->option_type('image-picker')->enqueue_static();
	}

	/**
	 * Generate option's html from option array
	 *
	 * @param string $id
	 * @param array $option Option array merged with _get_defaults()
	 * @param array $data {value => _get_value_from_input(), id_prefix => ..., name_prefix => ...}
	 *
	 * @return string HTML
	 * @internal
	 */
	protected function _render( $id, $option, $data ) {

		$data_positions_options = fw_ext( 'sidebars' )->is_missing_config() ? array() : fw_ext( 'sidebars' )->get_data_positions_options();

		$data_positions_options['choices'] = array_merge(
			array(
				'default' => array(
					'label' => false,
					'small' => fw()->extensions->get('sidebars')->locate_URI('/includes/option-type/sidebar-picker/static/images/default.png'),
					'data' => array(
						'colors' => 0,
					),
				),
			),
			$data_positions_options['choices']
		);

		return fw_render_view( fw_ext( 'sidebars' )->get_declared_path( '/includes/option-type/sidebar-picker/view.php' ), array(
			'data_positions_options' => $data_positions_options,
			'id'                     => $id,
			'sidebars'               => fw_ext( 'sidebars' )->get_sidebars(),
			'colors'                 => fw_ext( 'sidebars' )->get_allowed_places(),
			'data'                   => $data,
			'image_picker'           => fw_ext( 'sidebars' )->get_sidebars_for_select(),
			'attr'                   => $option['attr']

		), false );
	}

	/**
	 * Extract correct value for $option['value'] from input array
	 * If input value is empty, will be returned $option['value']
	 *
	 * @param array $option Option array merged with _get_defaults()
	 * @param array|string|null $input_value
	 *
	 * @return string|array|int|bool Correct value
	 * @internal
	 */
	protected function _get_value_from_input( $option, $input_value ) {
		return ( is_null( $input_value ) ? $option['value'] : $input_value );
	}

	/**
	 * Default option array
	 *
	 * This makes possible an option array to have required only one parameter: array('type' => '...')
	 * Other parameters are merged with array returned from this method
	 *
	 * @return array
	 *
	 * array(
	 *     'value' => '',
	 *     ...
	 * )
	 * @internal
	 */
	protected function _get_defaults() {
		return array(
			'value' => array(),
		);
	}

	public function _get_backend_width_type() {
		return 'full';
	}

}

FW_Option_Type::register( 'FW_Option_Type_Sidebar_Picker' );