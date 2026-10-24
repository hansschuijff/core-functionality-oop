<?php
/**
 * WooCommerce Follow-Up Emails Verification Guard.
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
 * Class WoocommerceFollowUpEmails
 */
class WoocommerceFollowUpEmails extends AbstractPlugin {

	#[Override]
	public function get_id(): string {
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
				title:   __( 'Follow-Up Emails', 'core-functionality' ),
				href:    admin_url( 'admin.php?page=followup-emails' ),
				label:   __( 'Link to', 'core-functionality' ) . ' ' . __( 'WooCommerce Follow-Up Emails', 'core-functionality' ),
				orientation: Orientation::ADMIN,
			),
		);
	}

	#[Override]
	protected function get_menu_nodes(): array {
		return array();
	}
}
