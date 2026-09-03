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
				'title'  => \__( 'Settings', 'core-functionality-dwp' ),
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
				'title'  => \__( 'WooCommerce', 'core-functionality-dwp' ),
				'parent' => 'cf-shortcuts-woocommerce-settings',
				'id'     => 'cf-shortcuts-woocommerce-settings-woocommerce',
				'href'   => \admin_url( 'admin.php?page=wc-settings', 'admin' ),
				'meta'   => array(
					'title' => \__( 'Link to ', 'core-functionality-dwp' )
							. ' '
							. \__( 'WooCommerce Settings', 'core-functionality-dwp' ),
				),
			),
		'visibility' => 'both',
		'dependency' => array(
			'plugin' => 'WooCommerce',
		),
	),
);
