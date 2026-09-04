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
	...$this->get( 'admin-toolbar-add-shop-settings-woocommerce', AdminToolbar::get_id() ),
	...$this->get( 'admin-toolbar-add-shop-settings-activewoo', AdminToolbar::get_id() ),
	...$this->get( 'admin-toolbar-add-shop-settings-pdf-invoices', AdminToolbar::get_id() ),
	...$this->get( 'admin-toolbar-add-shop-settings-eu-vat-assistant', AdminToolbar::get_id() ),
);
