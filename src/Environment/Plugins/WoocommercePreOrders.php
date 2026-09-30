<?php
/**
 * WooCommerce Verification Guard.
 *
 * @package DeWittePrins\CoreFunctionality\Environment\Plugins
 */

declare(strict_types=1);

namespace DeWittePrins\CoreFunctionality\Environment\Plugins;

use DeWittePrins\CoreFunctionality\Enums\Orientation;
use DeWittePrins\CoreFunctionality\DTO\AdminMenuBlock;
use DeWittePrins\CoreFunctionality\DTO\ToolbarShortcut;
use DeWittePrins\CoreFunctionality\Environment\DTO\ProofRequirement;
use Override;

use function __;
use function admin_url;
use function home_url;

/**
 * Class Woocommerce
 */
class WoocommercePreOrders extends AbstractPlugin {

	#[Override]
	public function get_id(): string {
		// Full plugin name is WooCommerce PDF Invoices & Packing Slips
		return 'woocommerce-pre-orders';
	}

	// #[Override]
	// protected function get_proof_requirements(): ProofRequirement {
	// 	return new ProofRequirement(
	// 		// classes: '',
	// 		// actions: ''
	// 	);
	// }

	#[Override]
	protected function get_toolbar_nodes(): array {
		return array(
			new ToolbarShortcut(
				id:      'cf-woocommerce-my-account-pre-orders',
 				title:   __( 'My Account: Pre-orders', 'dwp-cf' ),
				href:    fn() => function_exists( 'wc_get_page_permalink' ) ? wc_get_endpoint_url( 'pre-orders', '', wc_get_page_permalink( 'myaccount' ) ) : home_url( __( '/myaccount/pre-orders/', 'dwp-cf' ) ),
				label:   __( 'Link to', 'dwp-cf' ) . ' ' . __( 'My Account: Pre-orders', 'dwp-cf' ),
				orientation: Orientation::FRONTEND,
			),
		);
	}

	#[Override]
	protected function get_menu_nodes(): array {
		return array();
	}
}
