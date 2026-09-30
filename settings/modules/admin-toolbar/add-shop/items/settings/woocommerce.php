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
	// Shop -> Settings.
	array(
		'node_args'  =>
			array(
				'title'  => \__( 'Settings', 'dwp-cf' ),
				'parent' => 'cf-shortcuts-woocommerce',
				'id'     => 'cf-shortcuts-woocommerce-settings',
				'href'   => '#',
			),
		'visibility' => 'both',
		'dependency' => array(
			'plugin' => 'woocommerce',
		),
	),
	// Shop -> Settings -> WooCommerce.
	array(
		'node_args'  =>
			array(
				'title'  => \__( 'WooCommerce', 'dwp-cf' ),
				'parent' => 'cf-shortcuts-woocommerce-settings',
				'id'     => 'cf-shortcuts-woocommerce-settings-woocommerce',
				'href'   => \admin_url( 'admin.php?page=wc-settings', 'admin' ),
				'meta'   => array(
					'title' => \__( 'Link to ', 'dwp-cf' )
							. ' '
							. \__( 'WooCommerce Settings', 'dwp-cf' ),
				),
			),
		'visibility' => 'both',
		'dependency' => array(
			'plugin' => 'WooCommerce',
		),
	),
);
