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

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	// Site.
	array(
		'node_args'  =>
			array(
				'title'  => \__( 'Site', 'core-functionality' ),
				'parent' => 'site-name',
				'id'     => 'cf-shortcuts-site',
				'href'   => '#',
			),
		'visibility' => 'both',
	),
	// Site -> Site Editor.
	array(
		'node_args'  =>
			array(
				'title'  => \__( 'Site editor', 'core-functionality' ),
				'parent' => 'cf-shortcuts-site',
				'id'     => 'cf-shortcuts-site-editor',
				'href'   => \admin_url( 'site-editor.php', 'admin' ),
				'meta'   => array(
					'title' => \__( 'Link to', 'core-functionality' )
							. ' '
							. \__( 'Site editor', 'core-functionality' ),
				),
			),
		'visibility' => 'both',
		'dependency' => array(
			'fse' => 'fse',
		),
	),
	// Site -> Customizer.
	array(
		'node_args'  =>
			array(
				'title'  => \__( 'Customizer', 'core-functionality' ),
				'parent' => 'cf-shortcuts-site',
				'id'     => 'cf-shortcuts-site-customize',
				'href'   => \admin_url( 'customize.php', 'admin' ),
				'meta'   => array(
					'title' => \__( 'Link to', 'core-functionality' )
							. ' '
							. \__( 'Customizer', 'core-functionality' ),
				),
			),
		'visibility' => 'both',
	),
	// Site -> Widgets.
	array(
		'node_args'  =>
			array(
				'title'  => \__( 'Widgets', 'core-functionality' ),
				'parent' => 'cf-shortcuts-site',
				'id'     => 'cf-shortcuts-site-widgets',
				'href'   => \admin_url( 'widgets.php', 'admin' ),
				'meta'   => array(
					'title' => \__( 'Link to', 'core-functionality' )
							. ' '
							. \__( 'Widgets', 'core-functionality' ),
				),
			),
		'visibility' => 'both',
	),
	// Site -> Menus.
	array(
		'node_args'  =>
			array(
				'title'  => \__( 'Menus', 'core-functionality' ),
				'parent' => 'cf-shortcuts-site',
				'id'     => 'cf-shortcuts-site-menus',
				'href'   => \admin_url( 'nav-menus.php', 'admin' ),
				'meta'   => array(
					'title' => \__( 'Link to', 'core-functionality' )
							. ' '
							. \__( 'Menus', 'core-functionality' ),
				),
			),
		'visibility' => 'both',
	),
	// Site -> Themes.
	array(
		'node_args'  =>
			array(
				'title'  => \__( 'Themes', 'core-functionality' ),
				'parent' => 'cf-shortcuts-site',
				'id'     => 'cf-shortcuts-site-themes',
				'href'   => \admin_url( 'themes.php', 'admin' ),
				'meta'   => array(
					'title' => \__( 'Link to', 'core-functionality' )
							. ' '
							. \__( 'Themes', 'core-functionality' ),
				),
			),
		'visibility' => 'both',
	),
	// Site -> Genesis.
	array(
		'node_args'  =>
			array(
				'title'  => \__( 'Genesis Framework', 'core-functionality' ),
				'parent' => 'cf-shortcuts-site',
				'id'     => 'cf-shortcuts-site-genesis',
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
	// Site -> Updraft Backups.
	array(
		'node_args'  =>
			array(
				'title'  => \__( 'Updraft Backups', 'core-functionality' ),
				'parent' => 'cf-shortcuts-site',
				'id'     => 'cf-shortcuts-site-backups',
				'href'   => \admin_url( 'options-general.php?page=updraftplus', 'admin' ),
				'meta'   => array(
					'title' => \__( 'Link to', 'core-functionality' )
							. ' '
							. \__( 'Updraft Backups', 'core-functionality' ),
				),
			),
		'visibility' => 'both',
		'dependency' => array(
			'plugin' => 'UpdraftPlus - Backup/Restore',
		),
	),
);
