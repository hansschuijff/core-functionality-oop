<?php
/**
 * Automated File System Pather and Contextual Storage Hulls.
 *
 * @package DeWittePrins\Core
 * @since   4.0.0
 */

namespace DeWittePrins\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class ModuleContext
 *
 * Computes, organizes, and locks down absolute directory tracks and public URL
 * patterns for active modules dynamically on boot using localized system contexts.
 *
 * @since 4.0.0
 */
class ModuleContext {

	/**
	 * Data repository dictionary tree caching verified layout trajectories.
	 *
	 * @since 4.0.0
	 * @var array
	 */
	private array $data = array();

	/**
	 * ModuleContext constructor.
	 *
	 * Translates modular configurations smoothly applying strict Convention over Configuration.
	 *
	 * @since 4.0.0
	 * @param string $module_id  The unique identity lookup token of the target package folder.
	 * @param string $plugin_dir Absolute baseline core tracking path fetched from the main container.
	 * @param string $plugin_url Publicly accessible web uniform location endpoint fetched from container.
	 */
	public function __construct( string $module_id, string $plugin_dir, string $plugin_url ) {
		// Calculate all trajectories automatically utilizing default framework conventions.
		$module_dir = $plugin_dir . 'src/Modules/' . $module_id . '/';
		$module_url = $plugin_url . 'src/Modules/' . $module_id . '/';

		$this->data = array(
			'base-dir'     => $module_dir,
			'views-dir'    => $module_dir . 'views/',
			'language-dir' => $module_dir . 'languages/',
			'base-url'     => $module_url,
			'views-url'    => $module_url . 'views/',
			'assets-url'   => $module_url . 'assets/',
		);
	}

	/**
	 * Extracts an absolute file track path indicator string from cache indexes.
	 *
	 * @since 4.0.0
	 * @param string $key Property track reference selector token (e.g. 'views-url').
	 * @return string The absolute system destination target, or an empty placeholder string.
	 */
	public function get( string $key ): string {
		return $this->data[ $key ] ?? '';
	}
}
