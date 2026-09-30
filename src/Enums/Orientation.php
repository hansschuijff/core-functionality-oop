<?php
/**
 * File containing the Orientation Enum.
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
 * Enum indicating the intended running context for a Module or Feature.
 *
 * Helps the Kernel determine whether code should be bootstrapped for Admin, Frontend, or both.
 *
 * @since 1.0.0
 */
enum Orientation: string {

	case FRONTEND = 'frontend';
	case ADMIN    = 'admin';
	case BOTH     = 'both';
}
