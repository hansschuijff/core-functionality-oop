<?php
/**
 * PHP Array File.
 *
 * Writes an array to a PHP file that returns it, as used for the kernel registers and for
 * settings defaults.
 *
 * @package DeWittePrins\CoreFunctionality\Services\FileSystem
 * @author  Hans Schuijff <@hansschuijff>
 * @license GPL-2.0
 * @since   1.0.0
 */

declare( strict_types=1 );

namespace DeWittePrins\CoreFunctionality\Services\FileSystem;

use function clearstatcache;
use function dirname;
use function file_exists;
use function file_put_contents;
use function function_exists;
use function opcache_invalidate;
use function var_export;
use function wp_is_writable;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class PhpArrayFile
 *
 * @since 1.0.0
 */
final class PhpArrayFile {

	/**
	 * Checks whether a file can be written (the file itself, or its folder if it does not exist yet).
	 *
	 * @since  1.0.0
	 * @param  string $path Full path of the file.
	 * @return bool         True if the file can be written, false otherwise.
	 */
	public static function is_writable( string $path ): bool {
		if ( '' === $path ) {
			return false;
		}

		return file_exists( $path ) ? wp_is_writable( $path ) : wp_is_writable( dirname( $path ) );
	}

	/**
	 * Writes an array to a PHP file that returns it.
	 *
	 * @since  1.0.0
	 * @param  string       $path        Full path of the file.
	 * @param  array<mixed> $data        The array to write.
	 * @param  string       $description First line of the file's doc comment.
	 * @return bool                      True if the file was written, false if it is not writable or writing failed.
	 */
	public static function write( string $path, array $data, string $description = 'Auto-generated configuration file.' ): bool {
		if ( ! self::is_writable( $path ) ) {
			return false;
		}

		$bytes = file_put_contents( $path, self::build_content( $data, $description ), LOCK_EX );
		if ( false !== $bytes ) {
			self::forget_cached( $path );
		}

		return false !== $bytes;
	}

	/**
	 * Makes sure the next read of a just written file sees the new content.
	 *
	 * OPcache can otherwise return the old version of the file; that depends on the server
	 * configuration, which is one reason the files are only read when the option has no value.
	 *
	 * @since  1.0.0
	 * @param  string $path Path of the file that was just written.
	 * @return void
	 */
	public static function forget_cached( string $path ): void {
		clearstatcache( true, $path );

		if ( function_exists( 'opcache_invalidate' ) ) {
			opcache_invalidate( $path, true );
		}
	}

	/**
	 * Builds the content of the PHP file.
	 *
	 * @since  1.0.0
	 * @param  array<mixed> $data        The array to write.
	 * @param  string       $description First line of the file's doc comment.
	 * @return string                    The file content.
	 */
	private static function build_content( array $data, string $description ): string {
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
}
