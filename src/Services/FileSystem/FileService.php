<?php
/**
 * File Service.
 *
 * Reads and writes the plugin's data files: PHP files that return an array, and JSON files.
 *
 * @package DeWittePrins\CoreFunctionality\Services\FileSystem
 * @author  Hans Schuijff <@hansschuijff>
 * @license GPL-2.0
 * @since   1.0.0
 */

declare( strict_types=1 );

namespace DeWittePrins\CoreFunctionality\Services\FileSystem;

use DeWittePrins\CoreFunctionality\Enums\FileExtension;

use function clearstatcache;
use function dirname;
use function file_exists;
use function file_get_contents;
use function file_put_contents;
use function function_exists;
use function is_array;
use function is_dir;
use function is_readable;
use function json_decode;
use function json_encode;
use function opcache_invalidate;
use function pathinfo;
use function strtolower;
use function unlink;
use function var_export;
use function wp_is_writable;
use function wp_mkdir_p;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class FileService
 *
 * The general reader and writer of data files. It knows how to read, write and delete a file,
 * not which file holds what: that is for the layers that use it, such as ComponentsRegisterFiles
 * (the registers of the ComponentsRegister in kernel/) and SettingsFiles (the defaults in settings/).
 *
 * The format follows the extension: .php files return an array, .json files hold one.
 *
 * @since 1.0.0
 */
class FileService {

	/**
	 * Reads the array a data file holds.
	 *
	 * @since  1.0.0
	 * @param  string $path Full path of the file.
	 * @return array|null   The array, or null when the file does not exist, cannot be read, or holds no array.
	 */
	public function read( string $path ): ?array {
		if ( '' === $path || ! is_readable( $path ) ) {
			return null;
		}

		$data = match ( $this->get_extension( $path ) ) {
			FileExtension::PHP->value  => include $path,
			FileExtension::JSON->value => json_decode( (string) file_get_contents( $path ), true ),
			default                    => null,
		};

		return is_array( $data ) ? $data : null;
	}

	/**
	 * Writes an array to a data file.
	 *
	 * @since  1.0.0
	 * @param  string       $path        Full path of the file (.php or .json).
	 * @param  array<mixed> $data        The array to write.
	 * @param  string       $description First line of the doc comment of a PHP file.
	 * @return bool                      True if the file was written, false if it is not writable or writing failed.
	 */
	public function write( string $path, array $data, string $description = 'Auto-generated configuration file.' ): bool {
		if ( ! $this->is_writable( $path ) || ! wp_mkdir_p( dirname( $path ) ) ) {
			return false;
		}

		$content = match ( $this->get_extension( $path ) ) {
			FileExtension::PHP->value  => $this->build_php_content( $data, $description ),
			FileExtension::JSON->value => json_encode( $data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ),
			default                    => false,
		};
		if ( false === $content ) {
			return false;
		}

		$bytes = file_put_contents( $path, $content, LOCK_EX );
		if ( false !== $bytes ) {
			$this->forget_cached( $path );
		}

		return false !== $bytes;
	}

	/**
	 * Deletes a data file.
	 *
	 * @since  1.0.0
	 * @param  string $path Full path of the file.
	 * @return bool         True if the file was deleted, false if it did not exist or could not be deleted.
	 */
	public function delete( string $path ): bool {
		if ( '' === $path || ! file_exists( $path ) ) {
			return false;
		}

		$deleted = unlink( $path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_unlink
		if ( $deleted ) {
			$this->forget_cached( $path );
		}

		return $deleted;
	}

	/**
	 * Checks whether a file can be written: the file itself, or else the nearest existing folder
	 * on its path (write() creates the folders that are missing).
	 *
	 * @since  1.0.0
	 * @param  string $path Full path of the file.
	 * @return bool         True if the file can be written, false otherwise.
	 */
	public function is_writable( string $path ): bool {
		if ( '' === $path ) {
			return false;
		}
		if ( file_exists( $path ) ) {
			return wp_is_writable( $path );
		}

		$dir = dirname( $path );
		while ( ! is_dir( $dir ) && dirname( $dir ) !== $dir ) {
			$dir = dirname( $dir );
		}

		return wp_is_writable( $dir );
	}

	/**
	 * Makes sure the next read of a changed file sees the new content.
	 *
	 * OPcache can otherwise return the old version of the file; that depends on the server
	 * configuration, which is one reason the files are only read when the option has no value.
	 *
	 * @since  1.0.0
	 * @param  string $path Path of the file that changed.
	 * @return void
	 */
	public function forget_cached( string $path ): void {
		clearstatcache( true, $path );

		if ( function_exists( 'opcache_invalidate' ) ) {
			opcache_invalidate( $path, true );
		}
	}

	/**
	 * Builds the content of a PHP file that returns the array.
	 *
	 * @since  1.0.0
	 * @param  array<mixed> $data        The array.
	 * @param  string       $description First line of the doc comment.
	 * @return string                    The file content.
	 */
	private function build_php_content( array $data, string $description ): string {
		$html  = '<?php' . PHP_EOL;
		$html .= '/**' . PHP_EOL;
		$html .= ' * ' . $description . PHP_EOL;
		$html .= ' *' . PHP_EOL;
		$html .= ' * @since 1.0.0' . PHP_EOL;
		$html .= ' */' . PHP_EOL;
		$html .= PHP_EOL;
		$html .= "if ( ! defined( 'ABSPATH' ) ) {" . PHP_EOL;
		$html .= "\texit;" . PHP_EOL;
		$html .= '}' . PHP_EOL;
		$html .= PHP_EOL;
		$html .= 'return ' . var_export( $data, true ) . ';' . PHP_EOL; // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.serialize_var_export

		return $html;
	}

	/**
	 * Returns the lowercase extension of a file.
	 *
	 * @since  1.0.0
	 * @param  string $path The path or file name.
	 * @return string       The extension ('php', 'json'), or an empty string.
	 */
	private function get_extension( string $path ): string {
		return strtolower( pathinfo( $path, PATHINFO_EXTENSION ) );
	}
}
