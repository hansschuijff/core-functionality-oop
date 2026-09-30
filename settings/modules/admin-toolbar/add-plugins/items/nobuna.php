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
	// Plugins -> Nobuna.
	array(
		'node_args'  =>
			array(
				'title'  => \__( 'Nobuna', 'dwp-cf' ),
				'parent' => 'cf-shortcuts-plugins',
				'id'     => 'cf-shortcuts-plugins-nobuna',
				'href'   => \admin_url( 'admin.php?page=nobuna-plugins', 'admin' ),
				'meta'   => array(
					'title' => \__( 'Link to', 'dwp-cf' )
							. ' '
							. \__( 'Nobuna Plugins', 'dwp-cf' ),
				),
			),
		'visibility' => 'both',
		'dependency' => array(
			'plugin' => 'Nobuna Plugins',
		),
	),
);
