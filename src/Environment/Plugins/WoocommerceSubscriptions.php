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
use function function_exists;
use function home_url;
use function wc_get_page_permalink;
use function get_term_by;
use function get_term_link;

/**
 * Class Woocommerce
 */
class WoocommerceSubscriptions extends AbstractPlugin {

	#[Override]
	public function get_id(): string {
		return 'woocommerce-subscriptions';
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
				id:      'cf-admin-woocommerce-subscriptions',
				title:   __( 'Subscriptions', 'dwp-cf' ),
				href:    admin_url( 'edit.php?post_type=shop_subscription' ),
				label:   __( 'Link to', 'dwp-cf' ) . ' ' . __( 'WooCommerce Subscriptions', 'dwp-cf' ),
				orientation: Orientation::ADMIN,
			),
			// Frontend Links - Safe lazy loading via PHP short closures!
			new ToolbarShortcut(
				id:      'cf-woocommerce-front-my-account-subscriptions',
 				title:   __( 'My Account: Subscriptions', 'dwp-cf' ),
				href:    fn() => function_exists( 'wc_get_page_permalink' ) ? wc_get_endpoint_url( 'orders', '', wc_get_page_permalink( 'myaccount' ) ) : home_url( __( '/myaccount/subscriptions/', 'dwp-cf' ) ),
				label:   __( 'Link to', 'dwp-cf' ) . ' ' . __( 'My Account: Subscriptions', 'dwp-cf' ),
				orientation: Orientation::FRONTEND,
			),
			new ToolbarShortcut(
				id:      'cf-view-course-subscriptions',
				title:   __( 'Course Payment Plans', 'dwp-cf' ),
				href:    fn() => $this->get_product_cat_url_by_slug( __( 'course-payment-plans', 'dwp-cf' ) ),
				label:   __( 'Link to', 'dwp-cf' ) . ' ' . __( 'WooCommerce Course Payment Plans', 'dwp-cf' ),
				orientation: Orientation::FRONTEND,
			),
		);
	}

	#[Override]
	protected function get_menu_nodes(): array {
		return array();
	}

	private function get_product_cat_url_by_slug( string $category_slug ): string {
		if ( empty( $category_slug ) ) {
			return '#';
		}
		$category = get_term_by( 'slug', $category_slug, 'product_cat' );
		if ( ! $category ) {
			return '#';
		}
		$category_url = get_term_link( $category, 'product_cat' );
		return $category_url;
	}
}
