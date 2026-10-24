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
	// Shop -> Settings -> PDF-invoice Settings.
	array(
		'node_args'  =>
			array(
				'title'  => \__( 'PDF-invoice', 'core-functionality' ),
				'parent' => 'cf-shortcuts-woocommerce-settings',
				'id'     => 'cf-shortcuts-woocommerce-settings-pdf-invoice',
				'href'   => \admin_url( 'admin.php?page=wpo_wcpdf_options_page', 'admin' ),
				'meta'   => array(
					'title' => \__( 'Link to WooCommerce', 'core-functionality' )
							. ' '
							. \__( 'PDF-invoice Settings', 'core-functionality' ),
				),
			),
		'visibility' => 'both',
		'dependency' => array(
			'plugin' => 'WooCommerce PDF Invoices & Packing Slips',
		),
	),
);
