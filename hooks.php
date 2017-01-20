<?php

/**
 * Add ability to WP_Query to find out posts by title for the sidebars auto-complete
 * @internal
 */
function _filter_fw_ext_sidebars_title_like_posts_where( $where, $wp_query ) {
	/**
	 * @var WPDB $wpdb
	 */
	global $wpdb;

	if ( $post_title_like = $wp_query->get( 'fw_ext_sidebars_post_title_like' ) ) {
		$where .= $wpdb->prepare(' AND ' . $wpdb->posts . '.post_title LIKE %s', '%'. $wpdb->esc_like( $post_title_like ) .'%' );
	}

	return $where;
}
add_filter( 'posts_where', '_filter_fw_ext_sidebars_title_like_posts_where', 10, 2);

function _filter_fw_ext_sidebars_ext_backups_db_restore_option_names_replace($option_names, $params) {
	if (isset($params['stylesheet'])) {
		/** @see FW_Extension_Sidebars::get_fw_option_sidebars_settings_key() */
		$option_names[$params['stylesheet'] . '-fw-sidebars-options'] = get_stylesheet() . '-fw-sidebars-options';
	}

	return $option_names;
}
add_filter(
	'fw_ext_backups_db_restore_option_names_replace',
	'_filter_fw_ext_sidebars_ext_backups_db_restore_option_names_replace',
	10, 2
);