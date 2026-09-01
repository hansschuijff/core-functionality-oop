<?php
/**
 * De Witte Prins Core Custom Logging Engine.
 *
 * @package DeWittePrins\Core\Services
 * @since   4.0.0
 */

namespace DeWittePrins\Core\Services;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class Logger
 *
 * Governs diagnostic logs and debugging matrix extractions. Allows fluid
 * environment toggles shifting entries safely across multiple server log targets.
 *
 * @since 4.0.0
 */
class Logger {

	/**
	 * Stored administration parameter metrics extracted from options.
	 *
	 * @since 4.0.0
	 * @var array
	 */
	private array $settings;

	/**
	 * Logger constructor.
	 *
	 * Extracts structural settings profiles mapping fallback configurations securely.
	 *
	 * @since 4.0.0
	 * @see get_option()
	 */
	public function __construct() {
		$this->settings = get_option(
			'dwp_log_settings',
			array(
				'activate_log'  => true,
				'active_logger' => 'core_log',
				'print_method'  => 'flex_print_r',
				'log_file'      => WP_CONTENT_DIR . '/_core-functionality.log',
			)
		);
	}

	/**
	 * Filters payload constraints before streaming markers toward targets.
	 *
	 * @since 4.0.0
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

			case 'core_log':
			default:
				$this->write_to_custom_file( $formatted_msg );
				break;
		}
	}

	/**
	 * Serializes payload entities using safe string fallback chains.
	 *
	 * @since 4.0.0
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
	 * @since 4.0.0
	 * @param string $message Compiled diagnostics entry data string.
	 * @return void
	 */
	private function write_to_custom_file( string $message ): void {
		$file = $this->settings['log_file'];

		if ( is_writable( dirname( $file ) ) ) {
			error_log( $message . "\n", 3, $file );
		}
	}
}
