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
	// Plugins -> Github.
	array(
		'node_args'  =>
			array(
				'title'  => \__( 'Github', 'dwp-cf' ),
				'parent' => 'cf-shortcuts-plugins',
				'id'     => 'cf-shortcuts-plugins-git',
				'href'   => \admin_url( 'admin.php?page=git-remote-updater', 'admin' ),
				'meta'   => array(
					'title' => \__( 'Link to', 'dwp-cf' )
							. ' '
							. \__( 'Git Remote Updater', 'dwp-cf' ),
				),
			),
		'visibility' => 'both',
		'dependency' => array(
			'plugin' => 'Git Updater',
		),
	),
	// Plugins -> Github -> Git Remote Updater.
	array(
		'node_args'  =>
			array(
				'title'  => \__( 'Remote Updater', 'dwp-cf' ),
				'parent' => 'cf-shortcuts-plugins-git',
				'id'     => 'cf-shortcuts-plugins-git-remote-updater',
				'href'   => \admin_url( 'admin.php?page=git-remote-updater', 'admin' ),
				'meta'   => array(
					'title' => \__( 'Link to', 'dwp-cf' )
							. ' '
							. \__( 'Git Remote Updater', 'dwp-cf' ),
				),
			),
		'visibility' => 'both',
		'dependency' => array(
			'plugin' => 'Git Remote Updater',
		),
	),
	// Plugins -> Github -> Git Updater: Install Plugin.
	array(
		'node_args'  =>
			array(
				'title'  => \__( 'Install plugin', 'dwp-cf' ),
				'parent' => 'cf-shortcuts-plugins-git',
				'id'     => 'cf-shortcuts-plugins-git-updater-plugin-install',
				'href'   => \admin_url( 'options-general.php?page=git-updater&tab=git_updater_install_plugin', 'admin' ),
				'meta'   => array(
					'title' => \__( 'Link to', 'dwp-cf' )
							. ' '
							. \__( 'Git Updater', 'dwp-cf' )
							. ': '
							. \__( 'Install Plugin', 'dwp-cf' ),
				),
			),
		'visibility' => 'both',
		'dependency' => array(
			'plugin' => 'Git Updater',
		),
	),
	// Plugins -> Github -> Git Updater: Install Theme.
	array(
		'node_args'  =>
			array(
				'title'  => \__( 'Install theme', 'dwp-cf' ),
				'parent' => 'cf-shortcuts-plugins-git',
				'id'     => 'cf-shortcuts-plugins-git-updater-theme-install',
				'href'   => \admin_url( 'options-general.php?page=git-updater&tab=git_updater_install_theme', 'admin' ),
				'meta'   => array(
					'title' => \__( 'Link to', 'dwp-cf' )
							. ' '
							. \__( 'Git Updater', 'dwp-cf' )
							. ': '
							. \__( 'Install Plugin', 'dwp-cf' ),
				),
			),
		'visibility' => 'both',
		'dependency' => array(
			'plugin' => 'Git Updater',
		),
	),
	// Plugins -> Github -> Git Updater: Github settings.
	array(
		'node_args'  =>
			array(
				'title'  => \__( 'Settings', 'dwp-cf' ),
				'parent' => 'cf-shortcuts-plugins-git',
				'id'     => 'cf-shortcuts-plugins-git-updater-settings',
				'href'   => \admin_url( 'options-general.php?page=git-updater&tab=git_updater_settings&subtab=github', 'admin' ),
				'meta'   => array(
					'title' => \__( 'Link to', 'dwp-cf' )
							. ' '
							. \__( 'Git Updater', 'dwp-cf' )
							. ': '
							. \__( 'Github settings', 'dwp-cf' ),
				),
			),
		'visibility' => 'both',
		'dependency' => array(
			'plugin' => 'Git Updater',
		),
	),
	// Plugins -> Github -> Git Updater: Remote management.
	array(
		'node_args'  =>
			array(
				'title'  => \__( 'Remote Management', 'dwp-cf' ),
				'parent' => 'cf-shortcuts-plugins-git',
				'id'     => 'cf-shortcuts-plugins-git-updater-remote-management',
				'href'   => \admin_url( 'options-general.php?page=git-updater&tab=git_updater_remote_management', 'admin' ),
				'meta'   => array(
					'title' => \__( 'Link to', 'dwp-cf' )
							. ' '
							. \__( 'Git Updater', 'dwp-cf' )
							. ': '
							. \__( 'Remote Management', 'dwp-cf' ),
				),
			),
		'visibility' => 'both',
		'dependency' => array(
			'plugin' => 'Git Updater',
		),
	),
);
