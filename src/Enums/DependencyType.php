<?php
/**
 * File containing the DependencyType Enum.
 *
 * @package DeWittePrins\CoreFunctionality\Enums
 * @author  Hans Schuijff @hansschuijff
 * @license GPL-2.0
 * @since   1.0.0
 */

namespace DeWittePrins\CoreFunctionality\Enums;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enum representing the type of dependency.
 *
 * Used by the Kernel to filter component activation based on the server environment.
 *
 * @since 1.0.0
 */
enum DependencyType: string {

	case PLUGIN      = 'plugin';
	case THEME       = 'theme';
	case SERVER      = 'server';

	/**
	 * Returns the localized human-readable label for the status.
	 *
	 * @since  1.0.0
	 * @return string The translated status label.
	 */
	public function get_label(): string {
		return match ( $this ) {
			self::PLUGIN => __( 'Plugin', 'dwp-cf' ),
			self::THEME  => __( 'Theme', 'dwp-cf' ),
			self::SERVER => __( 'Server', 'dwp-cf' ),
		};
	}
}
