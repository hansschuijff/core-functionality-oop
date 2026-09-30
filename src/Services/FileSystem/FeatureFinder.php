<?php
/**
 * File containing the FinderService class.
 *
 * @package    DeWittePrins\CoreFunctionality
 * @subpackage Services\FileSystem
 * @author     Hans Schuijff <@hansschuijff>
 * @license    GPL-2.0
 * @since      1.0.0
 */

namespace DeWittePrins\CoreFunctionality\Services\FileSystem;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use DirectoryIterator;
use DeWittePrins\CoreFunctionality\Plugin;

/**
 * Service responsible for scanning the file system to locate autonomous features.
 *
 * Handles raw disk directory iterations to identify valid modular framework targets.
 *
 * @since 1.0.0
 */
class FinderService {

	/**
	 * FinderService Constructor.
	 *
	 * @since 1.0.0
	 * @param Plugin $plugin The central framework orchestrator instance.
	 */
	public function __construct(
		private readonly Plugin $plugin,
	) {
		$this->modules_dir       = $this->untrailingslashit( $this->plugin->get_data( 'modules-dir' ) );
		$this->modules_namespace = $this->plugin->get_data( 'modules-namespace' );
	}

}
