<?php
/**
 * File containing the SettingsFiles class.
 *
 * @package DeWittePrins\CoreFunctionality\Services
 * @author  Hans Schuijff <@hansschuijff>
 * @license GPL-2.0
 * @since   1.0.0
 */

declare( strict_types=1 );

namespace DeWittePrins\CoreFunctionality\Services;

use Closure;
use DeWittePrins\CoreFunctionality\Services\FileSystem\FileService;
use RuntimeException;

use function basename;
use function file_exists;
use function trailingslashit;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class SettingsFiles
 *
 * The access of Settings to the settings files, as the Options vault is its access to the
 * database. A settings file holds the default of a key: settings/<slug>.php for general keys,
 * settings/modules/<module folder>/<slug>.php for module keys. The slug of a key comes from
 * settings/setup/key-slugs-translation.php, the module folder from
 * settings/setup/modules-settings-path.php.
 *
 * @since 1.0.0
 */
class SettingsFiles {

	/**
	 * The module id used for settings that belong to no module.
	 *
	 * @var string
	 */
	public const GENERAL = 'cf-general';

	/**
	 * The absolute base directory of the settings files, with a trailing slash.
	 *
	 * @var string
	 */
	private string $base_dir;

	/**
	 * Module id => settings folder of the module (relative to settings/modules/).
	 *
	 * @var array<string, string>
	 */
	private array $module_folders;

	/**
	 * Settings key => file slug.
	 *
	 * @var array<string, string>
	 */
	private array $key_slugs;

	/**
	 * SettingsFiles Constructor.
	 *
	 * @since 1.0.0
	 * @param string      $base_dir     The absolute root directory of the settings files.
	 * @param Settings    $settings     The settings service; a settings file is read as part of it, so
	 *                                  it can compose its value from other settings with $this->get().
	 * @param FileService $file_service The general reader and writer of data files.
	 * @throws RuntimeException If one of the setup files is missing.
	 */
	public function __construct(
		string $base_dir,
		private readonly Settings $settings,
		private readonly FileService $file_service
	) {
		$this->base_dir       = trailingslashit( $base_dir );
		$this->module_folders = $this->require_setup_file( 'modules-settings-path.php' );
		$this->key_slugs      = $this->require_setup_file( 'key-slugs-translation.php' );
	}

	/**
	 * Returns the content of the settings file of a key: its default.
	 *
	 * @since  1.0.0
	 * @param  string $key       The settings key.
	 * @param  string $module_id The module whose settings folder holds the file; empty for general settings.
	 * @return mixed             The default, or null when the key has no settings file.
	 */
	public function read( string $key, string $module_id = '' ): mixed {
		$file = $this->get_path( $key, $module_id );

		if ( '' === $file || ! file_exists( $file ) ) {
			return null;
		}

		// Not through FileService::read(): the file is included as part of Settings, for $this->get().
		$include = function () use ( $file ) {
			return include $file;
		};

		return Closure::bind( $include, $this->settings, Settings::class )();
	}

	/**
	 * Checks whether the settings file of a key can be written.
	 *
	 * True on a development site with a writable plugin folder; on production the folder is
	 * usually read-only and defaults arrive with a release instead.
	 *
	 * @since  1.0.0
	 * @param  string $key       The settings key.
	 * @param  string $module_id The module whose settings folder holds the file; empty for general settings.
	 * @return bool              True if the file can be written.
	 */
	public function is_writable( string $key, string $module_id = '' ): bool {
		return $this->file_service->is_writable( $this->get_path( $key, $module_id ) );
	}

	/**
	 * Writes the settings file of a key, making the value its default.
	 *
	 * @since  1.0.0
	 * @param  string       $key         The settings key.
	 * @param  string       $module_id   The module whose settings folder holds the file; empty for general settings.
	 * @param  array<mixed> $value       The new default.
	 * @param  string       $description First line of the file's doc comment.
	 * @return bool                      True if the file was written.
	 */
	public function write( string $key, string $module_id, array $value, string $description ): bool {
		return $this->file_service->write( $this->get_path( $key, $module_id ), $value, $description );
	}

	/**
	 * Returns the absolute path of the settings file of a key.
	 *
	 * @since  1.0.0
	 * @param  string $key       The settings key.
	 * @param  string $module_id The module whose settings folder holds the file; empty for general settings.
	 * @return string            The path, or an empty string when the key has no settings file.
	 */
	public function get_path( string $key, string $module_id = '' ): string {
		$module_id = '' !== $module_id ? $module_id : self::GENERAL;
		$dir       = $this->get_dir( $module_id );
		$slug      = $this->get_slug( $key, $module_id );

		if ( false === $dir || false === $slug ) {
			return '';
		}

		return $dir . $slug . '.php';
	}

	/**
	 * Translates a settings key to the slug of its file.
	 *
	 * A general key without a translation uses the key itself.
	 *
	 * @since  1.0.0
	 * @param  string $key       The settings key.
	 * @param  string $module_id The id of a module, or GENERAL.
	 * @return string|false      The file slug, or false if the key has no file.
	 */
	private function get_slug( string $key, string $module_id ): string|false {
		if ( ! empty( $this->key_slugs[ $key ] ) ) {
			return $this->key_slugs[ $key ];
		}

		return self::GENERAL === $module_id ? basename( $key ) : false;
	}

	/**
	 * Returns the settings folder of a module.
	 *
	 * @since  1.0.0
	 * @param  string $module_id The id of a module, or GENERAL.
	 * @return string|false      The absolute folder with a trailing slash, or false for an unknown module.
	 */
	private function get_dir( string $module_id ): string|false {
		if ( self::GENERAL === $module_id ) {
			return $this->base_dir;
		}
		if ( empty( $this->module_folders[ $module_id ] ) ) {
			return false;
		}

		return $this->base_dir . trailingslashit( 'modules/' . $this->module_folders[ $module_id ] );
	}

	/**
	 * Includes one of the setup files (settings/setup/).
	 *
	 * @since  1.0.0
	 * @param  string $file_name The file name.
	 * @return array             The array the file returns.
	 * @throws RuntimeException  If the file is missing.
	 */
	private function require_setup_file( string $file_name ): array {
		$file = $this->base_dir . 'setup/' . $file_name;

		if ( ! file_exists( $file ) ) {
			throw new RuntimeException( 'Vital configuration is missing on disk: ' . $file );
		}

		return (array) require $file;
	}
}
