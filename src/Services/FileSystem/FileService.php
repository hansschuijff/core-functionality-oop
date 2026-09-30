<?php
/**
 * File Service.
 *
 * Provides read, save, and delete access to config-style architecture files, and
 * provides dynamic discovery mechanics based on the plugin's structure.
 *
 * @package DeWittePrins\CoreFunctionality\Services\FileSystem
 * @author  Hans Schuijff <@hansschuijff>
 * @license GPL-2.0
 * @since   1.0.0
 */

declare( strict_types=1 );

namespace DeWittePrins\CoreFunctionality\Services\FileSystem;

use DeWittePrins\CoreFunctionality\Plugin;
use DeWittePrins\CoreFunctionality\Enums\Register;     // Enum for the registers.
use DeWittePrins\CoreFunctionality\Enums\RegisterFile; // Filenames of the registers.
use DeWittePrins\CoreFunctionality\Enums\FileExtension;
use file_chooser;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class FileService
 *
 * Handles reading, saving, and deleting of framework register array blueprints,
 * abstracting structural disk mutations safely into clean standalone routines.
 *
 * @since 1.0.0
 */
class FileService {

	/**
	 * Full directory base path where the active register files reside.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected string $register_base_path;

	/**
	 * FileService constructor.
	 *
	 * @since 1.0.0
	 * @param Plugin $plugin Central plugin core orchestrator instance used to retrieve configuration paths.
	 */
	public function __construct(
		protected Plugin $plugin,
	) {
		$this->register_base_path = $this->get_components_registers_path();
	}

	/**
	 * Returns the parsed array structure stored inside a targeted register file.
	 *
	 * @since 1.0.0
	 * @param Register $register Enum instance indicating which targeted configuration matrix blueprint to retrieve.
	 * @return array             The structural array data payload on success, or an empty fallback array.
	 */
	public function get_register( Register $register ): array {
		$file = $this->get_register_full_path( $register );

		if ( ! is_readable( $file ) ) {
			return array();
		}

		$register_data = match ( $this->get_file_extension( $register ) ) {
			FileExtension::PHP->value  => include $file,
			FileExtension::JSON->value => json_decode( (string) file_get_contents( $file ), true ),
			default                    => array(),
		};

		return is_array( $register_data ) ? $register_data : array();
	}

	/**
	 * Destroys a targeted register configuration file from the physical storage drive.
	 *
	 * @since 1.0.0
	 * @param Register $register Enum target file compilation configuration blueprint context.
	 * @return bool              True if the file was unlinked successfully, false otherwise.
	 */
	public function delete_register( Register $register ): bool {
		$path = $this->get_register_full_path( $register );

		if ( ! file_exists( $path ) ) {
			return false;
		}

		return unlink( $path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_unlink
	}

	/**
	 * Serializes and commits a structured matrix array safely to a physical file target.
	 *
	 * Supports dynamic encoding paths for both executable PHP arrays and JSON documents.
	 *
	 * @since  1.0.0
	 * @param  Register    $register      Enum configuration destination signature mapping index.
	 * @param  array<mixed> $register_data The structured components registry context data payload tree.
	 * @return string|bool                Translatable success message notification on success, false on write failure.
	 */
	public function save_register( Register $register, array $register_data ): string|bool {
		if ( empty( $register_data ) ) {
			return false;
		}

		$written = match ( $this->get_file_extension( $register ) ) {
			FileExtension::PHP->value  => $this->php_write_register( $register, $register_data ),
			FileExtension::JSON->value => $this->json_write_register( $register, $register_data ),
			default                    => false,
		};

		if ( false === $written ) {
			if ( \function_exists( '\d' ) ) {
				\ddd(
					\current_filter(),
					__METHOD__ . ':' . __LINE__,
					$register->value,
					this->get_file_extension( $register->value ),
					$written,
					$this->get_register_full_path( $register ),
					$register,
					$register_data,
				);
			}
			$this->plugin->notices->add(
				'error',
				sprintf(
					__( 'Critical Error: Failed to write updates toward configuration file: %1$s.', 'dwp-cf' ),
					$this->get_register_full_path( $register )
				)
			);
			return false;
		}

		return __( 'Settings saved successfully. Remember to commit changes in Git! 🚀', 'dwp-cf' );
	}

	/**
	 * Commits data payload maps into an executable PHP return array compilation blueprint file.
	 *
	 * @since  1.0.0
	 * @param  Register    $register      Enum configuration file destination indicator.
	 * @param  array<mixed> $register_data The structured components registry data context.
	 * @return bool                       True if data operations succeed, false otherwise.
	 */
	private function php_write_register( Register $register, array $register_data ): bool {
		if ( empty( $register_data ) ) {
			return false;
		}
		$path = $this->get_register_full_path( $register );

		// FIX: Stripped the floating redundant row, leveraging the builder method cleanly.
		$file_content = $this->build_file_content_php( $register_data );
		$bytes        = file_put_contents( $path, $file_content, LOCK_EX );

		if ( false !== $bytes ) {
			$this->clear_opcache( $path );
		}

		return false !== $bytes;
	}

	/**
	 * This is a fix for the problem that opcache returned old versions
	 * of just updated files.
	 *
	 * The fix solves it, but since it depends on server
	 * configuration we decided to read the files only as failsave, when
	 * wp option has no values.
	 *
	 * @param string $path
	 * @return void
	 */
	private function clear_opcache( string $path ) : void {
		/**
		 * FIX 1: Force the OS to synchronize the disk-state immediately.
		 */
		clearstatcache( true, $path );

		/**
		 * FIX 2: Destroy the OPcache for this specific path!
		 * The 'true' parameter forces the cache-clearing, even if the
		 * timestamp is still unchanged.
		 */
		if ( function_exists( 'opcache_invalidate' ) ) {
			opcache_invalidate( $path, true );
		}
	}

	/**
	 * Commits data payload maps into an indented, human-readable JSON string file configuration.
	 *
	 * @since  1.0.0
	 * @param  Register    $register      Enum configuration file destination indicator.
	 * @param  array<mixed> $register_data The structured components registry data context.
	 * @return bool                       True if data operations succeed, false otherwise.
	 */
	private function json_write_register( Register $register, array $register_data ): bool {
		$path = $this->get_register_full_path( $register );

		$json_string = json_encode( $register_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES );
		if ( false === $json_string ) {
			return false;
		}

		$bytes = file_put_contents( $path, $json_string, LOCK_EX );

		if ( false !== $bytes ) {
			$this->clear_opcache( $path );
		}

		return false !== $bytes;
	}

	/**
	 * Synthesizes valid PHP compilation file content source headers wrap around a given array.
	 *
	 * @since 1.0.0
	 * @param array<mixed> $array The specific configuration blueprint payload matrix to dump.
	 * @return string              Generated execution-ready PHP file code string.
	 */
	private function build_file_content_php( array $array ): string {
		$html  = "<?php" . PHP_EOL;
		$html .= "/**" . PHP_EOL;
		$html .= " * Auto-generated configuration file." . PHP_EOL;
		$html .= " *" . PHP_EOL;
		$html .= " * @since 1.0.0" . PHP_EOL;
		$html .= " */"  . PHP_EOL;
		$html .= PHP_EOL;
		$html .= "if ( ! defined( 'ABSPATH' ) ) {" . PHP_EOL;
		$html .= "\texit;" . PHP_EOL;
		$html .= "}" . PHP_EOL;
		$html .= PHP_EOL;
		$html .= 'return ' . var_export( $array, true ) . ";" . PHP_EOL; // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.serialize_var_export

		return $html;
	}

	/**
	 * Resolves the absolute directory disk path mapping toward a specific target configuration file.
	 *
	 * @since  1.0.0
	 * @param  Register $file Enum key matching the active registration identifier destination.
	 * @return string         The resolved cross-platform absolute filesystem target location.
	 */
	private function get_register_full_path( Register $register ): string {
		$filename = $this->get_filename( $register );
		return $this->trailingslashit( $this->register_base_path ) . $filename;
	}

	/**
	 * Gets the registers filename.
	 *
	 * @param Register $register
	 * @return string
	 */
	private function get_filename( Register $register ): string {
		return match ( $register ) {
			Register::COMPONENTS => RegisterFile::COMPONENTS->value,
			Register::ENABLED    => RegisterFile::ENABLED->value,
			Register::DEPLOYMENT => RegisterFile::DEPLOYMENT->value,
			Register::FRONTEND   => RegisterFile::FRONTEND->value,
			Register::ADMIN      => RegisterFile::ADMIN->value,

			// Failsafe: Prevent UnhandledMatchError crashes if an unmapped Enum case passes through.
			default              => '',
		};
	}

	/**
	 * Enforces that a directory path string consistently terminates with a standard forward slash.
	 *
	 * @since  1.0.0
	 * @param  string $path The targeted file directory path string.
	 * @return string Normalized path ending with a clean forward slash.
	 */
	private function trailingslashit( string $path ): string {
		return $this->untrailingslashit( $path ) . '/';
	}

	/**
	 * Strips trailing cross-platform forward and backward slashes uniformly from a path string.
	 *
	 * @since  1.0.0
	 * @param  string $path The raw file directory path string.
	 * @return string Cleaned path string without trailing slash artifacts.
	 */
	private function untrailingslashit( string $path ): string {
		return rtrim( $path, '/\\' );
	}

	/**
	 * Dynamic fallback path configuration gate resolving the absolute destination parameters for registers.
	 *
	 * Incorporates native notice administration alerts if critical framework locations fail validation checks.
	 *
	 * @since  1.0.0
	 * @return string Full path to the kernel settings dir.
	 */
	private function get_components_registers_path(): string {
		$path = $this->plugin->get_data( 'kernel-settings-dir' );

		if ( empty( $path ) || ! is_dir( $path ) ) {
			$this->plugin->notices->add(
				'error',
				__( 'Path to the component registers could not be retrieved, fallback paths used. Please inform Support', 'dwp-cf' )
			);

			$path = $this->plugin->get_data( 'settings-dir' );

			if ( empty( $path ) || ! is_dir( $path ) ) {
				$path = rtrim( __DIR__, '/\\' ) . '/registry';
			}
		}

		return $path;
	}

	/**
	 * Sanitize a file or path slug to prevent directory traversal.
	 *
	 * @since 1.0.0
	 * @param string $slug The slug or relative path to sanitize.
	 * @return string The sanitized slug.
	 */
	private function sanitize_slug( string $slug ): string {
		$slug = str_replace( '\\', '/', $slug );

		$segments = array_filter(
			explode( '/', $slug ),
			static function ( string $segment ): bool {
				return '' !== $segment && '.' !== $segment && '..' !== $segment;
			}
		);

		return implode( '/', array_map( 'sanitize_file_name', $segments ) );
	}

	/**
	 * Validates if the string representation conforms to a singular slug segment structure.
	 *
	 * @since  1.0.0
	 * @param  string $string Target raw string representation to investigate.
	 * @return bool           True if slug parameters align, false if string contains path indicators.
	 */
	private function is_slug( string $string ): bool {
		return false === strpbrk( $string, '/\\.' );
	}

	/**
	 * Validates if the string representation contains file navigation path structures.
	 *
	 * @since  1.0.0
	 * @param  string $string Target raw string representation to investigate.
	 * @return bool           True if string maps directory paths, false otherwise.
	 */
	private function is_file_path( string $string ): bool {
		return ! $this->is_slug( $string );
	}

	/**
	 * Checks if the filename string ends with a standard JSON extension match.
	 *
	 * @since  1.0.0
	 * @param  string $filename Raw filename configuration parameter context string.
	 * @return bool             True if filename targets JSON structures, false otherwise.
	 */
	private function is_json_file( string $filename ): bool {
		return FileExtension::JSON->value === $this->get_file_extension( $filename );
	}

	/**
	 * Checks if the filename string ends with a standard PHP extension match.
	 *
	 * @since  1.0.0
	 * @param  string $filename Raw filename configuration parameter context string.
	 * @return bool             True if filename targets PHP structures, false otherwise.
	 */
	private function is_php_file( string $filename ): bool {
		return FileExtension::PHP->value === $this->get_file_extension( $filename );
	}

	/**
	 * Retrieves the lowercase extension of a given filename or path.
	 *
	 * @since  1.0.0
	 * @param  Register $register The register Enum.
	 * @return string             The lowercase extension string (e.g., 'php', 'json'), or empty string if none.
	 */
	private function get_file_extension( Register $register ): string {
		$filename = $this->get_filename( $register );
		return strtolower( pathinfo( $filename, PATHINFO_EXTENSION ) );
	}
}
