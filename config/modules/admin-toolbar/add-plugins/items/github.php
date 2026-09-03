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
				'title'  => \__( 'Github', 'core-functionality-dwp' ),
				'parent' => 'cf-shortcuts-plugins',
				'id'     => 'cf-shortcuts-plugins-git',
				'href'   => \admin_url( 'admin.php?page=git-remote-updater', 'admin' ),
				'meta'   => array(
					'title' => \__( 'Link to', 'core-functionality-dwp' )
							. ' '
							. \__( 'Git Remote Updater', 'core-functionality-dwp' ),
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
				'title'  => \__( 'Remote Updater', 'core-functionality-dwp' ),
				'parent' => 'cf-shortcuts-plugins-git',
				'id'     => 'cf-shortcuts-plugins-git-remote-updater',
				'href'   => \admin_url( 'admin.php?page=git-remote-updater', 'admin' ),
				'meta'   => array(
					'title' => \__( 'Link to', 'core-functionality-dwp' )
							. ' '
							. \__( 'Git Remote Updater', 'core-functionality-dwp' ),
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
				'title'  => \__( 'Install plugin', 'core-functionality-dwp' ),
				'parent' => 'cf-shortcuts-plugins-git',
				'id'     => 'cf-shortcuts-plugins-git-updater-plugin-install',
				'href'   => \admin_url( 'options-general.php?page=git-updater&tab=git_updater_install_plugin', 'admin' ),
				'meta'   => array(
					'title' => \__( 'Link to', 'core-functionality-dwp' )
							. ' '
							. \__( 'Git Updater', 'core-functionality-dwp' )
							. ': '
							. \__( 'Install Plugin', 'core-functionality-dwp' ),
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
				'title'  => \__( 'Install theme', 'core-functionality-dwp' ),
				'parent' => 'cf-shortcuts-plugins-git',
				'id'     => 'cf-shortcuts-plugins-git-updater-theme-install',
				'href'   => \admin_url( 'options-general.php?page=git-updater&tab=git_updater_install_theme', 'admin' ),
				'meta'   => array(
					'title' => \__( 'Link to', 'core-functionality-dwp' )
							. ' '
							. \__( 'Git Updater', 'core-functionality-dwp' )
							. ': '
							. \__( 'Install Plugin', 'core-functionality-dwp' ),
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
				'title'  => \__( 'Settings', 'core-functionality-dwp' ),
				'parent' => 'cf-shortcuts-plugins-git',
				'id'     => 'cf-shortcuts-plugins-git-updater-settings',
				'href'   => \admin_url( 'options-general.php?page=git-updater&tab=git_updater_settings&subtab=github', 'admin' ),
				'meta'   => array(
					'title' => \__( 'Link to', 'core-functionality-dwp' )
							. ' '
							. \__( 'Git Updater', 'core-functionality-dwp' )
							. ': '
							. \__( 'Github settings', 'core-functionality-dwp' ),
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
				'title'  => \__( 'Remote Management', 'core-functionality-dwp' ),
				'parent' => 'cf-shortcuts-plugins-git',
				'id'     => 'cf-shortcuts-plugins-git-updater-remote-management',
				'href'   => \admin_url( 'options-general.php?page=git-updater&tab=git_updater_remote_management', 'admin' ),
				'meta'   => array(
					'title' => \__( 'Link to', 'core-functionality-dwp' )
							. ' '
							. \__( 'Git Updater', 'core-functionality-dwp' )
							. ': '
							. \__( 'Remote Management', 'core-functionality-dwp' ),
				),
			),
		'visibility' => 'both',
		'dependency' => array(
			'plugin' => 'Git Updater',
		),
	),
);
