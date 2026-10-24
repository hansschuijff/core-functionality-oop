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
class Woocommerce extends AbstractPlugin {

	#[Override]
	public function get_id(): string {
		return 'woocommerce';
	}

	#[Override]
	protected function get_proof_requirements(): ProofRequirement {
		return new ProofRequirement(
			classes: 'WooCommerce',
			actions: 'woocommerce_loaded'
		);
	}

	#[Override]
	protected function get_toolbar_nodes(): array {
		return array(
			// Admin Area Links - Safe to call directly because admin_url is purely string manipulation
			new ToolbarShortcut(
				id:      'cf-admin-woocommerce-orders',
 				title:   __( 'Orders', 'core-functionality' ),
				href:    admin_url( 'edit.php?post_type=shop_order' ),
				label:   __( 'Link to', 'core-functionality' ) . ' ' . __( 'WooCommerce Orders', 'core-functionality' ),
				orientation: Orientation::ADMIN,
			),
			new ToolbarShortcut(
				id:      'cf-admin-woocommerce-products',
				title:   __( 'Products', 'core-functionality' ),
				href:    admin_url( 'edit.php?post_type=product' ),
				label:   __( 'Link to', 'core-functionality' ) . ' ' . __( 'WooCommerce Products', 'core-functionality' ),
				orientation: Orientation::ADMIN,
			),
			new ToolbarShortcut(
				id:      'cf-admin-woocommerce-coupons',
 				title:   __( 'Coupons', 'core-functionality' ),
				href:    admin_url( 'edit.php?post_type=shop_coupon' ),
				label:   __( 'Link to', 'core-functionality' ) . ' ' . __( 'WooCommerce Coupons', 'core-functionality' ),
				orientation: Orientation::ADMIN,
			),
			new ToolbarShortcut(
				id:      'cf-admin-woocommerce-product-cats',
 				title:   __( 'Categories', 'core-functionality' ),
				href:    admin_url( 'edit-tags.php?taxonomy=product_cat&post_type=product' ),
				label:   __( 'Link to', 'core-functionality' ) . ' ' . __( 'WooCommerce Product Categories', 'core-functionality' ),
				orientation: Orientation::ADMIN,
			),
			new ToolbarShortcut(
				id:      'cf-admin-woocommerce-analytics',
 				title:   __( 'Statistics', 'core-functionality' ),
				href:    admin_url( 'admin.php?page=wc-admin&path=/analytics/revenue' ),
				label:   __( 'Link to', 'core-functionality' ) . ' ' . __( 'WooCommerce Sales Statistics', 'core-functionality' ),
				orientation: Orientation::ADMIN,
			),
			new ToolbarShortcut(
				id:      'cf-admin-woocommerce-settings-menu',
 				title:   __( 'Settings', 'core-functionality' ),
				href:    admin_url( 'admin.php?page=wc-settings' ),
				label:   __( 'Link to', 'core-functionality' ) . ' ' . __(  'WooCommerce Settings', 'core-functionality' ),
				orientation: Orientation::ADMIN,
			),
			// Frontend Links - Safe lazy loading via PHP short closures!
			new ToolbarShortcut(
				id:      'cf-woocommerce-front-shop',
				title:   __( 'Shop', 'core-functionality' ),
				href:    fn() => function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( __( '/shop', 'core-functionality' ) ),
				label:   __( 'Link to', 'core-functionality' ) . ' ' . __( 'WooCommerce Shop', 'core-functionality' ),
				orientation: Orientation::FRONTEND,
			),
			new ToolbarShortcut(
				id:      'cf-woocommerce-front-my-account',
 				title:   __( 'My Account', 'core-functionality' ),
				href:    fn() => function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'myaccount' ) : home_url( __( '/myaccount', 'core-functionality' ) ),
				label:   __( 'Link to', 'core-functionality' ) . ' ' . __( 'My Account', 'core-functionality' ),
				orientation: Orientation::FRONTEND,
			),
			new ToolbarShortcut(
				id:      'cf-woocommerce-front-my-account-orders',
 				title:   __( 'My Account: Orders', 'core-functionality' ),
				href:    fn() => function_exists( 'wc_get_page_permalink' ) ? wc_get_endpoint_url( 'orders', '', wc_get_page_permalink( 'myaccount' ) ) : home_url( __( '/myaccount/orders/', 'core-functionality' ) ),
				label:   __( 'Link to', 'core-functionality' ) . ' ' . __( 'My Account: Orders', 'core-functionality' ),
				orientation: Orientation::FRONTEND,
			),
			new ToolbarShortcut(
				id:      'cf-woocommerce-front-my-account-downloads',
 				title:   __( 'My Account: Downloads', 'core-functionality' ),
				href:    fn() => function_exists( 'wc_get_page_permalink' ) ? wc_get_endpoint_url( 'downloads', '', wc_get_page_permalink( 'myaccount' ) ) : home_url( __( '/myaccount/downloads/', 'core-functionality' ) ),
				label:   __( 'Link to', 'core-functionality' ) . ' ' . __( 'My Account: Downloads', 'core-functionality' ),
				orientation: Orientation::FRONTEND,
			),
			new ToolbarShortcut(
				id:      'cf-woocommerce-front-my-account-edit-address',
 				title:   __( 'My Account: Addresses', 'core-functionality' ),
				href:    fn() => function_exists( 'wc_get_page_permalink' ) ? wc_get_endpoint_url( 'edit-address', '', wc_get_page_permalink( 'myaccount' ) ) : home_url( __( '/myaccount/edit-address/', 'core-functionality' ) ),
				label:   __( 'Link to', 'core-functionality' ) . ' ' . __( 'My Account: Addresses', 'core-functionality' ),
				orientation: Orientation::FRONTEND,
			),
			new ToolbarShortcut(
				id:      'cf-woocommerce-front-my-account-edit-account',
 				title:   __( 'My Account: Account details', 'core-functionality' ),
				href:    fn() => function_exists( 'wc_get_page_permalink' ) ? wc_get_endpoint_url( 'edit-account', '', wc_get_page_permalink( 'myaccount' ) ) : home_url( __( '/myaccount/edit-account/', 'core-functionality' ) ),
				label:   __( 'Link to', 'core-functionality' ) . ' ' . __( 'My Account: Account details', 'core-functionality' ),
				orientation: Orientation::FRONTEND,
			),
			new ToolbarShortcut(
 				title:   __( 'Courses', 'core-functionality' ),
				id:      'cf-view-shop-courses',
				href:    fn() => $this->get_product_cat_url_by_slug( __( 'cursus-opleiding', 'core-functionality' ) ),
				label:   __( 'Link to', 'core-functionality' ) . ' ' . __( 'WooCommerce Courses', 'core-functionality' ),
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
		$category_slug = 'schoenen'; // Vervang door de slug van jouw categorie
		$category = get_term_by( 'slug', $category_slug, 'product_cat' );
		if ( ! $category ) {
			return '#';
		}
		$category_url = get_term_link( $category, 'product_cat' );
		return $category_url;
	}
}
