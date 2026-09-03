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
	// Shop -> Settings -> EU VAT Assistant.
	array(
		'node_args'  =>
			array(
				'title'  => \__( 'EU VAT Assistant', 'core-functionality-dwp' ),
				'parent' => 'cf-shortcuts-woocommerce-settings',
				'id'     => 'cf-shortcuts-woocommerce-settings-eu-vat-assistant',
				'href'   => \admin_url( 'admin.php?page=wc_aelia_eu_vat_assistant', 'admin' ),
				'meta'   => array(
					'title' => \__( 'Link to WooCommerce', 'core-functionality-dwp' )
							. ' '
							. \__( 'EU VAT Assistant Settings', 'core-functionality-dwp' ),
				),
			),
		'visibility' => 'both',
		'dependency' => array(
			'plugin' => 'WooCommerce EU VAT Assistant',
		),
	),
);
