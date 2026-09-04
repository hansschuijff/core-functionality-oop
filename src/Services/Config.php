<?php
/**
 * Configuration Registry and Cache Service.
 *
 * @package DeWittePrins\CoreFunctionality\Services
 * @since   4.0.0
 */

namespace DeWittePrins\CoreFunctionality\Services;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use DeWittePrins\CoreFunctionality\Plugin;

/**
 * Class Config
 *
 * Handles abstract configuration routing, static file caching, and resolves
 * disk-based default matrices with database-driven user settings.
 *
 * @since 4.0.0
 */
class Config {

	/**
	 * Central plugin controller reference.
	 *
	 * @var Plugin
	 */
	private Plugin $plugin;

	/**
	 * Database settings layer reference.
	 *
	 * @var Settings
	 */
	private Settings $settings;

	/**
	 * Internal runtime cache storage for resolved configuration indexes.
	 * Prevents repetitive file system hits by retaining arrays in memory [INDEX].
	 *
	 * @var array
	 */
	private array $config_cache = array();

	/**
	 * Config Constructor.
	 *
	 * @since 4.0.0
	 * @param Plugin   $plugin   The central orchestrator instance.
	 * @param Settings $settings The database storage service layer.
	 */
	public function __construct( Plugin $plugin, Settings $settings ) {
		$this->plugin   = $plugin;
		$this->settings = $settings;
	}

	/**
	 * The central, fully encapsulated configuration data gateway.
	 * Enforces strict cascading priorities, extendable via developers hooks [INDEX].
	 *
	 * @since  1.0.0
	 * @param  string      $config_key The abstract configuration identifier.
	 * @param  string|null $module_id  Optional abstract module identifier string.
	 * @return mixed The compiled configuration dataset, or null if unreadable.
	 */
	public function get( string $config_key, ?string $module_id = null ): mixed {
		// Step 1: Baseline defaults ophalen uit de bestandskanalen.
		$config_data = $this->load_config_file( $config_key, $module_id );

		// Step 2: Database-interrogatie (behalve voor de core module-index zelf) [INDEX].
		// if ( 'modules' !== $config_key && ! empty( $module_id ) ) {
			// Hier controleren we straks of de database-vinkjes de schijf-defaults overrulen [INDEX].
			// $config_data = $this->settings->apply_db_overrides( $config_data, $config_key, $module_id );
		// }.

		// Step 3: Genereer de dynamic filter hooks voor de runtime overrides [INDEX].
		if ( ! empty( $module_id ) ) {
			$filter_name = 'dwp_cf_config_' . $module_id . '_' . $config_key;
		} else {
			$filter_name = 'dwp_cf_config_' . $config_key;
		}

		return apply_filters( $filter_name, $config_data, $config_key, $module_id ); // phpcs:ignore WordPress.NamingConventions.ValidHookName.NotLowercase
	}

	/**
	 * Get config data from a config file.
	 *
	 * @since  1.0.0
	 * @param  string      $config_key The abstract configuration identifier.
	 * @param  string|null $module_id  Optional abstract module identifier string.
	 * @return mixed The compiled configuration dataset, or null if unreadable.
	 */
	private function load_config_file( string $config_key, ?string $module_id = null ): mixed {
		$file = $this->get_config_path( $config_key, $module_id );

		if ( is_readable( $file ) ) {
			// Load de baseline values van de config-file.
			return include $file;
		}

		return null;
	}

	/**
	 * Resolves the absolute disk path for a specific configuration key.
	 * Fully optimized with static runtime caching for blazing fast index resolution [INDEX].
	 *
	 * @since  1.0.0
	 * @param  string      $config_key The abstract configuration identifier.
	 * @param  string|null $module_id  Optional abstract module identifier string.
	 * @return string|false The fully qualified absolute file path on the disk, or false on failure.
	 */
	private function get_config_path( string $config_key, ?string $module_id = null ): string|false {
		if ( ! empty( $module_id ) ) {
			$routing_map = $this->get_config_filenames( $module_id );
		} else {
			$routing_map = $this->get_config_filenames();
		}

		if ( false === $routing_map ) {
			return false;
		}

		$filename    = $routing_map[ $config_key ] ?? $config_key;
		$config_base = $this->get_config_base( $module_id );

		if ( false === $config_base ) {
			return false;
		}

		return trailingslashit( $config_base ) . $filename . '.php';
	}

	/**
	 * Get the config that translates config_keys to config files for the plugin or a plugin module.
	 *
	 * @since  1.0.0
	 * @param  string|null $module_id Optional internal ID of a module in this plugin.
	 * @return array|false An array of keys and filenames, or false on failure.
	 */
	private function get_config_filenames( ?string $module_id = null ): array|false {
		if ( empty( $module_id ) ) {
			$key         = 'config-files';
			$config_base = $this->get_config_base();
			$filename    = 'config-index.php';
		} else {
			$key         = $module_id . '-config-files';
			$config_base = $this->get_config_base( $module_id );
			if ( false === $config_base ) {
				return false;
			}
			$filename = 'module-index.php';
		}
		$config_file_path = trailingslashit( $config_base ) . $filename;

		if ( empty( $this->get_config_cache( $key, array() ) ) ) {
			$this->set_config_cache( $key, $config_file_path, array() );
		}

		return $this->get_config_cache( $key, array() );
	}

	/**
	 * Gets a modules config base directory by module id.
	 *
	 * @since  1.0.0
	 * @param  string|null $module_id Optional internal ID of a module in this plugin.
	 * @return string|false Path if found, otherwise false.
	 */
	private function get_config_base( ?string $module_id = null ): string|false {
		if ( null === $module_id ) {
			return $this->plugin->get_data( 'config-dir' );
		}

		$modules_base_index = $this->get_modules_base_index();
		$module_path        = $modules_base_index[ $module_id ] ?? false;

		if ( false === $module_path ) {
			return false;
		}

		return trailingslashit( $this->plugin->get_data( 'config-dir' ) ) . $module_path;
	}

	/**
	 * Retrieves the master modules directory index mapping [INDEX].
	 *
	 * @since  1.0.0
	 * @return array The associative array linking module identifiers to paths.
	 */
	private function get_modules_base_index(): array {
		$key = 'modules_base_index';
		if ( empty( $this->get_config_cache( $key, array() ) ) ) {
			$path = trailingslashit( $this->plugin->get_data( 'config-dir' ) ) . 'modules/modules-index.php';
			$this->set_config_cache( $key, $path, array() );
		}

		return $this->get_config_cache( $key, array() );
	}

	/**
	 * Caches a configuration entry in the internal runtime storage [INDEX].
	 *
	 * @since 1.0.0
	 * @param string       $key           The internal reference token.
	 * @param string       $file          The absolute file path to load.
	 * @param string|array $default_value The default value if file is missing.
	 */
	private function set_config_cache( string $key, string $file, string|array $default_value = array() ): void {
		$this->config_cache[ $key ] = is_readable( $file )
			? include $file
			: $default_value;
	}

	/**
	 * Retrieves a cached configuration entry from the internal runtime storage [INDEX].
	 *
	 * @since  1.0.0
	 * @param  string       $key           The internal reference token.
	 * @param  string|array $default_value The default value if key is missing.
	 * @return string|array The cached configuration entry.
	 */
	private function get_config_cache( string $key, string|array $default_value = array() ): string|array {
		return $this->config_cache[ $key ] ?? $default_value;
	}
}
