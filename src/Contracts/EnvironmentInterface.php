<?php
/**
 * Core Framework Environment Data Provider Contract.
 *
 * @package DeWittePrins\CoreFunctionality\Contracts
 * @since   1.0.0
 */

namespace DeWittePrins\CoreFunctionality\Contracts;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Interface EnvironmentInterface
 *
 * Extends the baseline dependency check to act as a structured environment data provider.
 */
interface EnvironmentInterface extends DependencyInterface {

	/**
	 * Retrieves formatted layout nodes and baseline configurations from the environment.
	 *
	 * @return array Multi-dimensional registry dataset tracking environmental properties.
	 */
	public function get_node_data(): array;
}
