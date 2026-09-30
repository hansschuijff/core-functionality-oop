<?php
/**
 * PluginInspector Service Component.
 *
 * @package    DeWittePrins\CoreFunctionality
 * @subpackage Environment\Plugins\Services
 * @author     Hans Schuijff <@hansschuijff>
 * @license    GPL-2.0
 * @since      1.0.0
 */

declare(strict_types=1);

namespace DeWittePrins\CoreFunctionality\Environment\Plugins\Services;

use DeWittePrins\CoreFunctionality\Environment\DTO\ProofRequirement;

use function class_exists;
use function defined;
use function did_action;
use function doing_action;
use function explode;
use function function_exists;
use function is_array;
use function is_plugin_active;
use function is_string;
use function method_exists;
use function trim;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class PluginInspector
 *
 * Inspects the active WordPress plugin profile using defensive checks.
 *
 * @since 1.0.0
 */
class PluginInspector {

	/**
	 * Checks if a specific plugin is active within the WordPress installation.
	 *
	 * Re-introduced to prevent fatal undefined method exceptions.
	 *
	 * @since 1.0.0
	 * @param string $plugin_basename The plugin path relative to the plugins directory (e.g., 'slug/plugin.php').
	 * @return bool True if the plugin is active, false otherwise.
	 */
	public static function is_plugin_active( string $plugin_basename ): bool {
		$plugin_basename = trim( $plugin_basename );
		if ( empty( $plugin_basename ) ) {
			return false;
		}

		// Ensure the native WordPress plugin API is accessible if called very early.
		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		return is_plugin_active( $plugin_basename );
	}

	/**
	 * Evaluates a structured ProofRequirement DTO to determine if a plugin is loaded.
	 *
	 * Acts as the primary, crystal-clear entrance gate for validation.
	 *
	 * @since 1.0.0
	 * @param ProofRequirement $requirement The structured proof criteria object.
	 * @return bool True if all required elements pass and no negative actions fired, false otherwise.
	 */
	public static function must_exist( ProofRequirement $requirement ): bool {
		// 1. Verify required classes exist (without triggering autoloading).
		foreach ( $requirement->classes as $class ) {
			if ( ! empty( $class ) && ! self::proof( 'class', $class ) ) {
				return false;
			}
		}

		// 2. Verify global functions exist (Defensive bulk checks).
		foreach ( $requirement->functions as $function ) {
			if ( ! empty( $function ) && ! self::proof( 'function', $function ) ) {
				return false;
			}
		}

		// 3. Verify object/class methods exist (Format: 'Tribe__Tickets__Main::activate').
		foreach ( $requirement->methods as $method_identifier ) {
			if ( ! empty( $method_identifier ) && ! self::proof( 'method', $method_identifier ) ) {
				return false;
			}
		}

		// 4. Verify global constants are defined.
		foreach ( $requirement->constants as $constant ) {
			if ( ! empty( $constant ) && ! self::proof( 'constant', $constant ) ) {
				return false;
			}
		}

		// 5. Verify positive action hooks have fired or are executing.
		if ( ! empty( $requirement->actions ) && ! self::did_actions( $requirement->actions ) ) {
			return false;
		}

		// 6. Firewall Guard: Verify negative action hooks have NOT fired (e.g., abort hooks).
		if ( ! empty( $requirement->not_actions ) && ! self::did_not_actions( $requirement->not_actions ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Checks if a collection of action hooks have fired or are currently executing.
	 *
	 * @since 1.0.0
	 * @param string[] $hooks Array of action hook identifiers.
	 * @return bool True if all hooks have run, false otherwise.
	 */
	public static function did_actions( array $hooks ): bool {
		foreach ( $hooks as $hook ) {
			if ( empty( $hook ) || ! is_string( $hook ) ) {
				continue;
			}

			if ( did_action( $hook ) || doing_action( $hook ) ) {
				continue;
			}

			return false; // Hook did not run yet
		}
		return true;
	}

	/**
	 * Checks if a collection of negative action hooks have NOT fired.
	 *
	 * @since 1.0.0
	 * @param string[] $hooks Array of forbidden action hook identifiers.
	 * @return bool True if NONE of the hooks have run, false if any hook has fired.
	 */
	public static function did_not_actions( array $hooks ): bool {
		foreach ( $hooks as $hook ) {
			if ( empty( $hook ) || ! is_string( $hook ) ) {
				continue;
			}

			// Circuit Breaker: If the abort/failure hook did run, the verification fails.
			if ( did_action( $hook ) || doing_action( $hook ) ) {
				return false;
			}
		}
		return true;
	}

	/**
	 * Check a single proof criterion to determine if a structural footprint is present.
	 *
	 * @since 1.0.0
	 * @param  string $method The detection method ('function', 'class', 'method', 'constant').
	 * @param  string $proof  The name of the component target.
	 * @return bool True if found, false otherwise.
	 */
	public static function proof( string $method, string $proof ): bool {
		$proof = trim( $proof );
		if ( empty( $proof ) ) {
			return false;
		}

		return match ( $method ) {
			'class'    => class_exists( $proof, false ),
			'function' => function_exists( $proof ),
			'constant' => defined( $proof ),
			'method'   => self::verify_method_string( $proof ),
			default    => false,
		};
	}

	/**
	 * Destructures a method string and validates its runtime existence.
	 *
	 * @param string $method_string Format: "ClassName::methodName".
	 * @return bool True if class and method exist, false otherwise.
	 */
	private static function verify_method_string( string $method_string ): bool {
		if ( ! str_contains( $method_string, '::' ) ) {
			return false;
		}

		$parts = explode( '::', $method_string );
		if ( 2 !== count( $parts ) ) {
			return false;
		}

		$class  = trim( $parts[0] );
		$method = trim( $parts[1] );

		return class_exists( $class, false ) && method_exists( $class, $method );
	}
}
