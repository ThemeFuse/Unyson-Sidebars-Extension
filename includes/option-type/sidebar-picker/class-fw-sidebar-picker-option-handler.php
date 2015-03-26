<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

class FW_Sidebar_Picker_Option_Handler implements FW_Option_Handler {

	function get_option_value( $option_id, $option, $data = array() ) {
		return fw_ext( 'sidebars' )->get_specific_preset_by_id( get_the_ID() );
	}

	function save_option_value( $option_id, $option, $value, $data = array() ) {
		$settings = FW_Request::POST( 'fw_options/' . $option_id );
		fw_ext( 'sidebars' )->save_sidebar_settings( $settings );
	}
}