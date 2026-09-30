<?php
/**
 * Global Public API Gateway Shroud for External Extensions.
 *
 * @package DeWittePrins\CoreFunctionality\Api
 * @since   1.0.0
 */

namespace DeWittePrins\CoreFunctionality\Api;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use DeWittePrins\CoreFunctionality\Plugin;

/**
 * Class CF
 *
 * Sovereign static storefront tracking the active framework execution state.
 *
 * @since 1.0.0
 */
final class CF {

	/**
	 * Central core plugin controller instance reference.
	 *
	 * @var Plugin|null
	 */
	private static ?Plugin $plugin = null;

	/**
	 * Initializes the public gateway with the live framework container context.
	 *
	 * @since  1.0.0
	 * @param  Plugin $plugin The active root plugin container instance.
	 * @return void
	 */
	public static function initialize( Plugin $plugin ): void {
		if ( null === self::$plugin ) {
			self::$plugin = $plugin;
		}
	}

	/**
	 * External API: Checks if the entire framework is fully booted and ready for service.
	 *
	 * @since  1.0.0
	 * @return bool True if fully operational, false otherwise.
	 */
	public static function is_ready(): bool {
		if ( null === self::$plugin ) {
			return false;
		}
		return self::$plugin->is_ready();
	}

	/**
	 * External API: Checks if a specific module context is currently active in the ecosystem.
	 *
	 * @since  1.0.0
	 * @param  string $module_class_name The Fully Qualified Class Name of the target module.
	 * @return bool                      True if active, false otherwise.
	 */
	public static function is_module_active( string $module_class_name ): bool {
		if ( null === self::$plugin ) {
			return false;
		}
		return self::$plugin->components_register->is_enabled( $module_class_name );
	}

	/**
	 * External API: Safely retrieves the configuration metadata paths or version strings.
	 *
	 * @since  1.0.0
	 * @param  string $key The targeted infrastructure key (e.g., 'version', 'plugin-url').
	 * @return string      The resolved path configuration string, or empty if unmapped.
	 */
	public static function get_info( string $key ): string {
		if ( null === self::$plugin ) {
			return '';
		}
		return self::$plugin->get_data( $key );
	}
}
