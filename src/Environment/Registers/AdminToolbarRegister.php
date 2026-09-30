<?php
/**
 * Admin Toolbar Node Registry.
 *
 * @package DeWittePrins\CoreFunctionality\Environment\Registers
 */

declare(strict_types=1);

namespace DeWittePrins\CoreFunctionality\Environment\Registers;

use DeWittePrins\CoreFunctionality\DTO\ToolbarShortcut;

/**
 * Class AdminToolbarRegister
 *
 * Provides a centralized runtime cache for storing and retrieving validated toolbar shortcut DTOs.
 *
 * @since 1.0.0
 */
class AdminToolbarRegister {

	/**
	 * Central runtime cache storage for toolbar nodes.
	 *
	 * Structured as: array( 'woocommerce' => array( 'woo_orders' => ToolbarShortcut, ... ) )
	 *
	 * @var array<string, array<string, ToolbarShortcut>>
	 */
	private static array $nodes = array();

	/**
	 * Registers a collection of toolbar shortcut DTOs for a specific environment provider.
	 *
	 * Prevents duplicate registry entries by utilizing unique string identifiers as keys.
	 *
	 * @param string            $provider_id The get_id() token of the registering plugin or theme component.
	 * @param ToolbarShortcut[] $shortcuts   Array of validated toolbar shortcut objects.
	 * @return void
	 */
	public static function register( string $provider_id, array $shortcuts ): void {
		if ( ! isset( self::$nodes[ $provider_id ] ) ) {
			self::$nodes[ $provider_id ] = array();
		}

		foreach ( $shortcuts as $shortcut ) {
			if ( $shortcut instanceof ToolbarShortcut ) {
				// Keying by individual shortcut ID guarantees isolation per component.
				self::$nodes[ $provider_id ][ $shortcut->id ] = $shortcut;
			}
		}
	}

	/**
	 * Polymorphic retrieval method to query the toolbar master registry database.
	 *
	 * Supports three filter granularities based on provided arguments.
	 *
	 * @param string|null $provider_id Optional structural component token (e.g., 'woocommerce').
	 * @param string|null $node_id     Optional specific node identifier (e.g., 'woo_admin_orders').
	 * @return mixed Returns the entire multidimensional array, a subset collection, a single DTO, or null.
	 */
	public static function get( ?string $provider_id = null, ?string $node_id = null ): mixed {
		// Case 1: No arguments supplied -> Requesting the entire master registry library
		if ( null === $provider_id ) {
			return self::$nodes;
		}

		// Case 2: Provider and node ID supplied -> Requesting a single specific node target
		if ( null !== $node_id ) {
			return self::$nodes[ $provider_id ][ $node_id ] ?? null;
		}

		// Case 3: Only provider ID supplied -> Requesting all nodes belonging to this component context
		return self::$nodes[ $provider_id ] ?? array();
	}
}
