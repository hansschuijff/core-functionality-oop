<?php
/**
 * De Witte Prins Core Functionality Environment Mapping Service.
 *
 * @package DeWittePrins\CoreFunctionality\Services
 * @since   1.0.0
 */

namespace DeWittePrins\CoreFunctionality\Services;

use DeWittePrins\CoreFunctionality\Plugin;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class EnvironmentMapper
 *
 * Resolves abstract environment dependency ID's into true physical WordPress basenames.
 * Completely encapsulated and decoupled from file system locations and merging pipelines.
 *
 * @since 1.0.0
 */
class EnvironmentMapper {

	/**
	 * Centralized register containing token-to-basename parameter strings.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	private array $mapping = array();

	/**
	 * EnvironmentMapper constructor.
	 *
	 * @param Plugin $plugin De globale hoofd-plugin container shell.
	 */
	public function __construct( Plugin $plugin ) {
		// PURE BLISS: The service simply requests the config. What happens under the hood is a black box!
		$this->mapping = $plugin->config->get( 'environment-index' );
	}

	/**
	 * Translates an abstract dependency ID into a physical active plugin basename descriptor string.
	 *
	 * @since 1.0.0
	 * @param string $dependency_id The abstract configuration reference key (e.g. 'woocommerce').
	 * @return string The running mapping script path string, or an empty string if missing.
	 */
	public function get_basename( string $dependency_id ): string {
		return $this->mapping[ $dependency_id ] ?? '';
	}
}
