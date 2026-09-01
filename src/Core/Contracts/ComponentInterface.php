<?php
/**
 * Structural Contract for Administrative Interface Components.
 *
 * @package DeWittePrins\Core\Contracts
 * @since   4.0.0
 */

namespace DeWittePrins\Core\Contracts;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! interface_exists( __NAMESPACE__ . '\ComponentInterface' ) ) {
	/**
	 * Interface ComponentInterface
	 *
	 * Enforces the required payload structure for autonomous dashboard and
	 * admin toolbar blocks, enabling clean separation of UI concerns.
	 *
	 * @since 4.0.0
	 */
	interface ComponentInterface {

		/**
		 * Retrieves the unique node attributes required for WordPress layout injection.
		 *
		 * Used directly by the centralized ComponentRegistry to map, filter,
		 * and sort administrative dashboard elements based on user options.
		 *
		 * @since 4.0.0
		 * @return array Required schema array mapping 'id', 'title', 'parent', and 'slug'.
		 */
		public function get_node_data(): array;
	}
}
