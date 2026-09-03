<?php
/**
 * Dynamic Directory and Configuration Mapping Trait for De Witte Prins Modules.
 *
 * @package DeWittePrins\CoreFunctionality\Traits
 * @since   4.0.0
 */

namespace DeWittePrins\CoreFunctionality\Traits;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Trait HasModuleData
 *
 * Provides localized file system and URL utilities for individual modules.
 * Implements Convention over Configuration to automatically route domain-specific configs.
 *
 * @since 4.0.0
 */
trait HasModuleData {

	/**
	 * Local configuration mapping tracking directories and web endpoints.
	 *
	 * @since 4.0.0
	 * @var array
	 */
	private array $module_data = array();

	/**
	 * Automatically builds the directory directory layout structure using the module path.
	 *
	 * @since 4.0.0
	 * @param string $base_dir The absolute physical directory location (__DIR__) of the module.
	 * @return void
	 */
	protected function init_module_data( string $base_dir ): void {
		$base_dir = trailingslashit( $base_dir );
		$base_url = plugin_dir_url( $base_dir . 'module.php' );

		$this->module_data = array(
			'base-dir'   => $base_dir,
			'config-dir' => $base_dir . 'config/',
			'base-url'   => $base_url,
			'views-url'  => $base_url . 'views/',
			'assets-url' => $base_url . 'assets/',
		);
	}

	/**
	 * Retrieves an absolute path or public web endpoint from the dataset.
	 *
	 * @since 4.0.0
	 * @param string $key The internal reference token (e.g., 'base-dir' or 'assets-url').
	 * @return string The mapped storage entry, or an empty string if missing.
	 */
	public function get_module_data( string $key ): string {
		return $this->module_data[ $key ] ?? '';
	}

	/**
	 * Fetches standard software baselines and auto-merges site-specific environment domain overrides.
	 *
	 * Supports both traditional configurations (arrays) and singular execution values (strings).
	 *
	 * @since 4.0.0
	 * @param string $filename The target script configuration reference.
	 * @return mixed Gathers arrays or string directives dynamically.
	 */
	public function get_config( string $filename ) {
		// 1. Resolve and extract the global software baseline layout from Git.
		$default_file = $this->get_module_data( 'config-dir' ) . $filename . '.php';
		$config       = is_readable( $default_file ) ? include $default_file : array();

		// 2. Discover the running context environment using the native home URL structure.
		$home_url  = function_exists( 'get_home_url' ) ? get_home_url() : '';
		$domain_id = str_replace( array( 'http://', 'https://', '.' ), array( '', '', '_' ), trailingslashit( $home_url ) );
		$domain_id = trim( $domain_id, '_' );

		// 3. Search and extract local ecosystem alterations matching this active partition.
		$domain_file = $this->get_module_data( 'config-dir' ) . 'domains/' . $domain_id . '/' . $filename . '.php';

		if ( is_readable( $domain_file ) ) {
			$domain_config = include $domain_file;

			if ( is_array( $config ) && is_array( $domain_config ) ) {
				$config = array_replace_recursive( $config, $domain_config );
			} else {
				$config = $domain_config; // Overwrite entirely if dealing with scalar string parameters.
			}
		}

		return $config;
	}
}
