<?php
/**
 * WooCommerce Pre-Orders Verification Guard.
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
 * Class WoocommercePreOrders
 */
class WoocommercePreOrders extends AbstractPlugin {

	/**
	 * Returns the environment id of this plugin or theme.
	 *
	 * @since  1.0.0
	 * @return string The id, also the key in environment-index.php.
	 */
	#[Override]
	public function get_id(): string {
		return 'woocommerce-pre-orders';
	}

	/**
	 * Returns what must exist to prove the plugin has started.
	 *
	 * @since  1.0.0
	 * @return ProofRequirement The proof criteria.
	 */
	#[Override]
	protected function get_proof_requirements(): ProofRequirement {
		return new ProofRequirement( constants: 'WC_PRE_ORDERS_VERSION' );
	}

	/**
	 * Returns the admin toolbar links this plugin offers for the quick menu.
	 *
	 * @since  1.0.0
	 * @return ToolbarShortcut[] The toolbar links.
	 */
	#[Override]
	public function get_toolbar_nodes(): array {
		return array(
			new ToolbarShortcut(
				id:      'cf-woocommerce-pre-orders-view-my-account',
				title:   __( 'My Account: Pre-orders', 'core-functionality' ),
				href:    fn() => function_exists( 'wc_get_page_permalink' ) ? wc_get_endpoint_url( 'pre-orders', '', wc_get_page_permalink( 'myaccount' ) ) : home_url( __( '/myaccount/pre-orders/', 'core-functionality' ) ),
				label:   __( 'Link to', 'core-functionality' ) . ' ' . __( 'My Account: Pre-orders', 'core-functionality' ),
				orientation: Orientation::FRONTEND,
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
