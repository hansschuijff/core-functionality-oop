<?php
/**
 * Central Service Locator and Runtime Registry for De Witte Prins Core Functionality.
 *
 * @package DeWittePrins\CoreFunctionality
 * @since   4.0.0
 */

namespace DeWittePrins\CoreFunctionality;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class Core
 *
 * Acting as a unified service locator, registry, and state tracker for the
 * entire application ecosystem. Manages the lifecycle of modules and features.
 *
 * @since 4.0.0
 */
class Core {

	/**
	 * Tracks if the Core Functionality framework kernel has successfully booted and is ready.
	 *
	 * @since 4.0.0
	 * @var bool
	 */
	private static bool $cf_ready = false;

	/**
	 * Internal registry tracking currently active and booted core modules.
	 *
	 * @since 4.0.0
	 * @var array
	 */
	private static array $active_modules = array();

	/**
	 * Internal registry tracking currently active micro-features.
	 *
	 * @since 4.0.0
	 * @var array
	 */
	private static array $active_features = array();

	/**
	 * Internal registry storing instantiated core service objects.
	 *
	 * @since 4.0.0
	 * @var array
	 */
	private static array $services = array();

	/**
	 * Smart orchestration gateway for dependent plugins.
	 *
	 * Immediately executes the callback if the framework kernel is already booted,
	 * or defers execution to the global 'dwp_cf_ready' broadcast event.
	 *
	 * @since 4.0.0
	 * @param callable $callback The code or initialization logic to execute.
	 * @return void
	 */
	public static function call_when_ready( callable $callback ): void {
		if ( self::is_cf_ready() ) {
			call_user_func( $callback );
		} else {
			add_action( 'dwp_cf_ready', $callback );
		}
	}

	/**
	 * Sets the central framework kernel state to ready.
	 *
	 * @since 4.0.0
	 * @return void
	 */
	public static function set_cf_ready(): void {
		self::$cf_ready = true;
	}

	/**
	 * Verifies if the framework kernel is fully initialized and ready.
	 *
	 * @since 4.0.0
	 * @return bool True if booted and ready, false otherwise.
	 */
	public static function is_cf_ready(): bool {
		return self::$cf_ready;
	}

	/**
	 * Registers a newly instantiated service within the container.
	 *
	 * @since 4.0.0
	 * @param string $id      The unique identification key for the service.
	 * @param object $service The service instance object.
	 * @return void
	 */
	public static function register_service( string $id, object $service ): void {
		self::$services[ $id ] = $service;
	}

	/**
	 * Resolves and retrieves an active service instance from the container.
	 *
	 * @since 4.0.0
	 * @param string $id The unique identification key of the service.
	 * @return object|null The service object instance, or null if missing.
	 */
	public static function get_service( string $id ) {
		return self::$services[ $id ] ?? null;
	}

	/**
	 * Registers a module as successfully loaded and active.
	 *
	 * @since 4.0.0
	 * @param string $id The unique configuration ID of the module.
	 * @return void
	 */
	public static function register_active_module( string $id ): void {
		self::$active_modules[ $id ] = true;
	}

	/**
	 * Registers a specific micro-feature within a module as active.
	 *
	 * @since 4.0.0
	 * @param string $module_id  The configuration ID of the parent module.
	 * @param string $feature_id The unique configuration ID of the feature.
	 * @return void
	 */
	public static function register_active_feature( string $module_id, string $feature_id ): void {
		self::$active_features[ $module_id ][ $feature_id ] = true;
	}

	/**
	 * Checks if a specific module is currently active.
	 *
	 * @since 4.0.0
	 * @param string $id The unique configuration ID of the module.
	 * @return bool True if active, false otherwise.
	 */
	public static function is_active( string $id ): bool {
		return isset( self::$active_modules[ $id ] );
	}

	/**
	 * Checks if a specific micro-feature is currently operational.
	 *
	 * @since 4.0.0
	 * @param string $module_id  The configuration ID of the parent module.
	 * @param string $feature_id The unique configuration ID of the feature.
	 * @return bool True if fully functional, false otherwise.
	 */
	public static function is_feature_active( string $module_id, string $feature_id ): bool {
		return isset( self::$active_features[ $module_id ][ $feature_id ] );
	}
}
