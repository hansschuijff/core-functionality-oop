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

/**
 * Class Woocommerce
 */
class WoocommerceProductAddons extends AbstractPlugin {

	#[Override]
	public function get_id(): string {
		return 'woocommerce-product-addons';
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
				id:      'cf-woocommerce-product-add-ons',
				title:   __( 'Product Add-ons', 'dwp-cf' ),
				href:    admin_url( 'edit.php?post_type=product&page=addons' ),
				label:   __( 'Link to', 'dwp-cf' ) . ' ' . __( 'WooCommerce', 'dwp-cf' ) . ' ' . __( 'Product Add-ons', 'dwp-cf' ),
				orientation: Orientation::ADMIN,
			),
		);
	}

	#[Override]
	protected function get_menu_nodes(): array {
		return array();
	}
}
