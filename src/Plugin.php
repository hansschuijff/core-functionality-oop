<?php
/**
 * De Witte Prins Core Functionality Main Plugin Container Shell.
 *
 * @package DeWittePrins\CoreFunctionality
 * @since   1.0.0
 */

namespace DeWittePrins\CoreFunctionality;

use DeWittePrins\CoreFunctionality\Contracts\ModuleInterface;
use DeWittePrins\CoreFunctionality\Contracts\FeatureInterface;

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
	 * Internal runtime cache storage for resolved configuration indexes.
	 * Prevents repetitive file system hits by retaining arrays in memory.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	private array $config = array();

	/**
	 * Active execution timing array rules.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	private array $load_timing = array();

	/**
	 * Plugin constructor.
	 *
	 * Boots up paths, versions, and schedules the core lifecycle on plugins_loaded.
	 *
	 * @since 1.0.0
	 * @param string $root_file The absolute file path to the main plugin root file.
	 */
	public function __construct( string $root_file ) {
		if ( ! function_exists( 'get_plugin_data' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$base_dir = plugin_dir_path( $root_file );

		// BEHOUDEN: Je oorspronkelijke baseline data setups!
		$this->plugin_data = array(
			'version'    => get_plugin_data( $root_file )['Version'] ?? '1.0.0',
			'plugin-dir' => $base_dir,
			'plugin-url' => plugin_dir_url( $root_file ),
			'config-dir' => $base_dir . 'config/',
		);

		// BEHOUDEN: Dynamic Inversion of Control Registry Assignment.
		$this->register_config_setting( 'environment-index', 'dwp_environment_basenames' );

		$this->load_timing = get_option(
			'dwp_flight_timing',
			array(
				'hook'     => 'init',
				'priority' => 11,
			)
		);

		// GEÏNTEGREERD: Sluit direct aan in de WordPress-rij tijdens de plugin laad-volgorde.
		add_action( 'plugins_loaded', array( $this, 'init_lifecycle' ), 1 );
	}

	/**
	 * Prepares core framework services early during the plugins_loaded phase.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function init_lifecycle(): void {
		$mapper = new \DeWittePrins\CoreFunctionality\Services\EnvironmentMapper( $this );
		Core::register_service( 'environment_mapper', $mapper );

		$hook     = $this->load_timing['hook'];
		$priority = $this->load_timing['priority'];

		// GEÏNTEGREERD: Plan de uiteindelijke lancering in op de dynamic hook!
		add_action(
			$hook,
			function () {
				Core::set_cf_ready(); // Zet de global ready status omhoog.
				$this->launch();      // Schiet de modules en features in gang!
			},
			$priority
		);
	}

	/**
	 * Launches the modules based on strict environment readiness.
	 */
	public function launch(): void {
		$loader         = new ModuleLoader( $this );
		$loaded_modules = $loader->load_modules();

		foreach ( $loaded_modules as $module_id => $module ) {
			// Controleer de omgevings-eisen van de module via de Environment engine.
			if ( Environment::is_ready( $module->get_preflight_checks() ) ) {

				$module->launch();

				Core::register_active_module( $module_id );
			}
		}

		do_action( 'dwp_core_functionality_ready' );
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
	 * Resolves the absolute disk path for a specific configuration key using a two-tier index system.
	 * Fully optimized with static runtime caching for blazing fast index resolution.
	 *
	 * @since 1.0.0
	 * @param string      $config_key The abstract configuration identifier.
	 * @param string|null $module_id  Optional abstract module identifier string (e.g., 'admin_toolbar').
	 * @return string|false The fully qualified absolute file path on the disk, or false on failure.
	 */
	public function get_config_path( string $config_key, ?string $module_id = null ): string|false {
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
	 * @since 1.0.0
	 * @param string|null $module_id Optional internal ID of a module in this plugin.
	 * @return array|false An array of keys and filenames, or false if the modules config path could not be determined.
	 */
	public function get_config_filenames( ?string $module_id = null ): array|false {
		if ( empty( $module_id ) ) {
			$key         = 'config-files';
			$config_base = $this->get_config_base();
			$filename    = 'config-index.php';
		} else {
			$key = $module_id . '-config-files';
			// Get the base path of the modules config.
			$config_base = $this->get_config_base( $module_id );
			if ( false === $config_base ) {
				return false;
			}
			$filename = 'module-index.php';
		}
		$config_file_path = trailingslashit( $config_base ) . $filename;

		if ( empty( $this->get_config_cache( $key, array() ) ) ) {
			// Save config file to cache.
			$this->set_config_cache( $key, $config_file_path, array() );
		}

		return $this->get_config_cache( $key, array() );
	}

	/**
	 * Gets a modules config base directory by module id.
	 *
	 * @since 1.0.0
	 * @param string|null $module_id Optional internal ID of a module in this plugin.
	 * @return string|false Path if found, otherwise false.
	 */
	public function get_config_base( ?string $module_id = null ): string|false {

		if ( null === $module_id ) {
			return $this->get_data( 'config-dir' );
		}

		$modules_base_index = $this->get_modules_base_index();
		$module_path        = $modules_base_index[ $module_id ] ?? false;

		if ( false === $module_path ) {
			return false;
		}

		return trailingslashit( $this->get_data( 'config-dir' ) ) . $module_path;
	}

	/**
	 * Retrieves the master modules directory index mapping.
	 *
	 * @since 1.0.0
	 * @return array The associative array linking module identifiers to their respective subdirectory paths.
	 */
	public function get_modules_base_index(): array {
		$key = 'modules_base_index';
		if ( empty( $this->get_config_cache( $key, array() ) ) ) {
			$path = trailingslashit( $this->get_data( 'config-dir' ) ) . 'modules/modules-index.php';
			$this->set_config_cache( $key, $path, array() );
		}

		return $this->get_config_cache( $key, array() );
	}

	/**
	 * Caches a configuration entry in the internal runtime storage.
	 *
	 * @since 1.0.0
	 * @param string       $key           The internal reference token (e.g., 'modules_base_index').
	 * @param string       $file          The absolute file path to load and cache.
	 * @param string|array $default_value The default value to store if the file is missing or unreadable.
	 * @return void
	 */
	public function set_config_cache( string $key, string $file, string|array $default_value = array() ): void {
		$this->config[ $key ] = is_readable( $file )
			? include $file
			: $default_value;
	}

	/**
	 * Retrieves a cached configuration entry from the internal runtime storage.
	 *
	 * @since 1.0.0
	 * @param string       $key           The internal reference token (e.g., 'modules_base_index').
	 * @param string|array $default_value The default value to return if the key is missing.
	 * @return string|array The cached configuration entry, or the provided default if not found.
	 */
	public function get_config_cache( string $key, string|array $default_value = array() ): string|array {
		return $this->config[ $key ] ?? $default_value;
	}

	/**
	 * The central, fully encapsulated configuration data gateway.
	 * Enforces strict, lightweight string routing across the architecture using uniform identifier names.
	 *
	 * @since 1.0.0
	 * @param string      $config_key The abstract configuration identifier.
	 * @param string|null $module_id  Optional abstract module identifier string (e.g., 'admin_toolbar').
	 * @return mixed The compiled, potentially merged or overwritten configuration dataset.
	 */
	public function get_config( string $config_key, ?string $module_id = null ) {
		// Pass the uniform identifier token directly to the path resolver.
		$absolute_path = $this->get_config_path( $config_key, $module_id );

		if ( ! is_readable( $absolute_path ) ) {
			return null;
		}

		// Operational execution is completely decoupled from object instances.
		$defaults = include $absolute_path;

		$option_key = $this->config_setting_map[ $config_key ] ?? null;
		if ( empty( $option_key ) ) {
			return $defaults;
		}

		$user_settings = get_option( $option_key, '__dwp_missing__' );
		if ( '__dwp_missing__' === $user_settings ) {
			return $defaults;
		}

		if ( is_array( $defaults ) && is_array( $user_settings ) ) {
			return array_merge( $defaults, $user_settings );
		}

		return $user_settings;
	}
}
