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
class WoocommercePdfInvoices extends AbstractPlugin {

	#[Override]
	public function get_id(): string {
		// Full plugin name is WooCommerce PDF Invoices & Packing Slips
		return 'woocommerce_pdf_invoices';
	}

	// #[Override]
	protected function get_proof_requirements(): ProofRequirement {
		return new ProofRequirement( functions: 'WPO_WCPDF' );
	}

	#[Override]
	protected function get_toolbar_nodes(): array {
		return array(
			new ToolbarShortcut(
				id:      'cf-woocommerce-settings-pdf-invoice',
				title:   __( 'PDF-invoice', 'core-functionality' ),
				href:    admin_url( 'admin.php?page=wpo_wcpdf_options_page' ),
				label:   __( 'Link to', 'core-functionality' ) . ' ' . __( 'WooCommerce PDF-invoice Settings', 'core-functionality' ),
				orientation: Orientation::ADMIN,
			),
		);
	}

	#[Override]
	protected function get_menu_nodes(): array {
		return array();
	}
}
