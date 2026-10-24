<?php
/**
 * Admin Toolbar Node Registry.
 *
 * @package DeWittePrins\CoreFunctionality\Environment\Registers
 */

declare(strict_types=1);

namespace DeWittePrins\CoreFunctionality\Environment\Registers;

use DeWittePrins\CoreFunctionality\DTO\ToolbarShortcut;
use DeWittePrins\CoreFunctionality\Enums\ProviderStatus;

use function did_action;
use function is_callable;

/**
 * Class AdminToolbarRegister
 *
 * The catalogue of admin toolbar shortcuts offered by all known plugins, themes and the core,
 * with the status of each provider. The settings page uses the whole catalogue, the toolbar only
 * the nodes of active providers.
 *
 * The catalogue is filled once per request by a loader (set by the Environment), the first time
 * it is asked for after init.
 *
 * @since 1.0.0
 */
class AdminToolbarRegister {

	/**
	 * The nodes per provider.
	 *
	 * Structured as: array( 'woocommerce' => array( 'cf-woocommerce-orders' => ToolbarShortcut, ... ) )
	 *
	 * @var array<string, array<string, ToolbarShortcut>>
	 */
	private static array $nodes = array();

	/**
	 * The status per provider.
	 *
	 * @var array<string, ProviderStatus>
	 */
	private static array $statuses = array();

	/**
	 * Fills the catalogue; called once, on first use.
	 *
	 * @var callable|null
	 */
	private static $loader = null;

	/**
	 * Whether the loader has run.
	 *
	 * @var bool
	 */
	private static bool $loaded = false;

	/**
	 * Sets the callable that fills the catalogue on first use.
	 *
	 * @since  1.0.0
	 * @param  callable $loader Registers the nodes of every provider.
	 * @return void
	 */
	public static function set_loader( callable $loader ): void {
		self::$loader = $loader;
	}

	/**
	 * Registers the toolbar shortcuts of a provider.
	 *
	 * @since  1.0.0
	 * @param  string            $provider_id The get_id() of the plugin, theme or core guard.
	 * @param  ToolbarShortcut[] $shortcuts   The shortcuts the provider offers.
	 * @param  ProviderStatus    $status      Whether the provider is active, inactive or not installed.
	 * @return void
	 */
	public static function register( string $provider_id, array $shortcuts, ProviderStatus $status = ProviderStatus::ACTIVE ): void {
		self::$statuses[ $provider_id ] = $status;
		self::$nodes[ $provider_id ]  ??= array();

		foreach ( $shortcuts as $shortcut ) {
			if ( $shortcut instanceof ToolbarShortcut ) {
				self::$nodes[ $provider_id ][ $shortcut->id ] = $shortcut;
			}
		}
	}

	/**
	 * Returns the nodes of all known providers, active or not.
	 *
	 * @since  1.0.0
	 * @return array<string, array<string, ToolbarShortcut>> Provider id => node id => shortcut.
	 */
	public static function get_all(): array {
		self::load();

		return self::$nodes;
	}

	/**
	 * Returns the nodes of the active providers.
	 *
	 * @since  1.0.0
	 * @return array<string, array<string, ToolbarShortcut>> Provider id => node id => shortcut.
	 */
	public static function get_active(): array {
		self::load();

		$active = array();
		foreach ( self::$nodes as $provider_id => $shortcuts ) {
			if ( ProviderStatus::ACTIVE === self::$statuses[ $provider_id ] ) {
				$active[ $provider_id ] = $shortcuts;
			}
		}

		return $active;
	}

	/**
	 * Returns the status of a provider.
	 *
	 * @since  1.0.0
	 * @param  string $provider_id The get_id() of the plugin, theme or core guard.
	 * @return ProviderStatus|null The status, or null for an unknown provider.
	 */
	public static function get_status( string $provider_id ): ?ProviderStatus {
		self::load();

		return self::$statuses[ $provider_id ] ?? null;
	}

	/**
	 * Fills the catalogue once, through the loader.
	 *
	 * Not before init: the guards check their proofs on init.
	 *
	 * @since  1.0.0
	 * @return void
	 */
	private static function load(): void {
		if ( self::$loaded || ! is_callable( self::$loader ) || ! did_action( 'init' ) ) {
			return;
		}

		self::$loaded = true;
		( self::$loader )();
	}
}
