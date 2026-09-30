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

use DeWittePrins\Corefunctionality\Modules\AdminToolbar\AdminToolbar;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	// Shop.
	array(
		'node_args'  =>
			array(
				'title'  => \__( 'Shop', 'dwp-cf' ),
				'parent' => 'site-name',
				'id'     => 'cf-shortcuts-woocommerce',
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
	// Shop -> Shop Front.
	...$this->get( 'admin-toolbar-add-shop-front', AdminToolbar::get_id() ),
	// Shop -> Shop Admin.
	...$this->get( 'admin-toolbar-add-shop-admin', AdminToolbar::get_id() ),
	// Shop -> Settings.
	...$this->get( 'admin-toolbar-add-shop-settings', AdminToolbar::get_id() ),
);
