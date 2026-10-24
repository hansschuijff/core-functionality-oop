<?php
/**
 * Runtime configuration defaults for the quick link admin-toolbar menu.
 *
 * Returns a list (array) of link attributes and dependant plugins
 *
 * @package     DeWittePrins\CoreFunctionality
 * @since       1.0.0
 * @author      Hans Schuijff
 * @link        https://dewitteprins.nl
 * @license     GNU-2.0+
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	// Settings.
	array(
		'node_args'  =>
			array(
				'title'  => \__( 'Settings', 'core-functionality' ),
				'parent' => 'site-name',
				'id'     => 'cf-shortcuts-settings',
				'href'   => '#',
			),
		'visibility' => 'both',
	),
	// Settings -> Themes.
	array(
		'node_args'  =>
			array(
				'title'  => \__( 'Themes', 'core-functionality' ),
				'parent' => 'cf-shortcuts-settings',
				'id'     => 'cf-shortcuts-settings-themes',
				'href'   => \admin_url( 'themes.php', 'admin' ),
				'meta'   => array(
					'title' => \__( 'Link to', 'core-functionality' )
							. ' '
							. \__( 'Themes', 'core-functionality' ),
				),
			),
		'visibility' => 'both',
	),
	// Settings -> Genesis Settings.
	array(
		'node_args'  =>
			array(
				'title'  => \__( 'Genesis Framework', 'core-functionality' ),
				'parent' => 'cf-shortcuts-settings',
				'id'     => 'cf-shortcuts-settings-genesis',
				'href'   => \admin_url( 'admin.php?page=genesis', 'admin' ),
				'meta'   => array(
					'title' => \__( 'Link to', 'core-functionality' )
							. ' '
							. \__( 'Genesis Framework', 'core-functionality' ),
				),
			),
		'visibility' => 'both',
		'dependency' => array(
			'theme' => 'genesis',
		),
	),
	// Settings -> Menus.
	array(
		'node_args'  =>
			array(
				'title'  => \__( 'Menus', 'core-functionality' ),
				'parent' => 'cf-shortcuts-settings',
				'id'     => 'cf-shortcuts-settings-menus',
				'href'   => \admin_url( 'nav-menus.php', 'admin' ),
				'meta'   => array(
					'title' => \__( 'Link to', 'core-functionality' )
							. ' '
							. \__( 'Menus', 'core-functionality' ),
				),
			),
		'visibility' => 'both',
	),
	// Settings -> Widgets.
	array(
		'node_args'  =>
			array(
				'title'  => \__( 'Widgets', 'core-functionality' ),
				'parent' => 'cf-shortcuts-settings',
				'id'     => 'cf-shortcuts-settings-widgets',
				'href'   => \admin_url( 'widgets.php', 'admin' ),
				'meta'   => array(
					'title' => \__( 'Link to', 'core-functionality' )
							. ' '
							. \__( 'Widgets', 'core-functionality' ),
				),
			),
		'visibility' => 'both',
	),
	// Settings -> Shared Counts Settings.
	array(
		'node_args'  =>
			array(
				'title'  => \__( 'Shared Counts', 'core-functionality' ),
				'parent' => 'cf-shortcuts-settings',
				'id'     => 'cf-shortcuts-settings-social-share',
				'href'   => \admin_url( 'options-general.php?page=shared_counts_options', 'admin' ),
				'meta'   => array(
					'title' => \__( 'Link to', 'core-functionality' )
							. ' '
							. \__( 'Shared Counts', 'core-functionality' ),
				),
			),
		'visibility' => 'both',
		'dependency' => array(
			'plugin' => 'Shared Counts',
		),
	),
	// Settings -> Broken Links Checker.
	array(
		'node_args'  =>
			array(
				'title'  => \__( 'Broken Links Checker', 'core-functionality' ),
				'parent' => 'cf-shortcuts-settings',
				'id'     => 'cf-shortcuts-settings-broken-links',
				'href'   => \admin_url( 'tools.php?page=view-broken-links', 'admin' ),
				'meta'   => array(
					'title' => \__( 'Link to', 'core-functionality' )
								. ' '
								. \__( 'Broken Links Checker', 'core-functionality' ),
				),
			),
		'visibility' => 'both',
		'dependency' => array(
			'plugin' => 'Broken Link Checker',
		),
	),
	// Settings -> Widgets.
	array(
		'node_args'  =>
			array(
				'title'  => \__( 'Core Functionality', 'core-functionality' ),
				'parent' => 'cf-shortcuts-settings',
				'id'     => 'cf-shortcuts-settings-core-functionality',
				'href'   => \admin_url( 'options-general.php?page=core-functionality', 'admin' ),
				'meta'   => array(
					'title' => \__( 'Link to', 'core-functionality' )
							. ' '
							. \__( 'Core functionality settings', 'core-functionality' ),
				),
			),
		'visibility' => 'both',
	),
);
