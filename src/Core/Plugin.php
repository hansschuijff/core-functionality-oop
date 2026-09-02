<?php
/**
 * De Witte Prins Core Functionality Main Plugin Container Shell.
 *
 * @package DeWittePrins\Core
 * @since   1.0.0
 */

namespace DeWittePrins\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class Plugin
 *
 * Encapsulates global root plugin configurations and isolates file system routing.
 * Manages dynamic runtime relationships linking abstract configurations to database options.
 *
 * @since 1.0.0
 */
class Plugin {

	/**
	 * Internal cache index storage holding absolute paths and directories.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	private array $plugin_data = array();

	/**
	 * Internal mapping registry linking abstract config keys to their registered database option keys.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	private array $config_setting_map = array();

	/**
	 * Plugin constructor.
	 *
	 * @since 1.0.0
	 * @param string $root_file The absolute physical disk path pointing to the main execution file.
	 */
	public function __construct( string $root_file ) {
		if ( ! function_exists( 'get_plugin_data' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$base_dir = plugin_dir_path( $root_file );

		$this->plugin_data = array(
			'version'    => get_plugin_data( $root_file )['Version'] ?? '1.0.0',
			'plugin-dir' => $base_dir,
			'plugin-url' => plugin_dir_url( $root_file ),
			'config-dir' => $base_dir . 'config/',
		);

		// The knowledge about the database option is registered exactly where it is born.
		$this->register_config_setting( 'environment-mapping', 'dwp_environment_basenames' );
	}

	/**
	 * Registers a runtime relationship between an abstract configuration key and a database option key.
	 *
	 * @since 1.0.0
	 * @param string $config_key The abstract configuration identifier (e.g., 'environment-mapping').
	 * @param string $option_key The actual WordPress option database key (e.g., 'dwp_environment_basenames').
	 * @return void
	 */
	public function register_config_setting( string $config_key, string $option_key ): void {
		$this->config_setting_map[ $config_key ] = $option_key;
	}

	/**
	 * Resolves structural file attributes and directory roots from the central registry.
	 *
	 * @since 1.0.0
	 * @param string $key Reference mapping selector (e.g., 'version', 'config-dir').
	 * @return string Target layout value string, or an empty string configuration if missing.
	 */
	public function get_data( string $key ): string {
		return $this->plugin_data[ $key ] ?? '';
	}

	/**
	 * Resolves the absolute disk path for a specific configuration file.
	 *
	 * @since 1.0.0
	 * @param string $config_key The abstract configuration identifier.
	 * @return string The fully qualified absolute file path on the disk.
	 */
	public function get_config_path( string $config_key ): string {
		return $this->plugin_data['config-dir'] . $config_key . '.php';
	}

	/**
	 * The central, fully encapsulated configuration data gateway.
	 * Automatically checks for registered database options and merges them seamlessly behind the scenes.
	 *
	 * @since 1.0.0
	 * @param string $config_key The abstract configuration identifier (e.g., 'environment-mapping').
	 * @return array The compiled, potentially merged configuration array dataset.
	 */
	/**
	 * The central, fully encapsulated configuration data gateway.
	 * Automatically checks for registered database options and merges or overwrites them seamlessly.
	 * Supports both complex datasets (arrays) and singular execution parameters (strings, bools, ints).
	 *
	 * @since 1.0.0
	 * @param string $config_key The abstract configuration identifier (e.g., 'environment-mapping' or 'environment-type').
	 * @return mixed The compiled, potentially merged or overwritten configuration dataset.
	 */
	public function get_config( string $config_key ) {
		// 1. Fetch the static software baseline defaults from the disk.
		$absolute_path = $this->get_config_path( $config_key );

		if ( ! is_readable( $absolute_path ) ) {
			return null; // Return null if the mapped configuration source does not exist.
		}

		$defaults = include $absolute_path;

		// 2. INTERNAL AUTOMATION: Verify if this config key has an active database relationship.
		$option_key = $this->config_setting_map[ $config_key ] ?? null;

		// If no option mapping is found, return the baseline data instantly.
		if ( empty( $option_key ) ) {
			return $defaults;
		}

		// 3. Fetch the dynamic user settings from the WordPress options table.
		// We use a unique string as a distinct fallback indicator to verify if the option actually exists.
		$user_settings = get_option( $option_key, '__dwp_missing__' );

		if ( '__dwp_missing__' === $user_settings ) {
			return $defaults; // No database overwrite found, return baseline.
		}

		// 4. DYNAMIC SYNTHESIS LAYER: Treat arrays and scalars with different logic! 🎯
		if ( is_array( $defaults ) ) {
			if ( is_array( $user_settings ) ) {
				return array_merge( $defaults, $user_settings ); // Merge array layers.
			}
			return $defaults; // Fail-safe fallback if the database contains corrupt non-array data.
		}

		// If the config baseline is a scalar (string, int, bool), the database option overwrites it completely!
		return $user_settings;
	}
}
