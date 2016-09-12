<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

class FW_Extension_Sidebars extends FW_Extension {
	/** @var _FW_Extension_Sidebars_Backend */
	private $backend;

	/** @var _FW_Extension_Sidebars_Frontend */
	private $frontend;

	/**
	 * key for saving/get data from wp_option
	 */
	public function get_fw_option_sidebars_settings_key() {
		$current_theme = wp_get_theme();

		return $current_theme->get_stylesheet() . '-fw-sidebars-options';
	}


	public function is_missing_config() {
		$positions = $this->backend->config->get_sidebar_positions();
		if ( empty( $positions ) or false === is_array( $positions ) ) {
			return true;
		}

		return false;
	}

	public function get_sidebars_for_select() {
		$sidebars  = $this->get_sidebars();
		$collector = array();
		foreach ( $sidebars as $sidebar ) {
			$collector[ $sidebar->get_id() ] = $sidebar->get_name();
		}

		return array(
			'choices' => $collector
		);
	}

	public function save_sidebar_settings( $settings ) {
		return $this->backend->save_sidebar_settings( $settings );
	}

	/**
	 * @param $args
	 * @return array
	 * @since 1.0.10
	 */
	public function delete_sidebar_preset( $args ) {
		return $this->backend->remove_preset( $args );
	}

	/**
	 * @internal
	 */
	protected function _init() {
		$this->backend = new _FW_Extension_Sidebars_Backend();

		$this->backend->init_sidebars();

		add_filter( 'fw_post_options', array( $this, '_admin_filter_render_sidebar_picker' ), 10, 2 );

		if ( is_admin() ) {
			$this->add_admin_actions();
		} else {
			$this->add_theme_actions();
		}

		add_action('fw_option_types_init', array($this, '_action_option_types_init'));
		add_action('fw:option-storage-types:register', array($this, '_action_register_option_storage_types'));
	}

	public function _action_option_types_init() {
		require_once dirname(__FILE__) .'/includes/option-type/sidebar-picker/class-fw-option-type-sidebar-picker.php';
	}

	public function _action_register_option_storage_types(_FW_Option_Storage_Type_Register $register) {
		require_once dirname(__FILE__) .'/includes/option-storage-type/class-fw-option-storage-type-fw-ext-sidebar-picker.php';
		$register->register(new FW_Option_Storage_Type_FW_Ext_Sidebar_Picker());
	}

	private function add_theme_actions() {
		if ( $this->is_missing_config() ) {
			add_filter( 'sidebars_widgets', array( $this->get_frontend_instance(), 'replace_sidebars' ) );

		}
	}

	private function  add_admin_actions() {
		add_action( 'admin_enqueue_scripts', array( $this, '_admin_action_enqueue_scripts' ) );
		add_action( 'sidebar_admin_page', array( $this, '_admin_action_render_partial' ) );

		if ( current_user_can( 'edit_theme_options' ) ) {
			add_action( 'wp_ajax_add_new_sidebar_ajax', array( $this, '_admin_action_add_new_sidebar_ajax' ) );
			add_action( 'wp_ajax_sidebar_autocomplete_ajax', array($this, '_admin_action_sidebar_autocomplete_ajax') );
			add_action( 'wp_ajax_save_sidebar_preset_ajax', array( $this, '_admin_action_save_sidebar_preset_ajax' ) );
			add_action( 'wp_ajax_remove_sidebar_preset_ajax', array($this, '_admin_action_remove_sidebar_preset_ajax') );
			add_action( 'wp_ajax_delete_sidebar_ajax', array( $this, '_admin_action_delete_sidebar_ajax' ) );
			add_action( 'wp_ajax_load_sidebar_preset_ajax', array( $this, '_admin_action_load_sidebar_preset_ajax' ) );
		}
	}

	public function _admin_filter_render_sidebar_picker($options, $post_type) {
		$post_type_array = $this->get_config('post_types_support');

		if ($this->get_config('show_in_post_types') === true) {
			if ( ! empty($post_type_array) && ! in_array($post_type, $post_type_array) ) {
				return $options;
			}

			return array_merge($options, array(
				'sidebar-picker' => array(
					'title'   => __('Sidebar Picker', 'fw'),
					'type'    => 'box',
					'context' => 'side',
					'options' => array(
						'sidebar' => array(
							'type'          => 'sidebar-picker',
							'label'         => false,
							'fw-storage'    => 'fw-ext-sidebar-picker',
						)
					),
				),
			));
		}

		return $options;
	}

	/**
	 * @internal
	 */
	public function _admin_action_enqueue_scripts( $hook ) {
		if ( 'widgets.php' === $hook ) {
			wp_enqueue_style(
				'fw-extension-' . $this->get_name() . '-css',
				$this->get_declared_URI( '/static/css/sidebar.css' ),
				array( 'fw', 'fw-selectize', 'fw-backend-options' ),
				fw()->manifest->get_version()
			);

			wp_enqueue_script( 'fw-extension-' . $this->get_name() . '-autocomplete-js',
				$this->get_declared_URI( '/static/js/sidebar-autocomplete.js' ),
				array( 'fw-events', 'jquery', 'jquery-ui-autocomplete', 'fw' ),
				fw()->manifest->get_version()
			);
			wp_localize_script( 'fw-extension-' . $this->get_name() . '-autocomplete-js', 'noMatchesFoundMsg', __( 'No matches found', 'fw' ) );

			wp_enqueue_script( 'fw-extension-' . $this->get_name() . '-general-js',
				$this->get_declared_URI( '/static/js/sidebar-general.js' ),
				array( 'fw-events', 'jquery', 'fw', 'fw-selectize', 'jquery-ui-tabs' ),
				fw()->manifest->get_version()
			);
			wp_localize_script( 'fw-extension-' . $this->get_name() . '-general-js', 'PhpVar', array(
				'confirmMessage'        => __( 'Do you realy want to change without saving?', 'fw' ),
				'dynamicSidebars'       => $this->backend->get_dynamic_sidebars_ids(),
				'missingIdMessage'      => __( 'Missing ID. Check that you provided all mandatory data.', 'fw' ),
				'createdTabName'        => __( 'Created', 'fw' ),
				'groupedTabDesc'        => __( '(For Grouped Pages)', 'fw' ),
				'specificTabDesc'       => __( '(For Specific Pages)', 'fw' ),
				'missingSidebarName'    => __( 'No sidebar name specified', 'fw' ),
				'newSidebarPlaceholder' => __( 'Sidebar Name', 'fw' ),
				'newSidebarLabel'       => __( 'New Sidebar', 'fw' ),
				'addSidebarButtonTxt'   => __( 'Add', 'fw' )
			) );
		}
	}

	/**
	 * @internal
	 */
	public function _admin_action_delete_sidebar_ajax() {
		$sidebar_id = FW_Request::POST( 'sidebar' );
		$result     = $this->backend->delete_sidebar( $sidebar_id );
		$this->ajax_response( $result );
	}

	/**
	 * @internal
	 */
	public function _admin_action_load_sidebar_preset_ajax() {
		$params = FW_Request::POST( 'params' );
		$result = $this->backend->get_preset( $params );
		$this->ajax_response( $result );
	}

	/**
	 * @internal
	 */
	public function _admin_action_remove_sidebar_preset_ajax() {
		$args   = FW_Request::POST( 'data' );
		$result = $this->backend->remove_preset( $args );
		$this->ajax_response( $result );
	}

	/**
	 * @internal
	 */
	public function _admin_action_save_sidebar_preset_ajax() {
		$settings = FW_Request::POST( 'settings' );
		$result   = $this->backend->save_sidebar_settings( $settings );
		$this->ajax_response( $result );
	}

	/**
	 * @internal
	 */
	public function _admin_action_sidebar_autocomplete_ajax() {
		$search_type = FW_Request::POST( 'searchType' );
		$search_term = FW_Request::POST( 'searchTerm' );

		$result = $this->backend->get_autocomplete_results( $search_type, $search_term );
		$this->ajax_response( $result );
	}

	public function get_data_positions_options() {
		$sidebar_positions = $this->backend->config->get_sidebar_positions();

		$choices = array();
		foreach ( $sidebar_positions as $key => $position ) {
			$choices[ $key ] = array(
				'label' => false,
				'small' => $position['icon_url'],
				'data'  => array(
					'colors' => fw_akg( _FW_Extension_Sidebars_Config::SIDEBARS_NR_KEY, $position )
				)
			);
		}

		$data_positions_options = array(
			'type'    => 'image-picker',
			'choices' => $choices,
			'value'   => '',
			'attr'    => array( 'class' => 'fw-ext-sidebars-positions' )
		);

		return $data_positions_options;
	}

	/**
	 * Render partial on widgets.php page
	 * @internal
	 */
	public function _admin_action_render_partial() {
		$specific_options = array(
			'type'    => 'select',
			'choices' => $this->backend->config->get_specific_labels(),
			'value'   => ''
		);

		$grouped_options = array(
			'type'    => 'select',
			'choices' => $this->backend->config->get_grouped_labels(),
			'value'   => ''
		);

		$created_sidebars = $this->backend->get_presets_sidebars();

		$sidebars = $this->backend->get_all_sidebars();

		echo $this->render_view( 'backend-main-view', array(
			'grouped_options'        => $grouped_options,  //options for select grouped pages tab
			'specific_options'       => $specific_options, //options for select specific page tab
			'created_sidebars'       => $created_sidebars, //used for removable items on created tab
			'data_positions_options' => $this->get_data_positions_options(), //used for image-picker
			'sidebars'               => $sidebars, //used for selectize options on grouped and specific tabs
		) );
	}

	public function get_presets_sidebars() {
		return $this->backend->get_presets_sidebars();
	}

	public function get_sidebars() {
		return $this->backend->get_all_sidebars();
	}

	/**
	 * @internal
	 */
	public function _admin_action_add_new_sidebar_ajax() {
		$name   = FW_Request::POST( 'name' );
		$result = $this->backend->save_new_sidebar( $name );
		$this->ajax_response( $result );
	}

	/**
	 * return standards WP AJAX responses
	 */
	private function ajax_response( $result ) {
		if ( isset( $result['status'] ) ) {
			if ( $result['status'] ) {
				unset( $result['status'] );
				wp_send_json_success( $result );
			} else {
				unset( $result['status'] );
				wp_send_json_error( $result );
			}
		} else {
			wp_send_json( $result );
		}
	}

	public function get_specific_preset_by_id( $id ) {
		return $this->backend->get_specific_preset_by_id( $id );
	}

	private function get_frontend_instance() {
		if ( ! $this->frontend ) {
			$this->frontend = new _FW_Extension_Sidebars_Frontend();
		}

		return $this->frontend;
	}

	public function render_sidebar( $color ) {
		return $this->get_frontend_instance()->render_sidebar( $color );
	}

	public function get_current_preset() {
		return $this->get_frontend_instance()->get_current_page_preset();
	}

	/**
	 * @since 1.0.6
	 */
	public function get_current_position() {
		return $this->get_frontend_instance()->get_preset_position();
	}

	/**
	 * Old method with typo in name.
	 * We didn't deleted it because there may be someone that is calling it (because it is public)
	 * @deprecated
	 */
	public function get_current_positon() {
		return $this->get_current_position();
	}

	/**
	 * @return array
	 */
	public function get_allowed_places() {
		return $this->backend->_fw_get_allowed_places();
	}

	/**
	 * {@inheritdoc}
	 */
	public function _get_link() {
		return self_admin_url( 'widgets.php' );
	}
}
