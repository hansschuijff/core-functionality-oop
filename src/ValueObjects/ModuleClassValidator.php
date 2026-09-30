<?php
/**
 * File containing the ModuleClassValidator class.
 *
 * @package DeWittePrins\CoreFunctionality\ValueObjects
 * @author  Hans Schuijff <@hansschuijff>
 * @license GPL-2.0
 * @since   1.0.0
 */

declare( strict_types=1 );

namespace DeWittePrins\CoreFunctionality\ValueObjects;

use InvalidArgumentException;
use DeWittePrins\CoreFunctionality\Services\ClassParser;
use DeWittePrins\CoreFunctionality\Interfaces\ModuleInterface;

// Import global PHP and WordPress core functions.
use function class_exists;
use function is_subclass_of;
use function defined;
use function error_log;
use function sprintf;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Meta-validator for Module and Feature directory structures.
 *
 * Validates the module architecture via PSR-4 rules and returns
 * a strictly filtered array of features on success, or false on failure.
 *
 * @since 1.0.0
 */
final class ModuleClassValidator {

	/**
	 * Disable instantiation.
	 *
	 * This class functions purely as a static architecture gatekeeper.
	 *
	 * @since 1.0.0
	 */
	private function __construct() {}

	/**
	 * Universal gatekeeper method for complete Module hierarchies.
	 *
	 * Validates the module layout, filters out invalid nested features,
	 * and returns the cleaned feature tree array directly, or false if invalid.
	 *
	 * @since  1.0.0
	 * @param  string   $module_fqcn         The fully qualified module class name.
	 * @param  string[] $found_feature_slugs Array of raw feature directory slugs from disk.
	 * @param  string   $modules_namespace   The root modules namespace configuration.
	 * @return string[]|false                Array of verified feature FQCN strings, or false if invalid.
	 */
	public static function validate( string $module_fqcn, array $found_feature_slugs, string $modules_namespace ): array|false {
		try {
			if ( ! class_exists( $module_fqcn ) ) {
				throw new InvalidArgumentException( sprintf( "Module class '%s' does not exist.", $module_fqcn ) );
			}

			// FIX: Corrected contract exception message to explicitly mention ModuleInterface instead of FeatureInterface.
			if ( ! is_subclass_of( $module_fqcn, ModuleInterface::class ) ) {
				throw new InvalidArgumentException( sprintf( "Class '%s' violates contract: ModuleInterface is not implemented.", $module_fqcn ) );
			}

			$module_slug           = ClassParser::fqcn_remove_namespace( $module_fqcn );
			$expected_module_class = $modules_namespace . '\\' . $module_slug . '\\' . $module_slug;

			if ( $module_fqcn !== $expected_module_class ) {
				throw new InvalidArgumentException( sprintf( "Module '%s' resides in an illegal architecture subdirectory.", $module_fqcn ) );
			}

			$valid_features = array();
			foreach ( $found_feature_slugs as $feature_slug ) {
				$feature_fqcn = "{$modules_namespace}\\{$module_slug}\\Features\\{$feature_slug}\\{$feature_slug}";

				if ( FeatureClassValidator::is_valid( $feature_fqcn, $module_slug, $modules_namespace ) ) {
					$valid_features[] = $feature_fqcn;
				}
			}

			if ( empty( $valid_features ) ) {
				throw new InvalidArgumentException( sprintf( "Module '%s' is currently empty or has no active features.", $module_slug ) );
			}

			return $valid_features;

		} catch ( InvalidArgumentException $e ) {
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				error_log( sprintf( 'CoreFunctionality Architecture Notice: %s', $e->getMessage() ) );
			}

			return false;
		}
	}
}
