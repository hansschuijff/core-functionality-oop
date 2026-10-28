<?php
/**
 * Multiple Add-to-Cart Feature.
 *
 * @package    DeWittePrins\CoreFunctionality
 * @subpackage Modules\WooCommerce\Features\MultiAddToCart
 * @since      1.0.0
 */

declare( strict_types=1 );

namespace DeWittePrins\CoreFunctionality\Modules\WooCommerce\Features\MultiAddToCart;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use DeWittePrins\CoreFunctionality\Abstracts\AbstractFeature;
use DeWittePrins\CoreFunctionality\DTO\ComponentMeta;
use Override;

use function __;
use function absint;
use function add_action;
use function apply_filters;
use function array_pad;
use function class_exists;
use function explode;
use function function_exists;
use function is_string;
use function remove_action;
use function sanitize_text_field;
use function str_contains;
use function trim;
use function wc_add_to_cart_message;
use function wc_get_cart_url;
use function wp_safe_redirect;
use function wp_unslash;

/**
 * Class MultiAddToCart
 *
 * Lets one add-to-cart link put several products in the cart, each with its own quantity:
 * ?add-to-cart=101:1,102:3 (product id, a colon and the quantity; a comma between products).
 * The ticket forms of Remote Tickets use it. A plain ?add-to-cart=101 stays WooCommerce's own.
 *
 * Every product passes WooCommerce's own add-to-cart validation (stock, limits, add-ons), and
 * the customer gets WooCommerce's usual message. Taken over from Remote Tickets Server Add-ons,
 * whose copy steps aside while both are active.
 *
 * @since 1.0.0
 */
class MultiAddToCart extends AbstractFeature {

	/**
	 * The callback of the same feature in Remote Tickets Server Add-ons, removed while CF has it.
	 *
	 * @var array{0: string, 1: string}
	 */
	private const LEGACY_HANDLER = array( 'DeWittePrins\RemoteTicketsServerAddons\WooCartExtension', 'process_batch_add_to_cart' );

	/**
	 * Returns the descriptive data of this feature.
	 *
	 * @since  1.0.0
	 * @return ComponentMeta Id, name, description and requirements.
	 */
	#[Override]
	public static function get_meta(): ComponentMeta {
		return new ComponentMeta(
			id:          'woocommerce_multi_add_to_cart',
			name:        __( 'Several products in the cart at once', 'core-functionality' ),
			description: __( 'One add-to-cart link adds several products with their own quantities: ?add-to-cart=101:1,102:3. Used by the ticket forms of Remote Tickets.', 'core-functionality' ),
		);
	}

	/**
	 * Hooks in before WooCommerce handles add-to-cart (wp_loaded 20).
	 *
	 * @since  1.0.0
	 * @return void
	 */
	#[Override]
	public function launch(): void {
		// Remote Tickets Server Add-ons hooks its copy when it loads, before CF launches (init 0).
		remove_action( 'wp_loaded', self::LEGACY_HANDLER, 15 );

		add_action( 'wp_loaded', array( $this, 'add_products_to_cart' ), 15 );
	}

	/**
	 * Adds the products of an add-to-cart request with a comma or a colon, then sends the customer
	 * to the cart. When none could be added, the page shows WooCommerce's notices why.
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public function add_products_to_cart(): void {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- An add-to-cart link, like WooCommerce's own, has no nonce.
		$request = isset( $_REQUEST['add-to-cart'] ) && is_string( $_REQUEST['add-to-cart'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['add-to-cart'] ) ) : '';

		if ( '' === $request || ( ! str_contains( $request, ',' ) && ! str_contains( $request, ':' ) ) ) {
			return;
		}

		if ( ! function_exists( 'WC' ) || null === WC()->cart || ! class_exists( 'WC_Form_Handler' ) ) {
			return;
		}

		// This request is ours: WooCommerce's own handler would read "101:1,102:3" as product 101.
		remove_action( 'wp_loaded', array( 'WC_Form_Handler', 'add_to_cart_action' ), 20 );

		$added = array();
		foreach ( explode( ',', $request ) as $item ) {
			list( $id, $quantity ) = array_pad( explode( ':', trim( $item ), 2 ), 2, '1' );

			$product_id = absint( apply_filters( 'woocommerce_add_to_cart_product_id', absint( $id ) ) ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- WooCommerce's own hook.
			$quantity   = '' === trim( $quantity ) ? 1 : absint( $quantity );
			if ( 0 === $product_id || 0 === $quantity ) {
				continue;
			}

			// The same check WooCommerce runs before adding (stock, purchase limits, required add-ons).
			if ( ! apply_filters( 'woocommerce_add_to_cart_validation', true, $product_id, $quantity ) ) { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- WooCommerce's own hook.
				continue;
			}

			if ( false !== WC()->cart->add_to_cart( $product_id, $quantity ) ) {
				$added[ $product_id ] = ( $added[ $product_id ] ?? 0 ) + $quantity;
			}
		}

		if ( array() === $added ) {
			return;
		}

		wc_add_to_cart_message( $added, true );
		wp_safe_redirect( wc_get_cart_url() );
		exit;
	}
}
