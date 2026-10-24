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
class WoocommerceActiveCampaignIntegration extends AbstractPlugin {

	#[Override]
	public function get_id(): string {
		return 'woocommerce-active-campaign-integration';
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
				id:      'cf-woocommerce-settings-activewoo-menu',
				title:   __( 'ActiveWoo', 'core-functionality' ),
				href:    admin_url( 'admin.php?page=wc-settings&tab=integration&section=active-woo' ),
				label:   __( 'Link to', 'core-functionality' ) . ' ' . __( 'WooCommerce ActiveWoo Settings', 'core-functionality' ),
				orientation: Orientation::ADMIN,
			),
			new ToolbarShortcut(
				id:      'cf-woocommerce-settings-activewoo-base',
				title:   __( 'Settings', 'core-functionality' ),
				href:    admin_url( 'admin.php?page=wc-settings&tab=integration&section=active-woo' ),
				label:   __( 'Link to', 'core-functionality' ) . ' ' . __( 'WooCommerce ActiveWoo Settings', 'core-functionality' ),
				orientation: Orientation::ADMIN,
			),
			new ToolbarShortcut(
				id:      'cf-woocommerce-settings-activewoo-advanced',
				title:   __( 'Advanced Settings', 'core-functionality' ),
				href:    admin_url( 'admin.php?page=wc-settings&tab=integration&section=active-woo-advanced' ),
				label:   __( 'Link to', 'core-functionality' ) . ' ' . __( 'WooCommerce ActiveWoo Advanced Settings', 'core-functionality' ),
				orientation: Orientation::ADMIN,
			),
			new ToolbarShortcut(
				id:      'cf-woocommerce-settings-activewoo-rc',
				title:   __( 'Recover Cart Settings', 'core-functionality' ),
				href:    admin_url( 'admin.php?page=wc-settings&tab=integration&section=active-woo-advanced-rc' ),
				label:   __( 'Link to', 'core-functionality' ) . ' ' . __( 'WooCommerce ActiveWoo Recover Cart Settings', 'core-functionality' ),
				orientation: Orientation::ADMIN,
			),
			new ToolbarShortcut(
				id:      'cf-woocommerce-settings-activewoo-coupons',
				title:   __( 'Coupons Settings', 'core-functionality' ),
				href:    admin_url( 'admin.php?page=wc-settings&tab=integration&section=active-woo-advanced-coupons' ),
				label:   __( 'Link to', 'core-functionality' ) . ' ' . __( 'WooCommerce ActiveWoo Coupons Settings', 'core-functionality' ),
				orientation: Orientation::ADMIN,
			),
		);
	}

	#[Override]
	protected function get_menu_nodes(): array {
		return array();
	}
}
