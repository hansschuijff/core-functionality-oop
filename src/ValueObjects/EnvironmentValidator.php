<?php
/**
 * Environment Representative Class Validator Value Object.
 *
 * @package DeWittePrins\CoreFunctionality\ValueObjects
 * @author  Hans Schuijff <@hansschuijff>
 * @license GPL-2.0
 * @since   1.0.0
 */

declare( strict_types=1 );

namespace DeWittePrins\CoreFunctionality\ValueObjects;

use DeWittePrins\CoreFunctionality\Environment\Plugins\Interfaces\PluginInterface;
use DeWittePrins\CoreFunctionality\Environment\Themes\Interfaces\ThemeInterface;
use DeWittePrins\CoreFunctionality\Services\ClassParser;

use function class_exists;

/**
 * Class EnvironmentValidator
 *
 * Validates if a given class string is a structurally sound and authorized
 * ecosystem representative component matching framework interface contracts.
 */
final class EnvironmentValidator {

	/**
	 * Disable instantiation to enforce pure Value Object contract usage.
	 */
	private function __construct() {}

	/**
	 * Validates if a class exists and conforms to the expected environment interfaces.
	 *
	 * @since  1.0.0
	 * @param  string $class_fqcn The Fully Qualified Class Name string to validate.
	 * @return bool               True if the class passes all structural gates, false otherwise.
	 */
	public static function validate( string $class_fqcn ): bool {
		// Gate 1: Physical existence check.
		if ( ! class_exists( $class_fqcn ) ) {
			return false;
		}

		// Gate 2: Interface contract enforcement.
		$allowed_interfaces = array(
			PluginInterface::class,
			ThemeInterface::class,
		);

		return ClassParser::is_implementation_of( $class_fqcn, $allowed_interfaces );
	}
}
