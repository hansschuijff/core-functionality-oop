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
	// View -> Shop.
	array(
		'node_args'  =>
			array(
				'title'  => \__( 'Shop', 'dwp-cf' ),
				'parent' => 'cf-shortcuts-view',
				'id'     => 'view-store',
				'href'   => \get_site_url( null, '/winkel/' ),
				'meta'   => array(
					'title' => \__( 'Link to WooCommerce', 'dwp-cf' )
							. ' '
							. \__( 'Shop', 'dwp-cf' ),
				),
			),
		'visibility' => 'both',
		'dependency' => array(
			'plugin' => 'WooCommerce',
		),
	),
	// View -> Shop -> My Account.
	array(
		'node_args'  =>
			array(
				'title'  => \__( 'My Account', 'dwp-cf' ),
				'parent' => 'cf-shortcuts-view-shop',
				'id'     => 'cf-shortcuts-view-shop-my-account',
				'href'   => \get_site_url( null, '/mijn-account/' ),
				'meta'   => array(
					'title' => \__( 'Link to WooCommerce', 'dwp-cf' )
							. ' '
							. \__( 'My Account', 'dwp-cf' ),
				),
			),
		'visibility' => 'both',
		'dependency' => array(
			'plugin' => 'WooCommerce',
		),
	),
	// View -> Shop -> Courses.
	array(
		'node_args'  =>
			array(
				'title'  => \__( 'Courses', 'dwp-cf' ),
				'parent' => 'cf-shortcuts-view-shop',
				'id'     => 'cf-shortcuts-view-shop-courses',
				'href'   => \get_site_url( null, '/product-categorie/cursus-opleiding/' ),
				'meta'   => array(
					'title' => \__( 'Link to WooCommerce', 'dwp-cf' )
							. ' '
							. \__( 'Courses', 'dwp-cf' ),
				),
			),
		'visibility' => 'both',
		'dependency' => array(
			'plugin' => 'WooCommerce',
		),
	),
	// View -> Shop -> Course Payment Plans.
	array(
		'node_args'  =>
			array(
				'title'  => \__( 'Course Payment Plans', 'dwp-cf' ),
				'parent' => 'cf-shortcuts-view-shop',
				'id'     => 'cf-shortcuts-view-shop-course-subscriptions',
				'href'   => \get_site_url( null, '/product-categorie/leerplannen/' ),
				'meta'   => array(
					'title' => \__( 'Link to WooCommerce', 'dwp-cf' )
							. ' '
							. \__( 'Course Payment Plans', 'dwp-cf' ),
				),
			),
		'visibility' => 'both',
		'dependency' => array(
			'plugin' => 'WooCommerce Subscriptions',
		),
	),
);
