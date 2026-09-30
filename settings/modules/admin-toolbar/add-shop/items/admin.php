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
	// Shop -> Orders.
	array(
		'node_args'  =>
			array(
				'title'  => \__( 'Orders', 'dwp-cf' ),
				'parent' => 'cf-shortcuts-woocommerce',
				'id'     => 'cf-shortcuts-woocommerce-orders',
				'href'   => \admin_url( 'edit.php?post_type=shop_order', 'admin' ),
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
	// Shop -> Products.
	array(
		'node_args'  =>
			array(
				'title'  => \__( 'Products', 'dwp-cf' ),
				'parent' => 'cf-shortcuts-woocommerce',
				'id'     => 'cf-shortcuts-woocommerce-products',
				'href'   => \admin_url( 'edit.php?post_type=product', 'admin' ),
				'meta'   => array(
					'title' => \__( 'Link to WooCommerce', 'dwp-cf' )
							. ' '
							. \__( 'Products', 'dwp-cf' ),
				),
			),
		'visibility' => 'both',
		'dependency' => array(
			'plugin' => 'WooCommerce',
		),
	),
	// Shop -> Product Add-ons.
	array(
		'node_args'  =>
			array(
				'title'  => \__( 'Product Add-ons', 'dwp-cf' ),
				'parent' => 'cf-shortcuts-woocommerce',
				'id'     => 'cf-shortcuts-woocommerce-product-add-ons',
				'href'   => \admin_url( 'edit.php?post_type=product&page=addons', 'admin' ),
				'meta'   => array(
					'title' => \__( 'Link to WooCommerce', 'dwp-cf' )
							. ' '
							. \__( 'Product Add-ons', 'dwp-cf' ),
				),
			),
		'visibility' => 'both',
		'dependency' => array(
			'plugin' => 'WooCommerce Product Add-ons',
		),
	),
	// Shop -> Subscriptions.
	array(
		'node_args'  =>
			array(
				'title'  => \__( 'Subscriptions', 'dwp-cf' ),
				'parent' => 'cf-shortcuts-woocommerce',
				'id'     => 'cf-shortcuts-woocommerce-subscriptions',
				'href'   => \admin_url( 'edit.php?post_type=shop_subscription', 'admin' ),
				'meta'   => array(
					'title' => \__( 'Link to WooCommerce', 'dwp-cf' )
							. ' '
							. \__( 'Subscriptions', 'dwp-cf' ),
				),
			),
		'visibility' => 'both',
		'dependency' => array(
			'plugin' => 'WooCommerce Subscriptions',
		),
	),
	// Shop -> Coupons.
	array(
		'node_args'  =>
			array(
				'title'  => \__( 'Coupons', 'dwp-cf' ),
				'parent' => 'cf-shortcuts-woocommerce',
				'id'     => 'cf-shortcuts-woocommerce-coupons',
				'href'   => \admin_url( 'edit.php?post_type=shop_coupon', 'admin' ),
				'meta'   => array(
					'title' => \__( 'Link to WooCommerce', 'dwp-cf' )
							. ' '
							. \__( 'Coupons', 'dwp-cf' ),
				),
			),
		'visibility' => 'both',
		'dependency' => array(
			'plugin' => 'WooCommerce',
		),
	),
	// Shop -> Product Categories.
	array(
		'node_args'  =>
			array(
				'title'  => \__( 'Categories', 'dwp-cf' ),
				'parent' => 'cf-shortcuts-woocommerce',
				'id'     => 'cf-shortcuts-woocommerce-product-cats',
				'href'   => \admin_url( 'edit-tags.php?taxonomy=product_cat&post_type=product', 'admin' ),
				'meta'   => array(
					'title' => \__( 'Link to WooCommerce', 'dwp-cf' )
							. ' '
							. \__( 'Product Categories', 'dwp-cf' ),
				),
			),
		'visibility' => 'both',
		'dependency' => array(
			'plugin' => 'WooCommerce',
		),
	),
	// Shop -> Follow-Up Emails.
	array(
		'node_args'  =>
			array(
				'title'  => \__( 'Follow-Up Emails', 'dwp-cf' ),
				'parent' => 'cf-shortcuts-woocommerce',
				'id'     => 'cf-shortcuts-woocommerce-follow-up-emails',
				'href'   => \admin_url( 'admin.php?page=followup-emails', 'admin' ),
				'meta'   => array(
					'title' => \__( 'Link to WooCommerce', 'dwp-cf' )
							. ' '
							. \__( 'Follow-Up Emails', 'dwp-cf' ),
				),
			),
		'visibility' => 'both',
		'dependency' => array(
			'plugin' => 'Follow-Up Emails',
		),
	),
	// Shop -> Sales Statistics.
	array(
		'node_args'  =>
			array(
				'title'  => \__( 'Statistics', 'dwp-cf' ),
				'parent' => 'cf-shortcuts-woocommerce',
				'id'     => 'cf-shortcuts-woocommerce-analytics',
				'href'   => \admin_url( 'admin.php?page=wc-admin&path=/analytics/revenue', 'admin' ),
				'meta'   => array(
					'title' => \__( 'Link to WooCommerce', 'dwp-cf' )
							. ' '
							. \__( 'Sales Statistics', 'dwp-cf' ),
				),
			),
		'visibility' => 'both',
		'dependency' => array(
			'plugin' => 'WooCommerce',
		),
	),
	// Shop -> Order CSV Export.
	array(
		'node_args'  =>
			array(
				'title'  => \__( 'Shop Order CSV Export', 'dwp-cf' ),
				'parent' => 'cf-shortcuts-woocommerce',
				'id'     => 'cf-shortcuts-woocommerce-settings-csv-export',
				'href'   => \admin_url( 'admin.php?page=wc_customer_order_csv_export', 'admin' ),
				'meta'   => array(
					'title' => \__( 'Link to WooCommerce', 'dwp-cf' )
							. ' '
							. \__( 'Shop Order CSV Export', 'dwp-cf' ),
				),
			),
		'visibility' => 'both',
		'dependency' => array(
			'plugin' => 'WooCommerce Customer/Order CSV Export',
		),
	),
);
