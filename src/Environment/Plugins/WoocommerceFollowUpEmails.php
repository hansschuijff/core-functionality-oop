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
class WoocommerceFollowUpEmails extends AbstractPlugin {

	#[Override]
	public function get_id(): string {
		// Full plugin name is WooCommerce PDF Invoices & Packing Slips
		return 'woocommerce-follow-up-emails';
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
				id:      'cf-woocommerce-follow-up-emails',
				title:   __( 'Follow-Up Emails', 'dwp-cf' ),
				href:    admin_url( 'admin.php?page=followup-emails' ),
				label:   __( 'Link to', 'dwp-cf' ) . ' ' . __( 'WooCommerce Follow-Up Emails', 'dwp-cf' ),
				orientation: Orientation::ADMIN,
			),
		);
	}

	#[Override]
	protected function get_menu_nodes(): array {
		return array();
	}
}
