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
	// Plugins -> Updates.
	array(
		'node_args'  =>
			array(
				'title'  => \__( 'Updates', 'core-functionality' ),
				'parent' => 'cf-shortcuts-plugins',
				'id'     => 'cf-shortcuts-plugins-updates',
				'href'   => \admin_url( 'update-core.php', 'admin' ),
				'meta'   => array(
					'title' => \__( 'Link to', 'core-functionality' )
							. ' '
							. \__( 'Plugins & Themes Updates', 'core-functionality' ),
				),
			),
		'visibility' => 'both',
	),
	// Plugins -> Installed Plugins.
	array(
		'node_args'  =>
			array(
				'title'  => \__( 'Installed Plugins', 'core-functionality' ),
				'parent' => 'cf-shortcuts-plugins',
				'id'     => 'plugins',
				'href'   => \admin_url( 'plugins.php', 'admin' ),
				'meta'   => array(
					'title' => \__( 'Link to', 'core-functionality' )
							. ' '
							. \__( 'Installed Plugins', 'core-functionality' ),
				),
			),
		'visibility' => 'both',
	),
	// Plugins -> Add Plugin.
	array(
		'node_args'  =>
			array(
				'title'  => \__( 'Add Plugin', 'core-functionality' ),
				'parent' => 'cf-shortcuts-plugins',
				'id'     => 'cf-shortcuts-plugins-new',
				'href'   => \admin_url( 'plugin-install.php', 'admin' ),
				'meta'   => array(
					'title' => \__( 'Link to', 'core-functionality' )
							. ' '
							. \__( 'Add Plugin', 'core-functionality' ),
				),
			),
		'visibility' => 'both',
	),
);
