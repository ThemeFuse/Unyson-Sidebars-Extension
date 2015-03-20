<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

$manifest = array();

$manifest['name']         = __( 'Sidebars', 'fw' );
$manifest['description']  = __( 'Brings a new layer of customization freedom to your website by letting you add more than one sidebar to a page, or different sidebars on different pages.', 'fw' );
$manifest['version']      = '1.0.2';
$manifest['display']      = true;
$manifest['standalone']   = true;
$manifest['requirements'] = array(
	'framework' => array(
		// Requires Unyson minimum version 2.2.2, as in that version was solved the bug with children extension requirements when activate an extension
		'min_version' => '2.2.2',
	)
);

$manifest['github_update'] = 'ThemeFuse/Unyson-Sidebars-Extension';
