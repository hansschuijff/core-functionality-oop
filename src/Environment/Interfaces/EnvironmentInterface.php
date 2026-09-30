<?php
/**
 * Core Framework Environment Data Provider Contract.
 *
 * @package    DeWittePrins\CoreFunctionality
 * @subpackage Environment\Interfaces
 * @since   1.0.0
 */

namespace DeWittePrins\CoreFunctionality\Environment\Interfaces;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Interface EnvironmentInterface
 *
 * Extends the baseline dependency check to act as a structured environment data provider.
 */
interface EnvironmentInterface extends DependencyInterface {
	// The abstract protected data routines reside securely inside the Abstract base classes.
}
