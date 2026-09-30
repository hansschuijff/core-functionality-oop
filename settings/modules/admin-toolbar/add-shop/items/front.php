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
	// Shop -> Front.
	array(
		'node_args'  =>
			array(
				'title'  => \__( 'Shop front', 'dwp-cf' ),
				'parent' => 'cf-shortcuts-woocommerce',
				'id'     => 'cf-shortcuts-woocommerce-front',
				'href'   => \get_site_url( null, '/winkel/' ),
				'meta'   => array(
					'title' => \__( 'Link to WooCommerce', 'dwp-cf' )
							. ' '
							. \__( 'Orders', 'dwp-cf' ),
				),
			),
		'visibility' => 'both',
		'dependency' => array(
			'plugin' => 'WooCommerce',
		),
	),
	// Shop -> Front -> View Shop.
	array(
		'node_args'  =>
			array(
				'title'  => \__( 'Shop', 'dwp-cf' ),
				'parent' => 'cf-shortcuts-woocommerce-front',
				'id'     => 'cf-shortcuts-woocommerce-front-store',
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
	// Shop -> Front -> View My Account.
	array(
		'node_args'  =>
			array(
				'title'  => \__( 'My Account', 'dwp-cf' ),
				'parent' => 'cf-shortcuts-woocommerce-front',
				'id'     => 'cf-shortcuts-woocommerce-front-my-account',
				'href'   => \get_site_url( null, '/mijn-account/' ),
				'meta'   => array(
					'title' => \__( 'Link to My Account', 'dwp-cf' ),
				),
			),
		'visibility' => 'both',
		'dependency' => array(
			'plugin' => 'WooCommerce',
		),
	),
);
