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
	// Shop -> Settings -> ActiveWoo Settings.
	array(
		'node_args'  =>
			array(
				'title'  => \__( 'ActiveWoo', 'core-functionality-dwp' ),
				'parent' => 'cf-shortcuts-woocommerce-settings',
				'id'     => 'cf-shortcuts-woocommerce-settings-activewoo',
				'href'   => \admin_url( 'admin.php?page=wc-settings&tab=integration&section=active-woo', 'admin' ),
				'meta'   => array(
					'title' => \__( 'Link to WooCommerce', 'core-functionality-dwp' )
							. ' '
							. \__( 'ActiveWoo Settings', 'core-functionality-dwp' ),
				),
			),
		'visibility' => 'both',
		'dependency' => array(
			'plugin' => 'WooCommerce - Active Campaign Integration',
		),
	),
	// Shop -> Settings -> ActiveWoo -> WooCommerce Settings.
	array(
		'node_args'  =>
			array(
				'title'  => \__( 'ActiveWoo', 'core-functionality-dwp' ),
				'parent' => 'cf-shortcuts-woocommerce-settings-activewoo',
				'id'     => 'cf-shortcuts-woocommerce-settings-activewoo-base',
				'href'   => \admin_url( 'admin.php?page=wc-settings&tab=integration&section=active-woo', 'admin' ),
				'meta'   => array(
					'title' => \__( 'Link to WooCommerce', 'core-functionality-dwp' )
							. ' '
							. \__( 'ActiveWoo Settings', 'core-functionality-dwp' ),
				),
			),
		'visibility' => 'both',
		'dependency' => array(
			'plugin' => 'WooCommerce - Active Campaign Integration',
		),
	),
	// Shop -> Settings -> ActiveWoo -> Advanced Settings.
	array(
		'node_args'  =>
			array(
				'title'  => \__( 'Advanced Settings', 'core-functionality-dwp' ),
				'parent' => 'cf-shortcuts-woocommerce-settings-activewoo',
				'id'     => 'cf-shortcuts-woocommerce-settings-activewoo-advanced',
				'href'   => \admin_url( 'admin.php?page=wc-settings&tab=integration&section=active-woo-advanced', 'admin' ),
				'meta'   => array(
					'title' => \__( 'Link to WooCommerce', 'core-functionality-dwp' )
							. ' '
							. \__( 'ActiveWoo Advanced Settings', 'core-functionality-dwp' ),
				),
			),
		'visibility' => 'both',
		'dependency' => array(
			'plugin' => 'WooCommerce - Active Campaign Integration',
		),
	),
	// Shop -> Settings -> ActiveWoo -> Advanced Recover Cart.
	array(
		'node_args'  =>
			array(
				'title'  => \__( 'Recover Cart Settings', 'core-functionality-dwp' ),
				'parent' => 'cf-shortcuts-woocommerce-settings-activewoo',
				'id'     => 'cf-shortcuts-woocommerce-settings-activewoo-rc',
				'href'   => \admin_url( 'admin.php?page=wc-settings&tab=integration&section=active-woo-advanced-rc', 'admin' ),
				'meta'   => array(
					'title' => \__( 'Link to WooCommerce', 'core-functionality-dwp' )
							. ' '
							. \__( 'ActiveWoo Recover Cart Settings', 'core-functionality-dwp' ),
				),
			),
		'visibility' => 'both',
		'dependency' => array(
			'plugin' => 'WooCommerce - Active Campaign Integration',
		),
	),
	// Shop -> Settings -> ActiveWoo -> Advanced Coupons.
	array(
		'node_args'  =>
		array(
			'title'  => \__( 'Coupons Settings', 'core-functionality-dwp' ),
			'parent' => 'cf-shortcuts-woocommerce-settings-activewoo',
			'id'     => 'cf-shortcuts-woocommerce-settings-activewoo-coupons',
			'href'   => \admin_url( 'admin.php?page=wc-settings&tab=integration&section=active-woo-advanced-coupons', 'admin' ),
			'meta'   => array(
				'title' => \__( 'Link to WooCommerce', 'core-functionality-dwp' )
						. ' '
						. \__( 'ActiveWoo Coupons Settings', 'core-functionality-dwp' ),
			),
		),
		'visibility' => 'both',
		'dependency' => array(
			'plugin' => 'WooCommerce - Active Campaign Integration',
		),
	),
);
