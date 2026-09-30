<?php
/**
 * Admin Menu Item Registry.
 *
 * @package DeWittePrins\CoreFunctionality\Environment\Registers
 */

declare(strict_types=1);

namespace DeWittePrins\CoreFunctionality\Environment\Registers;

use DeWittePrins\CoreFunctionality\DTO\AdminMenuBlock;

/**
 * Class AdminMenuRegister
 *
 * Provides a centralized runtime cache for storing and retrieving validated admin menu block DTOs.
 *
 * @since 1.0.0
 */
class AdminMenuRegister {

	/**
	 * Central runtime cache storage for admin menu items.
	 *
	 * Structured as: array( 'woocommerce' => array( 'woo_admin_orders' => AdminMenuBlock, ... ) )
	 *
	 * @var array<string, array<string, AdminMenuBlock>>
	 */
	private static array $items = array();

	/**
	 * Registers a collection of admin menu block DTOs for a specific environment provider.
	 *
	 * Prevents duplicate registry entries by utilizing unique string sub-identifiers as keys.
	 *
	 * @param string           $provider_id The get_id() token of the registering plugin or theme component.
	 * @param AdminMenuBlock[] $menu_items  Array of validated admin menu block objects.
	 * @return void
	 */
	public static function register( string $provider_id, array $menu_items ): void {
		if ( ! isset( self::$items[ $provider_id ] ) ) {
			self::$items[ $provider_id ] = array();
		}

		foreach ( $menu_items as $item ) {
			if ( $item instanceof AdminMenuBlock ) {
				self::$items[ $provider_id ][ $item->id ] = $item;
			}
		}
	}

	/**
	 * Polymorphic retrieval method to query the admin menu master registry database.
	 *
	 * Supports three filter granularities based on provided arguments.
	 *
	 * @param string|null $provider_id Optional structural component token (e.g., 'woocommerce').
	 * @param string|null $item_id     Optional specific item identifier (e.g., 'woo_admin_orders').
	 * @return mixed Returns the entire multidimensional array, a subset collection, a single DTO, or null.
	 */
	public static function get( ?string $provider_id = null, ?string $item_id = null ): mixed {
		if ( null === $provider_id ) {
			return self::$items;
		}

		if ( null !== $item_id ) {
			return self::$items[ $provider_id ][ $item_id ] ?? null;
		}

		return self::$items[ $provider_id ] ?? array();
	}
}
