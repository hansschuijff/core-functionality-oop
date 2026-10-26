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
use function is_wp_error;

/**
 * Class Woocommerce
 */
class Woocommerce extends AbstractPlugin {

	/**
	 * Returns the environment id of this plugin or theme.
	 *
	 * @since  1.0.0
	 * @return string The id, also the key in environment-index.php.
	 */
	#[Override]
	public function get_id(): string {
		return 'woocommerce';
	}

	/**
	 * Returns what must exist to prove the plugin has started.
	 *
	 * @since  1.0.0
	 * @return ProofRequirement The proof criteria.
	 */
	#[Override]
	protected function get_proof_requirements(): ProofRequirement {
		return new ProofRequirement(
			classes: 'WooCommerce',
			actions: 'woocommerce_loaded'
		);
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
			// Admin Area Links - Safe to call directly because admin_url is purely string manipulation.
			new ToolbarShortcut(
				id:      'cf-woocommerce-orders',
				title:   __( 'Orders', 'core-functionality' ),
				href:    admin_url( 'edit.php?post_type=shop_order' ),
				label:   __( 'Link to', 'core-functionality' ) . ' ' . __( 'WooCommerce Orders', 'core-functionality' ),
				orientation: Orientation::ADMIN,
			),
			new ToolbarShortcut(
				id:      'cf-woocommerce-products',
				title:   __( 'Products', 'core-functionality' ),
				href:    admin_url( 'edit.php?post_type=product' ),
				label:   __( 'Link to', 'core-functionality' ) . ' ' . __( 'WooCommerce Products', 'core-functionality' ),
				orientation: Orientation::ADMIN,
			),
			new ToolbarShortcut(
				id:      'cf-woocommerce-coupons',
				title:   __( 'Coupons', 'core-functionality' ),
				href:    admin_url( 'edit.php?post_type=shop_coupon' ),
				label:   __( 'Link to', 'core-functionality' ) . ' ' . __( 'WooCommerce Coupons', 'core-functionality' ),
				orientation: Orientation::ADMIN,
			),
			new ToolbarShortcut(
				id:      'cf-woocommerce-product-cats',
				title:   __( 'Categories', 'core-functionality' ),
				href:    admin_url( 'edit-tags.php?taxonomy=product_cat&post_type=product' ),
				label:   __( 'Link to', 'core-functionality' ) . ' ' . __( 'WooCommerce Product Categories', 'core-functionality' ),
				orientation: Orientation::ADMIN,
			),
			new ToolbarShortcut(
				id:      'cf-woocommerce-analytics',
				title:   __( 'Statistics', 'core-functionality' ),
				href:    admin_url( 'admin.php?page=wc-admin&path=/analytics/revenue' ),
				label:   __( 'Link to', 'core-functionality' ) . ' ' . __( 'WooCommerce Sales Statistics', 'core-functionality' ),
				orientation: Orientation::ADMIN,
			),
			new ToolbarShortcut(
				id:      'cf-woocommerce-settings',
				title:   __( 'Settings', 'core-functionality' ),
				href:    admin_url( 'admin.php?page=wc-settings' ),
				label:   __( 'Link to', 'core-functionality' ) . ' ' . __( 'WooCommerce Settings', 'core-functionality' ),
				orientation: Orientation::ADMIN,
			),
			// Frontend Links - Safe lazy loading via PHP short closures!
			new ToolbarShortcut(
				id:      'cf-woocommerce-view-shop',
				title:   __( 'Shop', 'core-functionality' ),
				href:    fn() => function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( __( '/shop', 'core-functionality' ) ),
				label:   __( 'Link to', 'core-functionality' ) . ' ' . __( 'WooCommerce Shop', 'core-functionality' ),
				orientation: Orientation::FRONTEND,
			),
			new ToolbarShortcut(
				id:      'cf-woocommerce-view-my-account',
				title:   __( 'My Account', 'core-functionality' ),
				href:    fn() => function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'myaccount' ) : home_url( __( '/myaccount', 'core-functionality' ) ),
				label:   __( 'Link to', 'core-functionality' ) . ' ' . __( 'My Account', 'core-functionality' ),
				orientation: Orientation::FRONTEND,
			),
			new ToolbarShortcut(
				id:      'cf-woocommerce-view-my-account-orders',
				title:   __( 'My Account: Orders', 'core-functionality' ),
				href:    fn() => function_exists( 'wc_get_page_permalink' ) ? wc_get_endpoint_url( 'orders', '', wc_get_page_permalink( 'myaccount' ) ) : home_url( __( '/myaccount/orders/', 'core-functionality' ) ),
				label:   __( 'Link to', 'core-functionality' ) . ' ' . __( 'My Account: Orders', 'core-functionality' ),
				orientation: Orientation::FRONTEND,
			),
			new ToolbarShortcut(
				id:      'cf-woocommerce-view-my-account-downloads',
				title:   __( 'My Account: Downloads', 'core-functionality' ),
				href:    fn() => function_exists( 'wc_get_page_permalink' ) ? wc_get_endpoint_url( 'downloads', '', wc_get_page_permalink( 'myaccount' ) ) : home_url( __( '/myaccount/downloads/', 'core-functionality' ) ),
				label:   __( 'Link to', 'core-functionality' ) . ' ' . __( 'My Account: Downloads', 'core-functionality' ),
				orientation: Orientation::FRONTEND,
			),
			new ToolbarShortcut(
				id:      'cf-woocommerce-view-my-account-edit-address',
				title:   __( 'My Account: Addresses', 'core-functionality' ),
				href:    fn() => function_exists( 'wc_get_page_permalink' ) ? wc_get_endpoint_url( 'edit-address', '', wc_get_page_permalink( 'myaccount' ) ) : home_url( __( '/myaccount/edit-address/', 'core-functionality' ) ),
				label:   __( 'Link to', 'core-functionality' ) . ' ' . __( 'My Account: Addresses', 'core-functionality' ),
				orientation: Orientation::FRONTEND,
			),
			new ToolbarShortcut(
				id:      'cf-woocommerce-view-my-account-edit-account',
				title:   __( 'My Account: Account details', 'core-functionality' ),
				href:    fn() => function_exists( 'wc_get_page_permalink' ) ? wc_get_endpoint_url( 'edit-account', '', wc_get_page_permalink( 'myaccount' ) ) : home_url( __( '/myaccount/edit-account/', 'core-functionality' ) ),
				label:   __( 'Link to', 'core-functionality' ) . ' ' . __( 'My Account: Account details', 'core-functionality' ),
				orientation: Orientation::FRONTEND,
			),
			new ToolbarShortcut(
				title:   __( 'Courses', 'core-functionality' ),
				id:      'cf-woocommerce-view-shop-courses',
				href:    fn() => $this->get_product_cat_url_by_slug( __( 'cursus-opleiding', 'core-functionality' ) ),
				label:   __( 'Link to', 'core-functionality' ) . ' ' . __( 'WooCommerce Courses', 'core-functionality' ),
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

	/**
	 * Returns the front-end URL of a product category.
	 *
	 * @since  1.0.0
	 * @param  string $category_slug The product category slug.
	 * @return string The category URL, or '#' when the category does not exist.
	 */
	private function get_product_cat_url_by_slug( string $category_slug ): string {
		if ( empty( $category_slug ) ) {
			return '#';
		}
		$category = get_term_by( 'slug', $category_slug, 'product_cat' );
		if ( ! $category ) {
			return '#';
		}
		$category_url = get_term_link( $category, 'product_cat' );

		return is_wp_error( $category_url ) ? '#' : $category_url;
	}
}
