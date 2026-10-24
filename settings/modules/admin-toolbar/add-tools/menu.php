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
	// Tools.
	array(
		'node_args'  =>
			array(
				'title'  => \__( 'Tools', 'core-functionality' ),
				'parent' => 'site-name',
				'id'     => 'cf-shortcuts-tools',
				'href'   => '#',
			),
		'visibility' => 'both',
	),
	// Tools -> Updraft Backups.
	array(
		'node_args'  =>
			array(
				'title'  => \__( 'Updraft Backups', 'core-functionality' ),
				'parent' => 'cf-shortcuts-tools',
				'id'     => 'cf-shortcuts-tools-backups',
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
	// Tools -> FluentSMTP.
	array(
		'node_args'  =>
			array(
				'title'  => \__( 'Emails Send', 'core-functionality' ),
				'parent' => 'cf-shortcuts-tools',
				'id'     => 'cf-shortcuts-tools-fluentsmtp',
				'href'   => \admin_url( 'options-general.php?page=fluent-mail#/logs?per_page=10&page=1&status=&search=', 'admin' ),
				'meta'   => array(
					'title' => \__( 'Link to', 'core-functionality' )
							. ' '
							. \__( 'FluentSMTP', 'core-functionality' ),
				),
			),
		'visibility' => 'both',
		'dependency' => array(
			'plugin' => 'FluentSMTP',
		),
	),
	// Tools -> Redirects.
	array(
		'node_args'  =>
			array(
				'title'  => \__( 'Redirects', 'core-functionality' ),
				'parent' => 'cf-shortcuts-tools',
				'id'     => 'cf-shortcuts-tools-redirects',
				'href'   => \admin_url( 'options-general.php?page=seo-redirection-premium.php', 'admin' ),
				'meta'   => array(
					'title' => \__( 'Link to', 'core-functionality' )
							. ' '
							. \__( 'SEO Redirects', 'core-functionality' ),
				),
			),
		'visibility' => 'both',
		'dependency' => array(
			'plugin' => 'SEO Redirection Premium',
		),
	),
	// Tools -> Redirects.
	array(
		'node_args'  =>
			array(
				'title'  => \__( 'Redirects', 'core-functionality' ),
				'parent' => 'cf-shortcuts-tools-redirects',
				'id'     => 'cf-shortcuts-tools-redirects-all',
				'href'   => \admin_url( 'options-general.php?page=seo-redirection-premium.php', 'admin' ),
				'meta'   => array(
					'title' => \__( 'Link to', 'core-functionality' )
							. ' '
							. \__( 'SEO Redirects', 'core-functionality' ),
				),
			),
		'visibility' => 'both',
		'dependency' => array(
			'plugin' => 'SEO Redirection Premium',
		),
	),
	// Tools -> Redirect 404s.
	array(
		'node_args'  =>
			array(
				'title'  => \__( 'Redirect 404s', 'core-functionality' ),
				'parent' => 'cf-shortcuts-tools-redirects',
				'id'     => 'cf-shortcuts-tools-redirects-404',
				'href'   => \admin_url( 'options-general.php?page=seo-redirection-premium.php&SR_tab=404_manager', 'admin' ),
				'meta'   => array(
					'title' => \__( 'Link to', 'core-functionality' )
							. ' '
							. \__( 'Redirect 404s', 'core-functionality' ),
				),
			),
		'visibility' => 'both',
		'dependency' => array(
			'plugin' => 'SEO Redirection Premium',
		),
	),
	// Tools -> Translate.
	array(
		'node_args'  =>
			array(
				'title'  => \__( 'Translate', 'core-functionality' ),
				'parent' => 'cf-shortcuts-tools',
				'id'     => 'cf-shortcuts-tools-translate',
				'href'   => \admin_url( 'admin.php?page=loco', 'admin' ),
				'meta'   => array(
					'title' => \__( 'Link to', 'core-functionality' )
							. ' '
							. \__( 'Translations', 'core-functionality' ),
				),
			),
		'visibility' => 'both',
		'dependency' => array(
			'plugin' => 'Loco Translate',
		),
	),
	// Tools -> Translations.
	array(
		'node_args'  =>
			array(
				'title'  => \__( 'Translations', 'core-functionality' ),
				'parent' => 'cf-shortcuts-tools-translate',
				'id'     => 'cf-shortcuts-tools-translate-all',
				'href'   => \admin_url( 'admin.php?page=loco', 'admin' ),
				'meta'   => array(
					'title' => \__( 'Link to', 'core-functionality' )
							. ' '
							. \__( 'Translations', 'core-functionality' ),
				),
			),
		'visibility' => 'both',
		'dependency' => array(
			'plugin' => 'Loco Translate',
		),
	),
	// Tools -> Translations -> Plugin Translations.
	array(
		'node_args'  =>
			array(
				'title'  => \__( 'Plugin Translations', 'core-functionality' ),
				'parent' => 'cf-shortcuts-tools-translate',
				'id'     => 'cf-shortcuts-tools-translate-plugins',
				'href'   => \admin_url( 'admin.php?page=loco-plugin', 'admin' ),
				'meta'   => array(
					'title' => \__( 'Link to', 'core-functionality' )
							. ' '
							. \__( 'Plugin Translations', 'core-functionality' ),
				),
			),
		'visibility' => 'both',
		'dependency' => array(
			'plugin' => 'Loco Translate',
		),
	),
	// Tools -> Translations -> Theme Translations.
	array(
		'node_args'  =>
			array(
				'title'  => \__( 'Theme Translations', 'core-functionality' ),
				'parent' => 'cf-shortcuts-tools-translate',
				'id'     => 'cf-shortcuts-tools-translate-theme',
				'href'   => \admin_url( 'admin.php?page=loco-theme', 'admin' ),
				'meta'   => array(
					'title' => \__( 'Link to', 'core-functionality' )
							. ' '
							. \__( 'Theme Translations', 'core-functionality' ),
				),
			),
		'visibility' => 'both',
		'dependency' => array(
			'plugin' => 'Loco Translate',
		),
	),
	// Tools -> Database Editor.
	array(

		'node_args'  =>
			array(
				'title'  => \__( 'Database editor', 'core-functionality' ),
				'parent' => 'cf-shortcuts-tools',
				'id'     => 'cf-shortcuts-tools-phpmyadmin',
				'href'   => \admin_url( 'options-general.php?page=wp-phpmyadmin-extension&isactivation', 'admin' ),
				'meta'   => array(
					'title' => \__( 'Link to', 'core-functionality' )
							. ' '
							. \__( 'phpMyAdmin', 'core-functionality' ),
				),
			),
		'visibility' => 'both',
		'dependency' => array(
			'plugin' => 'WP phpMyAdmin',
		),
	),
	// Tools -> Redis Cache.
	array(

		'node_args'  =>
			array(
				'title'  => \__( 'Redis Cache', 'core-functionality' ),
				'parent' => 'cf-shortcuts-tools',
				'id'     => 'cf-shortcuts-tools-redis',
				'href'   => \admin_url( 'options-general.php?page=redis-cache', 'admin' ),
				'meta'   => array(
					'title' => \__( 'Link to', 'core-functionality' )
							. ' '
							. \__( 'Redis Cache Settings', 'core-functionality' ),
				),
			),
		'visibility' => 'both',
		'dependency' => array(
			'plugin' => 'redis-cache/redis-cache.php',
		),
	),
	// Tools -> Broken Links Checker.
	array(
		'node_args'  =>
			array(
				'title'  => \__( 'Broken Links Checker', 'core-functionality' ),
				'parent' => 'cf-shortcuts-tools',
				'id'     => 'cf-shortcuts-tools-broken-links',
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

);
