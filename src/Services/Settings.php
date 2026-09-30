<?php
/**
 * File containing the Settings class.
 *
 * @package DeWittePrins\CoreFunctionality\Services
 * @author  Hans Schuijff <@hansschuijff>
 * @license GPL-2.0
 * @since   1.0.0
 */

declare( strict_types=1 );

namespace DeWittePrins\CoreFunctionality\Services;

use DeWittePrins\CoreFunctionality\Diagnostics\Logger;
use DeWittePrins\CoreFunctionality\Admin\Services\Settings\Sections\OptionsManagementSettings;
use RuntimeException;

use function __;
use function trailingslashit;
use function file_exists;
use function basename;
use function is_array;
use function array_replace_recursive;
use function defined;
use function _doing_it_wrong;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Settings
 *
 * Sovereign authority managing encapsulated configuration files layout.
 * Functions strictly on a filesystem root anchor without core parent coupling.
 *
 * @since 1.0.0
 */
class Settings {

	/**
	 * The absolute base directory path where this service rules.
	 *
	 * @var string
	 */
	private string $base_settings_dir;

	/**
	 * Map tracking internal module identifiers to physical kebab-case directory layout.
	 *
	 * @var array<string, string>
	 */
	private array $modules_settings_location = array();

	/**
	 * Map tracking dynamic forms fields keys to physical filenames tokens.
	 *
	 * @var array<string, string>
	 */
	private array $key_slugs = array();

	/**
	 * In-memory runtime cache matrix for merged configuration results.
	 *
	 * @var array<string, array<string, mixed>>
	 */
	private array $runtime_cache = array();

	/**
	 * An instance of the Options option vault.
	 *
	 * @var Options An option vault adding key-management including token-to-key translation to wp options.
	 */
	private Options $options;

	/**
	 * Settings Constructor.
	 *
	 * @since 1.0.0
	 * @param string $base_settings_dir The absolute root directory context for settings files.
	 * @throws RuntimeException          If critical framework configuration files are missing on disk.
	 */
	public function __construct( string $base_settings_dir ) {
		$this->base_settings_dir         = trailingslashit( $base_settings_dir );
		$this->modules_settings_location = $this->setup_module_settings_dirs();
		$this->key_slugs                 = $this->setup_key_slugs();
		$this->options                   = new Options( $this );
	}

	/**
	 * Getter for settings layers.
	 *
	 * @since  1.0.0
	 * @param  string $key               The targeted configuration identifier string.
	 * @param  string $module_id         Optional. The specific context folder or component token. Default ''.
	 * @param  bool   $reset_to_defaults Optional. Force resolution to bypass customized data and return raw file defaults. Default false.
	 * @param  mixed  $default           Optional. The value that will be returned when there are no settings. Default false.
	 * @return mixed                     The combined multi-layered configuration dataset.
	 */
	public function get( string $key, string $module_id = '', bool $reset_to_defaults = false, mixed $default = false ): mixed {
		// Make sure context is always filled, because it is used as an array key.
		$module_id = ( ! empty( $module_id ) ) ? $module_id : 'cf-general';

		if ( true === $reset_to_defaults ) {
			return $this->get_settings_file( $key, $module_id );
		}

		if ( isset( $this->runtime_cache[ $module_id ][ $key ] ) ) {
			return $this->runtime_cache[ $module_id ][ $key ];
		}

		$settings_file = $this->get_settings_file( $key, $module_id );
		$option        = $this->options->get( $key, $default );

		if ( null === $settings_file ) {
			$settings = $option;
		} else {
			$settings = $settings_file;

			if ( is_array( $settings_file ) && is_array( $option ) ) {
				$settings = array_replace_recursive( $settings_file, $option );
			} else {
				if ( false !== $option ) {
					$settings = $option;
				}
			}
		}

		if ( ! isset( $this->runtime_cache[ $module_id ] ) ) {
			$this->runtime_cache[ $module_id ] = array();
		}
		$this->runtime_cache[ $module_id ][ $key ] = $settings;

		return $settings;
	}

	/**
	 * Direct route to the database via the Options Vault, bypassing token translation.
	 *
	 * Used for standard wp_* options or dynamically generated plugin keys.
	 *
	 * @param string $key     The exact database option key.
	 * @param mixed  $default Fallback value if the option does not exist.
	 * @return mixed
	 */
	public function get_unmanaged( string $key, mixed $default = null ): mixed {
		return $this->options->get_unmanaged( $key, $default );
	}

	/**
	 * Persists updated configuration records into the unified options key.
	 *
	 * @since  1.0.0
	 * @param  string $key   The unique configuration settings identifier string.
	 * @param  mixed  $value The dataset array or string value to store.
	 * @return bool          True on successful database update, false otherwise.
	 */
	public function save( string $key, mixed $value ): bool {
		return $this->options->update( $key, $value );
	}

	/**
	 * Purges specific customized option records to fallback onto raw defaults if they exist.
	 *
	 * @since  1.0.0
	 * @param  string $key The unique configuration settings identifier string to wipe.
	 * @return bool        True on successful database purge or missing keys, false otherwise.
	 */
	public function reset( string $key ): bool {
		return $this->delete( $key );
	}

	/**
	 * Purges specific customized option record.
	 *
	 * @since  1.0.0
	 * @param  string $key The unique identifier string to wipe.
	 * @return bool        True on successful database purge, false otherwise.
	 */
	public function delete( string $key ): bool {
		return $this->options->delete( $key );
	}

	/**
	 * Dynamically includes the module locations mapping array from disk.
	 *
	 * @since  1.0.0
	 * @return array
	 * @throws RuntimeException If the setup configuration file is missing.
	 */
	private function setup_module_settings_dirs(): array {
		$file = $this->base_settings_dir . 'setup/modules-settings-path.php';

		if ( ! file_exists( $file ) ) {
			throw new RuntimeException( 'Vitale configuratie ontbreekt op de schijf: modules-settings-path.php' );
		}

		return (array) require $file;
	}

	/**
	 * Dynamically includes the settings key translations array from disk.
	 *
	 * @since  1.0.0
	 * @return array
	 * @throws RuntimeException If the translation setup file is missing.
	 */
	private function setup_key_slugs(): array {
		$file = $this->base_settings_dir . 'setup/key-slugs-translation.php';

		if ( ! file_exists( $file ) ) {
			throw new RuntimeException( 'Vitale configuratie ontbreekt op de schijf: ' . $file );
		}

		return (array) require $file;
	}

	/**
	 * Translates a dynamic database settings key to its configuration filename.
	 *
	 * @since  1.0.0
	 * @param  string $key       The settings key token.
	 * @param  string $module_id The id of a module or cf-general.
	 * @return string|false      The physical file name slug, or false if unmapped.
	 */
	private function get_file_slug( string $key, string $module_id ): string|false {
		$slug = ! empty( $this->key_slugs[ $key ] ) ? $this->key_slugs[ $key ] : false;

		if ( false === $slug ) {
			if ( 'cf-general' === $module_id ) {
				return basename( $key );
			}
			return false;
		}

		return $slug;
	}

	/**
	 * Resolves the absolute baseline directory path for a specific module node branch.
	 *
	 * @since  1.0.0
	 * @param  string $module_id The internal identifier string.
	 * @return string|false      The absolute destination directory path, or false if unmapped.
	 */
	private function get_setting_base_dir( string $module_id ): string|false {
		if ( 'cf-general' !== $module_id && empty( $this->modules_settings_location[ $module_id ] ) ) {
			return false;
		}

		$path_base = trailingslashit( $this->base_settings_dir );
		$sub_path  = 'cf-general' === $module_id
			? ''
			: trailingslashit( 'modules/' . $this->modules_settings_location[ $module_id ] );

		return $path_base . $sub_path;
	}

	/**
	 * Resolves the absolute physical configuration file path internally.
	 *
	 * @since  1.0.0
	 * @param  string $settings_key The dynamic settings key identifier.
	 * @param  string $module_id    The internal identity string of the module.
	 * @return string               The absolute path to the configuration file, or empty string if invalid.
	 */
	private function resolve_config_file_path( string $settings_key, string $module_id ): string {
		$path = $this->get_setting_base_dir( $module_id );
		$slug = $this->get_file_slug( $settings_key, $module_id );

		if ( false === $slug || false === $path ) {
			return '';
		}

		return $path . $slug . '.php';
	}

	/**
	 * Universal Public API loading and returning raw evaluation configurations from disk.
	 *
	 * @since  1.0.0
	 * @param  string $key       The configuration focus layout identifier key.
	 * @param  string $module_id The parent host module identifier.
	 * @return mixed             The native contents evaluation payload array, or null if invalid.
	 */
	private function get_settings_file( string $key, string $module_id ): mixed {
		$file = $this->resolve_config_file_path( $key, $module_id );

		if ( '' === $file || ! file_exists( $file ) ) {
			return null;
		}

		return include $file;
	}

	/**
	 * Returns the live, shared instance of the Options Vault service.
	 *
	 * Guarantees a single in-memory cache registry across the entire plugin lifecycle.
	 *
	 * @since  1.0.0
	 * @return Options The active initialized option vault manager instance.
	 */
	public function get_options(): Options|false {
		if ( ! $this->is_allowed_get_options() ) {
			return false;
		}
		return $this->options;
	}

	private function is_allowed_get_options(): bool {
		[ 'caller' => $caller, 'called' => $called ] = Logger::get_caller_data( 3 );
		$caller_fqcn = $caller['class'] ?? '';
		// Only the options managementSettings section is allowed to call this method
		if ( OptionsManagementSettings::class !== $caller_fqcn ) {
			$called_method = $caller['method'] ?? 'unknown';
			_doing_it_wrong( $called_method, __( "Calling Settings->get_options() is only allowed, use Settings->get(), Settings->save() or Settings->delete() instead. For getting unmanaged options, you can use Settings->get_unmanaged().", 'dwp-cf' ), '1.0.0' );
			return false;
		}
		return true;
	}
}
