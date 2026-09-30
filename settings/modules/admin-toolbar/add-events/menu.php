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
	// Events.
	array(
		'node_args'  =>
			array(
				'title'  => \__( 'Events', 'dwp-cf' ),
				'parent' => 'site-name',
				'id'     => 'cf-shortcuts-events',
				'href'   => \admin_url( 'edit.php?post_type=tribe_events', 'admin' ),
				'meta'   => array(
					'title' => \__( 'Link to', 'dwp-cf' )
							. ' '
							. \__( 'Events', 'dwp-cf' ),
				),
			),
		'visibility' => 'both',
		'dependency' => array(
			'plugin' => 'The Events Calendar',
		),
	),
	// Events -> View Events.
	array(
		'node_args'  =>
			array(
				'title'  => \__( 'View', 'dwp-cf' ),
				'parent' => 'cf-shortcuts-events',
				'id'     => 'cf-shortcuts-events-view-events',
				'href'   => function_exists( 'tribe_get_events_link' ) ? \tribe_get_events_link() : '#',
				'meta'   => array(
					'title' => \__( 'Link to', 'dwp-cf' )
							. ' '
							. \__( 'The Events Calendar', 'dwp-cf' ),
				),
			),
		'visibility' => 'both',
		'dependency' => array(
			'plugin' => 'The Events Calendar',
		),
	),
	// Events -> Edit Events.
	array(
		'node_args'  =>
			array(
				'title'  => \__( 'Edit', 'dwp-cf' ),
				'parent' => 'cf-shortcuts-events',
				'id'     => 'cf-shortcuts-events-edit-events',
				'href'   => \admin_url( 'edit.php?post_type=tribe_events', 'admin' ),
				'meta'   => array(
					'title' => \__( 'Link to', 'dwp-cf' )
							. ' '
							. \__( 'Events', 'dwp-cf' ),
				),
			),
		'visibility' => 'both',
		'dependency' => array(
			'plugin' => 'The Events Calendar',
		),
	),
	// Events -> Organizers.
	array(
		'node_args'  =>
			array(
				'title'  => \__( 'Organizers', 'dwp-cf' ),
				'parent' => 'cf-shortcuts-events',
				'id'     => 'cf-shortcuts-events-organizers',
				'href'   => \admin_url( 'edit.php?post_type=tribe_organizer', 'admin' ),
				'meta'   => array(
					'title' => \__( 'Link to Event', 'dwp-cf' )
							. ' '
							. \__( 'Organizers', 'dwp-cf' ),
				),
			),
		'visibility' => 'both',
		'dependency' => array(
			'plugin' => 'The Events Calendar',
		),
	),
	// Events -> Venues.
	array(
		'node_args'  =>
			array(
				'title'  => \__( 'Venues', 'dwp-cf' ),
				'parent' => 'cf-shortcuts-events',
				'id'     => 'cf-shortcuts-events-venues',
				'href'   => \admin_url( 'edit.php?post_type=tribe_venue', 'admin' ),
				'meta'   => array(
					'title' => \__( 'Link to Event', 'dwp-cf' )
							. ' '
							. \__( 'Venues', 'dwp-cf' ),
				),
			),
		'visibility' => 'both',
		'dependency' => array(
			'plugin' => 'The Events Calendar',
		),
	),
	// Events -> Categories.
	array(
		'node_args'  =>
			array(
				'title'  => \__( 'Categories', 'dwp-cf' ),
				'parent' => 'cf-shortcuts-events',
				'id'     => 'cf-shortcuts-events-categories',
				'href'   => \admin_url( 'edit-tags.php?taxonomy=tribe_events_cat&post_type=tribe_events', 'admin' ),
				'meta'   => array(
					'title' => \__( 'Link to Event', 'dwp-cf' )
							. ' '
							. \__( 'Categories', 'dwp-cf' ),
				),
			),
		'visibility' => 'both',
		'dependency' => array(
			'plugin' => 'The Events Calendar',
		),
	),
	// Events -> Events Calender Settings.
	array(
		'node_args'  =>
			array(
				'title'  => \__( 'Settings', 'dwp-cf' ),
				'parent' => 'cf-shortcuts-events',
				'id'     => 'cf-shortcuts-events-settings',
				'href'   => \admin_url( 'edit.php?post_type=tribe_events&page=tec-events-settings', 'admin' ),
				'meta'   => array(
					'title' => \__( 'Link to', 'dwp-cf' )
							. ' '
							. \__( 'Events Calendar Settings', 'dwp-cf' ),
				),
			),
		'visibility' => 'both',
		'dependency' => array(
			'plugin' => 'The Events Calendar',
		),
	),
);
