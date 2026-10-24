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
				'title'  => \__( 'Shop', 'core-functionality' ),
				'parent' => 'cf-shortcuts-view',
				'id'     => 'view-store',
				'href'   => \get_site_url( null, '/winkel/' ),
				'meta'   => array(
					'title' => \__( 'Link to WooCommerce', 'core-functionality' )
							. ' '
							. \__( 'Shop', 'core-functionality' ),
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
				'title'  => \__( 'My Account', 'core-functionality' ),
				'parent' => 'cf-shortcuts-view-shop',
				'id'     => 'cf-shortcuts-view-shop-my-account',
				'href'   => \get_site_url( null, '/mijn-account/' ),
				'meta'   => array(
					'title' => \__( 'Link to WooCommerce', 'core-functionality' )
							. ' '
							. \__( 'My Account', 'core-functionality' ),
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
				'title'  => \__( 'Courses', 'core-functionality' ),
				'parent' => 'cf-shortcuts-view-shop',
				'id'     => 'cf-shortcuts-view-shop-courses',
				'href'   => \get_site_url( null, '/product-categorie/cursus-opleiding/' ),
				'meta'   => array(
					'title' => \__( 'Link to WooCommerce', 'core-functionality' )
							. ' '
							. \__( 'Courses', 'core-functionality' ),
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
				'title'  => \__( 'Course Payment Plans', 'core-functionality' ),
				'parent' => 'cf-shortcuts-view-shop',
				'id'     => 'cf-shortcuts-view-shop-course-subscriptions',
				'href'   => \get_site_url( null, '/product-categorie/leerplannen/' ),
				'meta'   => array(
					'title' => \__( 'Link to WooCommerce', 'core-functionality' )
							. ' '
							. \__( 'Course Payment Plans', 'core-functionality' ),
				),
			),
		'visibility' => 'both',
		'dependency' => array(
			'plugin' => 'WooCommerce Subscriptions',
		),
	),
);
