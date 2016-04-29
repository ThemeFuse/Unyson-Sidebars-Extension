<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

$manifest = array();

$manifest['name']         = __( 'Sidebars', 'fw' );
$manifest['description']  = __(
	'Brings a new layer of customization freedom to your website by letting you add more than one sidebar to a page,'
	.' or different sidebars on different pages.',
	'fw'
);
$manifest['version']      = '1.0.8';
$manifest['display']      = true;
$manifest['standalone']   = true;
$manifest['requirements'] = array(
	'framework' => array(
		'min_version' => '2.2.2', // in this version was added option handler
	)
);

$manifest['github_update'] = 'ThemeFuse/Unyson-Sidebars-Extension';
