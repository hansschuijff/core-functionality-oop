<?php
/**
 * WooCommerce Customer/Order CSV Export Verification Guard.
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
 * Class WoocommerceCustomerOrderCsvExport
 */
class WoocommerceCustomerOrderCsvExport extends AbstractPlugin {

	#[Override]
	public function get_id(): string {
		return 'woocommerce-customer-order-csv-export';
	}

	// #[Override]
	protected function get_proof_requirements(): ProofRequirement {
		return new ProofRequirement( classes: 'WC_Customer_Order_CSV_Export_Loader' );
	}

	#[Override]
	protected function get_toolbar_nodes(): array {
		return array(
			new ToolbarShortcut(
				id:      'cf-woocommerce-csv-export',
				title:   __( 'Shop Order CSV Export', 'core-functionality' ),
				href:    admin_url( 'admin.php?page=wc_customer_order_csv_export' ),
				label:   __( 'Link to', 'core-functionality' ) . ' ' . __( 'WooCommerce Shop Order CSV Export', 'core-functionality' ),
				orientation: Orientation::ADMIN,
			),
		);
	}

	#[Override]
	protected function get_menu_nodes(): array {
		return array();
	}
}
