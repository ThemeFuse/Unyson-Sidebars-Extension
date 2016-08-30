<?php if (!defined('FW')) die('Forbidden');

class FW_Option_Storage_Type_FW_Ext_Sidebar_Picker extends FW_Option_Storage_Type {
	public function get_type()
	{
		return 'fw-ext-sidebar-picker';
	}

	protected function _load($id, array $option, $value, array $params)
	{
		return fw_ext( 'sidebars' )->get_specific_preset_by_id(
			isset($params['post-id']) ? $params['post-id'] : get_the_ID()
		);
	}

	protected function _save($id, array $option, $value, array $params)
	{
		$ext = fw_ext( 'sidebars' ); /** @var FW_Extension_Sidebars $ext */

		if (empty($value)) {
			return $value;
		}

		if ( $value['position'] === 'default' ) {
			$ext->delete_sidebar_preset( array(
				'slug' => false,
				'preset' => $value['preset']
			) );
		} else {
			$ext->save_sidebar_settings( $value );
		}

		return array();
	}
}