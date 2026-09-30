<?php
/**
 * De Witte Prins Core Custom Logging Engine.
 *
 * @package DeWittePrins\CoreFunctionality\Services
 * @since   1.0.0
 */

namespace DeWittePrins\CoreFunctionality\Diagnostics;

use DeWittePrins\CoreFunctionality\Services\ClassParser;

use function str_starts_with;
use function str_replace;
use function trailingslashit;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class Logger
 *
 * Governs diagnostic logs and debugging matrix extractions. Allows fluid
 * environment toggles shifting entries safely across multiple server log targets.
 *
 * @since 1.0.0
 */
class Logger {

	/**
	 * Stored administration parameter metrics extracted from options.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	private array $settings;

	/**
	 * Logger constructor.
	 *
	 * Extracts structural settings profiles mapping fallback configurations securely.
	 *
	 * @since 1.0.0
	 * @see get_option()
	 */
	public function __construct() {
		$this->settings = get_option(
			'dwp_log_settings',
			array(
				'activate_log'  => true,
				'active_logger' => 'cf_log',
				'print_method'  => 'flex_print_r',
				'log_file'      => WP_CONTENT_DIR . '/_core-functionality.log',
			)
		);
	}

	/**
	 * Filters payload constraints before streaming markers toward targets.
	 *
	 * @since 1.0.0
	 * @param array $data Contextual logging parameter matrix package.
	 * @return void
	 */
	public function write( array $data ): void {
		if ( empty( $this->settings['activate_log'] ) ) {
			return; // Diagnostic recording disabled in backend.
		}

		$formatted_msg = $this->format_data( $data );

		switch ( $this->settings['active_logger'] ) {
			case 'kint':
				if ( function_exists( 'd' ) ) {
					d( $data ); // Trigger Debug Toolkit UI extractor directly.
				}
				break;

			case 'woocommerce':
				if ( function_exists( 'wc_get_logger' ) ) {
					wc_get_logger()->debug( $formatted_msg, array( 'source' => 'core-functionality' ) );
				}
				break;

			case 'php_error':
				error_log( $formatted_msg );
				break;

			case 'cf_log':
			default:
				$this->write_to_custom_file( $formatted_msg );
				break;
		}
	}

	/**
	 * Serializes payload entities using safe string fallback chains.
	 *
	 * @since 1.0.0
	 * @param array $data Extracted error parameters.
	 * @return string Structured human-readable payload report.
	 */
	private function format_data( array $data ): string {
		// Implements your unbreakable fallback structure bypassing hosting blocks.
		return print_r( $data, true );
	}

	/**
	 * Streams raw messages toward the fully qualified custom log location.
	 *
	 * @since 1.0.0
	 * @param string $message Compiled diagnostics entry data string.
	 * @return void
	 */
	private function write_to_custom_file( string $message ): void {
		$file = $this->settings['log_file'];

		if ( is_writable( dirname( $file ) ) ) {
			error_log( $message . "\n", 3, $file );
		}
	}

	/**
	 * Inspects the execution stack to identify and dump the direct caller of the current method.
	 *
	 * Performance note: debug_backtrace() can be heavy, so this method should exclusively
	 * be utilized during active development/debugging sessions.
	 *
	 * @since  1.0.0
	 * @param int $caller_depth Optional. Track trace depth where caller van be retrieved. Default 2.
 	 * @return array{
	 *     caller: array{
	 *         class: string,
	 *         type: string,
	 *         method: string,
	 *         file: string,
	 *         line: int
	 *     },
	 *     called: array{
	 *         class: string,
	 *         type: string,
	 *         method: string,
	 *         file: string,
	 *         line: int
	 *     }
	 * }
	 */
	public static function get_caller_data( int $caller_depth = 2 ): array {
		// prevents the stack index from getting negative.
		if ( $caller_depth < 2 ) {
			$caller_depth = 2;
		}
		$caller_file_depth = ( $caller_depth - 1 );
		$called_depth      = ( $caller_depth - 1 );
		$called_file_depth = ( $called_depth - 1 );

		// Capture only the top 3 layers of the execution stack to maximize performance.
		$trace             = debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS, $caller_depth + 1 );
		$default           =  array(
			'class'  => 'unknown',
			'type'   => 'unknown',
			'method' => 'unknown',
			'file'   => 'unknown',
			'line'   => 'unknown',
		);
		$caller_info       = $default;
		$called_info       = $default;

		/**
		 * Default backtrace array blueprint:
		 * $trace[0] -> This method itself (dump_method_caller)
		 * $trace[1] -> The method you are currently investigating
		 * $trace[2] -> The actual caller (the class/function that triggered the investigated method)
		 */
		if ( isset( $trace[ $called_depth ] ) ) {
			$called_info = array(
				'class'    => $trace[ $called_depth ]['class']    ?? 'Global Scope / Function',
				'type'     => $trace[ $called_depth ]['type']     ?? 'N/A',  // '->' for instance, '::' for static
				'function' => $trace[ $called_depth ]['function'] ?? 'N/A',
				'method'   => ( $trace[ $called_depth ]['class']    ?? '' )
							. ( $trace[ $called_depth ]['type']     ?? '' )
							. ( $trace[ $called_depth ]['function'] ?? 'unknown' )
							. '()',
				'file'     => $trace[ $called_file_depth ]['file']     ?? 'N/A',
				'line'     => $trace[ $called_file_depth ]['line']     ?? 'N/A', // Line inside the caller file
			);
		}
		if ( isset( $trace[ $caller_depth ] ) ) {
			$caller_info = array(
				'class'    => $trace[ $caller_depth ]['class']    ?? 'Global Scope / Function',
				'type'     => $trace[ $caller_depth ]['type']     ?? 'N/A',  // '->' for instance, '::' for static
				'function' => $trace[ $caller_depth ]['function'] ?? 'N/A',
				'method'   => ( $trace[ $caller_depth ]['class']    ?? '' )
							. ( $trace[ $caller_depth ]['type']     ?? '' )
							. ( $trace[ $caller_depth ]['function'] ?? 'unknown' )
							. '()',
				'file'     => $trace[ $caller_file_depth ]['file']     ?? 'N/A',
				'line'     => $trace[ $caller_file_depth ]['line']     ?? 'N/A', // Line inside the caller file
			);
		}
		return( array ( 'caller' => $caller_info, 'called' => $called_info ) );
	}

	/**
	 * Inspects the execution stack to identify and dump the direct caller of the current method.
	 *
	 * Performance note: debug_backtrace() can be heavy, so this method should exclusively
	 * be utilized during active development/debugging sessions.
	 *
	 * @since  1.0.0
	 * @param int $caller_depth Optional. Track trace depth where caller van be retrieved. Default 2.
	 * @return string
	 */
	public static function get_caller( int $caller_depth = 2 ): string {
		[ 'caller' => $caller, 'called' => $called ] = self::get_caller_data( $caller_depth + 1 ); // depth + 1 to take itself in account.

		$file = str_starts_with( $caller['file'], DWP_CF_PLUGIN_DIR )
			? str_replace( trailingslashit( DWP_CF_PLUGIN_DIR ), '', $caller['file'] )
			: basename( $caller['file'] );

		$called_method = ClassParser::fqcn_remove_namespace( $called['method'] ?? 'unknown' );
		$caller_method = $caller['method'] ?? 'unknown';
		$caller_method = str_starts_with( $caller_method, 'DeWittePrins\\CoreFunctionality\\' )
			? str_replace( 'DeWittePrins\\CoreFunctionality\\', '', $caller_method )
			: $caller_method;

		return sprintf(
			'%1$s was called by: ...\\%2$s from .../%3$s on line %4$s',
			$called_method,
			$caller_method,
			$file,
			$caller['line'],
		 );
	}
}
