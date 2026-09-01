<?php
/**
 * De Witte Prins Core Main Plugin Container Shell.
 *
 * @package DeWittePrins\Core
 * @since   4.0.0
 */

namespace DeWittePrins\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class Plugin
 *
 * Encapsulates global root plugin configurations, physical server storage directories,
 * and web-accessible uniform endpoints without bleeding metadata into global scopes.
 *
 * @since 4.0.0
 */
class Plugin {

	/**
	 * Internal cache index storage holding absolute paths and readme header values.
	 *
	 * @since 4.0.0
	 * @var array
	 */
	private array $plugin_data = array();

	/**
	 * Plugin constructor.
	 *
	 * Extracts native plugin header metrics and computes root locations instantly.
	 *
	 * @since 4.0.0
	 * @param string $root_file The absolute physical disk path pointing to the main execution file.
	 */
	public function __construct( string $root_file ) {
		if ( ! function_exists( 'get_plugin_data' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$this->plugin_data               = get_plugin_data( $root_file );
		$this->plugin_data['plugin-dir'] = plugin_dir_path( $root_file );
		$this->plugin_data['plugin-url'] = plugin_dir_url( $root_file );
	}

	/**
	 * Resolves structural file attributes from the central metadata register.
	 *
	 * @since 4.0.0
	 * @param string $key Reference mapping selector (e.g., 'Version', 'plugin-dir').
	 * @return string Target layout value string, or an empty string configuration if missing.
	 */
	public function get_data( string $key ): string {
		return $this->plugin_data[ $key ] ?? '';
	}
}
