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
use DeWittePrins\CoreFunctionality\Services\FileSystem\FileService;
use RuntimeException;

use function __;
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
 * The holder of all settings: defaults in the settings files (through SettingsFiles) and
 * changes in the database (through the Options vault).
 *
 * @since 1.0.0
 */
class Settings {

	/**
	 * Access to the settings files, which hold the defaults.
	 *
	 * @var SettingsFiles
	 */
	private SettingsFiles $files;

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
	 * @param string      $base_settings_dir The absolute root directory context for settings files.
	 * @param FileService $file_service      The general reader and writer of data files.
	 * @throws RuntimeException               If critical framework configuration files are missing on disk.
	 */
	public function __construct( string $base_settings_dir, FileService $file_service ) {
		$this->files   = new SettingsFiles( $base_settings_dir, $this, $file_service );
		$this->options = new Options( $this );
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
		$module_id = ( ! empty( $module_id ) ) ? $module_id : SettingsFiles::GENERAL;

		if ( true === $reset_to_defaults ) {
			return $this->files->read( $key, $module_id );
		}

		if ( isset( $this->runtime_cache[ $module_id ][ $key ] ) ) {
			return $this->runtime_cache[ $module_id ][ $key ];
		}

		$settings_file = $this->files->read( $key, $module_id );
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
	 * Returns the saved value, or the default from the settings file when nothing is saved.
	 *
	 * Unlike get(), a saved array replaces the default as a whole instead of being merged into it.
	 * Meant for lists and trees, where an item removed from the saved value must stay removed.
	 *
	 * @since  1.0.0
	 * @param  string $key       The configuration settings identifier.
	 * @param  string $module_id The module whose settings folder holds the default.
	 * @param  mixed  $default   Returned when there is neither a saved value nor a default.
	 * @return mixed             The saved value, the default, or $default.
	 */
	public function get_whole( string $key, string $module_id = '', mixed $default = false ): mixed {
		$saved = $this->options->get( $key, null );
		if ( null !== $saved ) {
			return $saved;
		}

		return $this->get_default( $key, $module_id ) ?? $default;
	}

	/**
	 * Returns the default from the settings file, ignoring a saved value.
	 *
	 * @since  1.0.0
	 * @param  string $key       The configuration settings identifier.
	 * @param  string $module_id The module whose settings folder holds the default.
	 * @return mixed             The default, or null when there is no settings file.
	 */
	public function get_default( string $key, string $module_id = '' ): mixed {
		return $this->files->read( $key, $module_id );
	}

	/**
	 * Checks whether a value is saved for a key (in the database, over the default).
	 *
	 * @since  1.0.0
	 * @param  string $key The configuration settings identifier.
	 * @return bool        True if a value is saved.
	 */
	public function has_saved( string $key ): bool {
		return null !== $this->options->get( $key, null );
	}

	/**
	 * Returns the path of the settings file that holds the default for a key.
	 *
	 * @since  1.0.0
	 * @param  string $key       The configuration settings identifier.
	 * @param  string $module_id The module whose settings folder holds the default.
	 * @return string            The path, or an empty string when the key has no settings file.
	 */
	public function get_default_file_path( string $key, string $module_id = '' ): string {
		return $this->files->get_path( $key, $module_id );
	}

	/**
	 * Checks whether the default of a key can be written to its settings file.
	 *
	 * True on a development site with a writable plugin folder; on production the folder is
	 * usually read-only and defaults arrive with a release instead.
	 *
	 * @since  1.0.0
	 * @param  string $key       The configuration settings identifier.
	 * @param  string $module_id The module whose settings folder holds the default.
	 * @return bool              True if the settings file can be written.
	 */
	public function can_save_default( string $key, string $module_id = '' ): bool {
		return $this->files->is_writable( $key, $module_id );
	}

	/**
	 * Writes a value as the default of a key to its settings file, so it travels with the plugin
	 * (in git and the release). A value saved in the database still goes over it.
	 *
	 * @since  1.0.0
	 * @param  string       $key         The configuration settings identifier.
	 * @param  string       $module_id   The module whose settings folder holds the default.
	 * @param  array<mixed> $value       The new default.
	 * @param  string       $description First line of the file's doc comment.
	 * @return bool                      True if the file was written.
	 */
	public function save_default( string $key, string $module_id, array $value, string $description ): bool {
		$this->forget_cached( $key );

		return $this->files->write( $key, $module_id, $value, $description );
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
	 * Saves an option under its exact name via the Options Vault, bypassing token translation.
	 *
	 * For options of WordPress or another plugin, whose name is fixed.
	 *
	 * @since  1.0.0
	 * @param  string $key   The exact database option key.
	 * @param  mixed  $value The value to store.
	 * @return bool          True if the value changed, false if it was the same or saving failed.
	 */
	public function save_unmanaged( string $key, mixed $value ): bool {
		return $this->options->update_unmanaged( $key, $value );
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
		$this->forget_cached( $key );

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
		$this->forget_cached( $key );

		return $this->options->delete( $key );
	}

	/**
	 * Removes a key from the runtime cache, for every module, so the next get() reads the new value.
	 *
	 * @since  1.0.0
	 * @param  string $key The configuration identifier that changed.
	 * @return void
	 */
	private function forget_cached( string $key ): void {
		foreach ( array_keys( $this->runtime_cache ) as $module_id ) {
			unset( $this->runtime_cache[ $module_id ][ $key ] );
		}
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

	/**
	 * Checks whether the caller may receive the Options vault itself.
	 *
	 * @since  1.0.0
	 * @return bool True if allowed, false otherwise.
	 */
	private function is_allowed_get_options(): bool {
		[ 'caller' => $caller, 'called' => $called ] = Logger::get_caller_data( 3 );
		$caller_fqcn                                 = $caller['class'] ?? '';
		// Only the options managementSettings section is allowed to call this method.
		if ( OptionsManagementSettings::class !== $caller_fqcn ) {
			$called_method = $caller['method'] ?? 'unknown';
			_doing_it_wrong( $called_method, __( 'Calling Settings->get_options() is only allowed, use Settings->get(), Settings->save() or Settings->delete() instead. For getting unmanaged options, you can use Settings->get_unmanaged().', 'core-functionality' ), '1.0.0' );
			return false;
		}
		return true;
	}
}
