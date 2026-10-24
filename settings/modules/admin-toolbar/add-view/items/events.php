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
	// View -> Events Calendar.
	array(
		'node_args'  =>
			array(
				'title'  => \__( 'Events Calendar', 'core-functionality' ),
				'parent' => 'cf-shortcuts-view',
				'id'     => 'cf-shortcuts-view-events',
				'href'   => function_exists( 'tribe_get_events_link' ) ? \tribe_get_events_link() : '#',
				'meta'   => array(
					'title' => \__( 'Link to', 'core-functionality' )
							. ' '
							. \__( 'The Events Calendar', 'core-functionality' ),
				),
			),
		'visibility' => 'both',
		'dependency' => array(
			'plugin' => 'The Events Calendar',
		),
	),

	// View -> Events Calendar -> Family Constellations.
	array(
		'node_args'  =>
			array(
				'title'  => \__( 'Family Constellations', 'core-functionality' ),
				'parent' => 'cf-shortcuts-view-events',
				'id'     => 'cf-shortcuts-view-events-cat-family-constellations',
				'href'   => \get_site_url( null, '/agenda/categorie/familieopstellingen/' ),
				'meta'   => array(
					'title' => \__( 'Link to', 'core-functionality' )
							. ' '
							. \__( 'Family Constellations', 'core-functionality' ),
				),
			),
		'visibility' => 'both',
		'dependency' => array(
			'plugin' => 'The Events Calendar',
		),
	),
);
