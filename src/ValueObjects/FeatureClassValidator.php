<?php
/**
 * Feature Class Validator.
 *
 * Successor of the FeatureClass value object. It catches its own exceptions, reports the
 * violation with _doing_it_wrong() and returns a bool, so callers need no try/catch.
 *
 * @package DeWittePrins\CoreFunctionality\ValueObjects
 * @since   1.0.0
 */

declare( strict_types=1 );

/**
 * File containing the FeatureClassValidator Value Object.
 *
 * @package DeWittePrins\CoreFunctionality\ValueObjects
 * @author  Hans Schuijff <info@dewitteprins.nl>
 * @license GPL-2.0
 * @since   1.0.0
 */

namespace DeWittePrins\CoreFunctionality\ValueObjects;

use InvalidArgumentException;
use DeWittePrins\CoreFunctionality\Interfaces\FeatureInterface;
use DeWittePrins\CoreFunctionality\Services\ClassParser;

use function class_exists;
use function is_subclass_of;
use function defined;
use function error_log;
use function sprintf;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Validator for the architecture and PSR-4 directory structure of Features.
 *
 * Checks autonomously whether feature classes exist physically, implement the correct
 * interface, and reside strictly in their own identically named subfolder.
 *
 * @since 1.0.0
 */
class FeatureClassValidator {

	/**
	 * Disable instantiation.
	 *
	 * This class functions purely as a static utility gateway.
	 *
	 * @since 1.0.0
	 */
	private function __construct() {}

	// phpcs:disable Squiz.Commenting.FunctionCommentThrowTag.Missing -- The exceptions are thrown and caught inside this method.
	/**
	 * Universal gatekeeper method for Feature classes.
	 *
	 * Validates the structure, logs architectural violations to the debug log (with WP_DEBUG),
	 * and returns a clean boolean to the calling linear flow.
	 *
	 * @since  1.0.0
	 *
	 * @param  string $class_name        The fully qualified class name to validate.
	 * @param  string $module_slug       The slug of the parent module context.
	 * @param  string $modules_namespace The root namespace configuration of the plugin.
	 * @return bool                      True if the class structure is valid, false otherwise.
	 */
	public static function is_valid( string $class_name, string $module_slug, string $modules_namespace ): bool {
		// phpcs:enable Squiz.Commenting.FunctionCommentThrowTag.Missing
		try {
			if ( ! class_exists( $class_name ) ) {
				throw new InvalidArgumentException( "Feature class '{$class_name}' does not exist physically. Check your PSR-4 mapping." );
			}

			if ( ! is_subclass_of( $class_name, FeatureInterface::class ) ) {
				throw new InvalidArgumentException( "Class '{$class_name}' violates contract: FeatureInterface is not implemented." );
			}

			$feature_class_slug  = ClassParser::fqcn_remove_namespace( $class_name );
			$expected_class_name = "{$modules_namespace}\\{$module_slug}\\Features\\{$feature_class_slug}\\{$feature_class_slug}";

			if ( $class_name !== $expected_class_name ) {
				throw new InvalidArgumentException(
					"Architectural violation: Namespace mismatch for '{$class_name}'. " .
					"The class must reside strictly inside the 'Features/{$feature_class_slug}/' subfolder."
				);
			}

			return true;

		} catch ( InvalidArgumentException $e ) {
			// Quietly, like ModuleClassValidator: a feature removed from the code stays in the
			// register until the next scan, and the plugin goes on without it.
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				error_log( sprintf( 'CoreFunctionality Architecture Notice: %s', $e->getMessage() ) );
			}

			return false;
		}
	}
}
