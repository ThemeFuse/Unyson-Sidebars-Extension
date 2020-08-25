<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * Working with DB models and config on Frontend
 * @internal
 */
class _FW_Extension_Sidebars_Frontend {
	/** @var _FW_Extension_Sidebars_Config */
	private $config;

	private $current_page_preset;

	private $wp_option_sidebar_settings;

	private $after_widgets_init;

	public function __construct() {
		$this->wp_option_sidebar_settings = fw()->extensions->get( 'sidebars' )->get_fw_option_sidebars_settings_key();
		$this->config                     = new _FW_Extension_Sidebars_Config();
		$this->register_actions();
	}

	private function register_actions() {
		add_action( 'widgets_init', array( $this, '_fw_enable_replace' ) );
	}

	public function _fw_enable_replace() {
		$this->after_widgets_init = true;
	}

	public function replace_sidebars( $sidebars_widgets ) {
		if ( ! $this->after_widgets_init ) {
			return $sidebars_widgets;
		}

		$db_settings = $this->get_db();


		$places = array();
		if ( isset( $db_settings['allowed_places'] ) ) {
			$places = $db_settings['allowed_places'];
		}

		$preset = $this->get_current_page_preset();


		if ( false === empty( $places ) ) {
			foreach ( $places as $sidebar_id => $color ) {
				if ( isset( $preset['sidebars'][ $color ] ) ) {
					if ( isset( $sidebars_widgets[ $preset['sidebars'][ $color ] ] ) ) {
						$sidebars_widgets[ $sidebar_id ] = $sidebars_widgets[ $preset['sidebars'][ $color ] ];
					} else {
						unset( $sidebars_widgets[ $sidebar_id ] );
					}
				}

			}
		}

		return $sidebars_widgets;
	}

	/**
	 * @return array database data
	 */
	private function get_db() {
		$db = get_option( $this->wp_option_sidebar_settings );

		return ! empty( $db ) ? $db : array();
	}


	/**
	 * If DB has preset for current page position
	 * @return string | false
	 */
	public function get_preset_position() {
		$preset = $this->get_current_page_preset();

		return ! empty( $preset['position'] ) ? $preset['position'] : false;
	}

	/**
	 * @param $color string
	 *
	 * @return html string of rendered widgets for current page
	 */
	public function render_sidebar( $color ) {
		if ( ! in_array( $color, _FW_Extension_Sidebars_Config::$allowed_colors ) ) {
			return false;
		}

		$preset = $this->get_current_page_preset();

		//get available sidebar by color
		$sidebar = isset( $preset['sidebars'][ $color ] ) ? $preset['sidebars'][ $color ] : null;

		ob_start();

		//check if sidebar is active
		if ( ! empty( $sidebar ) ) {
			$sidebars_widgets = wp_get_sidebars_widgets();
			if ( ! empty( $sidebars_widgets[ $sidebar ] ) ) {
				dynamic_sidebar( $sidebar );
			} else {
				fw_render_view( fw()->extensions->get( 'sidebars' )->locate_path( '/views/frontend-no-widgets.php' ), array( 'sidebar_id' => $sidebar ), false );
			}
		}

		return ob_get_clean();
	}

	/**
	 * Generate current page requirements and return array with available sidebars for current page
	 */
	public function get_current_page_preset() {
		$result = $this->_fw_check_conditional_tags( 'first' );
		if ( $result ) {
			return $result;
		}

		// wooCommerce support
		if( function_exists( 'is_shop') && is_shop() ) {
			$data['type']     = $this->config->get_type_by_prefix( _FW_Extension_Sidebars_Config::POST_TYPES_PREFIX );
			$data['sub_type'] = 'page';
			$data['id']       = get_option( 'woocommerce_shop_page_id' );

			$result = $this->get_preset_sidebars( $data );

			if ( $result ) {
				return $result;
			}
		}

		// Custom post types archives support
		if( is_post_type_archive() ) {
			$data['type']     = $this->config->get_type_by_prefix( _FW_Extension_Sidebars_Config::ARCHIVES_PREFIX );
			$data['sub_type'] = get_post_type();
			$data['id']       = get_post_type();

			$result = $this->get_preset_sidebars( $data );

			if ( $result ) {
				return $result;
			}
		}

		//static page which show blog posts
		if ( is_home() && get_option( 'page_for_posts' ) != '0' && get_option( 'show_on_front' ) == 'page' ) {
			$data['type']     = $this->config->get_type_by_prefix( _FW_Extension_Sidebars_Config::POST_TYPES_PREFIX );
			$data['sub_type'] = 'page';
			$data['id']       = get_queried_object_id();

			$result = $this->get_preset_sidebars( $data );
			if ( $result ) {
				return $result;
			}
		}

		if ( is_singular() ) {
			$data['type']     = $this->config->get_type_by_prefix( _FW_Extension_Sidebars_Config::POST_TYPES_PREFIX );
			$data['sub_type'] = get_post_type();
			$data['id']       = get_queried_object_id();

			$result = $this->get_preset_sidebars( $data );
			if ( $result ) {
				return $result;
			}
		}

		if ( is_category() ) {
			$data['type']     = $this->config->get_type_by_prefix( _FW_Extension_Sidebars_Config::TAXONOMIES_PREFIX );
			$data['sub_type'] = 'category';
			$data['id']       = get_query_var( 'cat' );

			$result = $this->get_preset_sidebars( $data );
			if ( $result ) {
				return $result;
			}
		}

		if ( is_tag() ) {
			$data['type']     = $this->config->get_type_by_prefix( _FW_Extension_Sidebars_Config::TAXONOMIES_PREFIX );
			$data['sub_type'] = 'post_tag';
			$data['id']       = get_query_var( 'tag' );

			$result = $this->get_preset_sidebars( $data );
			if ( $result ) {
				return $result;
			}
		}

		if ( is_tax() ) {
			$term_obj         = get_term_by( 'slug', get_query_var( 'term' ), get_query_var( 'taxonomy' ) );
			$data['type']     = $this->config->get_type_by_prefix( _FW_Extension_Sidebars_Config::TAXONOMIES_PREFIX );
			$data['sub_type'] = $term_obj->taxonomy;
			$data['id']       = $term_obj->term_id;

			$result = $this->get_preset_sidebars( $data );
			if ( $result ) {
				return $result;
			}
		}

		$result = $this->_fw_check_conditional_tags( 'last' );
		if ( $result ) {
			return $result;
		}

		$data['type']     = $this->config->get_type_by_prefix( _FW_Extension_Sidebars_Config::DEFAULT_PREFIX );
		$data['sub_type'] = _FW_Extension_Sidebars_Config::DEFAULT_SUB_TYPE;
		$result           = $this->get_preset_sidebars( $data ); //return preset default for all pages
		return $result;
	}

	private function _fw_check_conditional_tags( $priority ) {
		$conditional_tags = $this->config->get_conditional_tags( $priority );

		foreach ( $conditional_tags as $key => $cond_tag ) {
			$function = null;
			if ( isset( $cond_tag['conditional_tag'] ) ) {
				$function = isset( $cond_tag['conditional_tag']['callback'] ) ? $cond_tag['conditional_tag']['callback'] : '';

				if ( is_callable( $function ) ) {
					$params = array();

					if ( isset( $cond_tag['conditional_tag']['params'] ) and is_array( $cond_tag['conditional_tag']['params'] ) ) {
						$params = $cond_tag['conditional_tag']['params'];
					}

					if ( call_user_func_array( $function, $params ) ) {
						$data['type']     = $this->config->get_type_by_prefix( _FW_Extension_Sidebars_Config::CONDITIONAL_TAGS_PREFIX );
						$data['sub_type'] = $key;

						$result = $this->get_preset_sidebars( $data );

						if ( $result ) {
							return $result;
						}
					}
				}
			} else {
				$function = $key;
				if ( is_callable( $function ) ) {
					if ( call_user_func( $function ) ) {
						$data['type']     = $this->config->get_type_by_prefix( _FW_Extension_Sidebars_Config::CONDITIONAL_TAGS_PREFIX );
						$data['sub_type'] = $key;

						$result = $this->get_preset_sidebars( $data );

						if ( $result ) {
							return $result;
						}
					}
				}
			}

		}
	}

	private function preset_timestamp_cmp( $a, $b ) {
		if ( $a['timestamp'] === $b['timestamp'] ) {
			return 0;
		}

		return ( $a['timestamp'] > $b['timestamp'] ) ? - 1 : 1;
	}

	/**
	 * Get avaible preset from DB by current page requirements
	 */
	private function get_preset_sidebars( $data ) {
		if ( $this->config->is_enabled_select_option( $data['type'], $data['sub_type'] ) ) {
			$settings = $this->get_db();
			if ( ! empty( $data['id'] ) ) {   //get by ids preset
				if ( isset( $settings['settings'][ $data['type'] ][ $data['sub_type'] ]['saved_ids'] ) ) { //check if id in saved_ids
					if ( in_array( $data['id'], $settings['settings'][ $data['type'] ][ $data['sub_type'] ]['saved_ids'] ) ) {
						$by_ids_presets = $settings['settings'][ $data['type'] ][ $data['sub_type'] ]['by_ids'];
						usort( $by_ids_presets, array( $this, 'preset_timestamp_cmp' ) );
						foreach ( $by_ids_presets as $preset_key => $preset ) {
							if ( in_array( $data['id'], $preset['ids'] ) ) {
								$this->current_page_preset = $preset;
								if ( isset( $this->current_page_preset['timestamp'] ) ) {
									unset( $this->current_page_preset['timestamp'] );
								}

								return $this->current_page_preset;
							}
						}
					}
				}
			}

			$this->current_page_preset = fw_akg( 'settings/' . $data['type'] . '/' . $data['sub_type'] . '/common', $settings, false );
			if ( isset( $this->current_page_preset['timestamp'] ) ) {
				unset( $this->current_page_preset['timestamp'] );
			}

			return $this->current_page_preset;
		}

		return false;
	}

}
