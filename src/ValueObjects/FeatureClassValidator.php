<?php
/**
 * Dit is een opvolger van de FeatureClass VO met een betere naam
 * en de try-catch structuur ingebouwd, zodat de aanroepende kant
 * een bool returnwaarde terugkrijgt die getest kan worden.
 *
 * Voorheen moest je de aanroep van de VO in een try catch blok uitvoeren
 * en begrijpen dat als de code na de aanroep verder zou gaan het betekent
 * dat de vo de invoer goedgekeurd had, omat het anders de code had laten
 * crashen met een InvalidArgumentException die dan naar de catch liet
 * springen.
 *
 * Omdat de normale reactie van een quard is om de foutstatus
 * in een doing it wrong naar de log te schrijven doet de VO
 * dat nu zelf en geeft vervolgens een false waarde terug
 * als er een fout was en true als de invoer is goedgekeurd.
 */

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
use function esc_html;

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

	/**
	 * Universal gatekeeper method for Feature classes.
	 *
	 * Validates the structure, logs architectural violations directly via WordPress core
	 * developer tools, and returns a clean boolean to the calling linear flow.
	 *
	 * @since  1.0.0
	 *
	 * @param  string $class_name        The fully qualified class name to validate.
	 * @param  string $module_slug       The slug of the parent module context.
	 * @param  string $modules_namespace The root namespace configuration of the plugin.
	 * @return bool                      True if the class structure is valid, false otherwise.
	 */
	public static function is_valid( string $class_name, string $module_slug, string $modules_namespace ): bool {
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
			// Trigger a standard WordPress core developer notice in English.
			_doing_it_wrong(
				__CLASS__ . '::' . __FUNCTION__,
				esc_html( $e->getMessage() ),
				'1.0.0'
			);

			return false;
		}
	}
}
