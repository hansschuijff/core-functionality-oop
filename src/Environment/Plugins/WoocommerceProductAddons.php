<?php
/**
 * WooCommerce Product Add-Ons Verification Guard.
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
 * Class WoocommerceProductAddons
 */
class WoocommerceProductAddons extends AbstractPlugin {

	/**
	 * Returns the environment id of this plugin or theme.
	 *
	 * @since  1.0.0
	 * @return string The id, also the key in environment-index.php.
	 */
	#[Override]
	public function get_id(): string {
		return 'woocommerce-product-addons';
	}

	// #[Override]
	protected function get_proof_requirements(): ProofRequirement {
		return new ProofRequirement( functions: 'woocommerce_product_addons_init' );
	}

	/**
	 * Returns the admin toolbar links this plugin offers for the quick menu.
	 *
	 * @since  1.0.0
	 * @return ToolbarShortcut[] The toolbar links.
	 */
	#[Override]
	protected function get_toolbar_nodes(): array {
		return array(
			new ToolbarShortcut(
				id:      'cf-woocommerce-product-add-ons',
				title:   __( 'Product Add-ons', 'core-functionality' ),
				href:    admin_url( 'edit.php?post_type=product&page=addons' ),
				label:   __( 'Link to', 'core-functionality' ) . ' ' . __( 'WooCommerce', 'core-functionality' ) . ' ' . __( 'Product Add-ons', 'core-functionality' ),
				orientation: Orientation::ADMIN,
			),
		);
	}

	/**
	 * Returns the admin menu blocks this plugin offers.
	 *
	 * @since  1.0.0
	 * @return AdminMenuBlock[] The menu blocks.
	 */
	#[Override]
	protected function get_menu_nodes(): array {
		return array();
	}
}
