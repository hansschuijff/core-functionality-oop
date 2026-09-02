<?php
/**
 * Environment Orchestrator and Pre-Flight Evaluation Engine.
 *
 * @package DeWittePrins\Environment
 * @since   4.0.0
 */

namespace DeWittePrins;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class Environment
 *
 * Acting as the unified evaluator for external dependencies. Parses requirement
 * matrices dynamically to determine if the active WordPress context is ready.
 *
 * @since 4.0.0
 */
class Environment {

	/**
	 * Evaluates a complex AND/OR requirements matrix to determine system readiness.
	 *
	 * @since 4.0.0
	 * @param array $matrix Multi-dimensional array tracking environmental requirements.
	 * @return bool True if all checks pass and the ecosystem is ready, false otherwise.
	 */
	public static function is_ready( array $matrix ): bool {

		if ( isset( $matrix['or'] ) ) {
			foreach ( $matrix['or'] as $dependencies ) {
				// Calls itself recursively to evaluate or-clauses in the matrix.
				if ( self::is_ready( $dependencies ) ) {
					return true;
				}
			}
			return false;
		}

		// If no more or-clauses are present, check for and-clauses (or default to true if none exist).
		if ( ! empty( $matrix['and'] ) ) {
			return self::dependencies_ready( $matrix['and'] );
		}

		return true;
	}

	/**
	 * Evaluates an array of dependency_id's with an AND-relation if they are all ready.
	 *
	 * @since 4.0.0
	 * @param array $dependencies Array of dependency_ids to be checked (in an and-relationship).
	 * @return bool True if all dependencies (plugins, themes and/or environment) are available and ready.
	 */
	public static function dependencies_ready( array $dependencies ): bool {
		// No dependencies to check, default to ready.
		if ( empty( $dependencies ) ) {
			return true;
		}

		// If any dependency is not ready, return false immediately (circuit breaker).
		foreach ( $dependencies as $id ) {
			if ( ! self::dependency_is_ready( $id ) ) {
				return false;
			}
		}

		// All dependencies are ready, return true.
		return true;
	}

	/**
	 * Checks a single dependency if it's ready for business.
	 *
	 * @since 4.0.0
	 * @param string $dependency_id The identifier of a theme, plugin or server environment (e.g., 'any_genesis_theme', 'production_environment' or 'woocommerce').
	 * @return bool True if a single dependency is ready for work.
	 */
	private static function dependency_is_ready( string $dependency_id ): bool {

		// 1. CLEANING: Remove the suffix descriptor.
		$clean_id = self::get_clean( $dependency_id );

		// 2. TRANSFORMATION: Convert snake_case to CamelCase (e.g., 'any_genesis' -> 'AnyGenesis').
		$class_name = self::to_camel_case( $clean_id );

		// 3. NAMESPACE RESOLUTION: Determine the correct namespace based on the dependency type.
		$namespace = self::get_dependency_namespace( $dependency_id );

		// 4. EXECUTION: Target the exact fully qualified namespace path.
		$class = $namespace . '\\' . $class_name;

		if ( class_exists( $class ) ) {
			return ( new $class() )->is_ready();
		}

		return false; // Guard not found, default to safe abort.
	}

	/**
	 * Determines the correct namespace for a given dependency identifier.
	 *
	 * @since 4.0.0
	 * @param string $dependency_id The identifier of a theme, plugin or server environment (e.g., 'any_genesis_theme', 'production_environment' or 'woocommerce').
	 * @return string The fully qualified namespace for the dependency.
	 */
	private static function get_dependency_namespace( string $dependency_id ): string {

		$prefix = '\\DeWittePrins\\Environment\\';

		if ( str_ends_with( $dependency_id, '_theme' ) ) {
			return $prefix . 'Themes';
		}
		if ( str_ends_with( $dependency_id, '_environment' ) ) {
			return $prefix . 'Servers';
		}
		return $prefix . 'Plugins';
	}

	/**
	 * Removes the configuration suffix from a dependency identifier.
	 *
	 * @since 4.0.0
	 * @param string $dependency_id The identifier of a theme, plugin or server environment.
	 * @return string The cleaned dependency identification token.
	 */
	private static function get_clean( string $dependency_id ): string {
		if ( str_ends_with( $dependency_id, '_theme' ) ) {
			return substr( $dependency_id, 0, -6 );
		}
		if ( str_ends_with( $dependency_id, '_environment' ) ) {
			return substr( $dependency_id, 0, -12 );
		}

		return $dependency_id;
	}

	/**
	 * Converts a standard snake_case configuration string into strict CamelCase formatting.
	 *
	 * @since 4.0.0
	 * @param string $snake_case The raw identifier string to convert.
	 * @return string The compiled CamelCase configuration output token string.
	 */
	private static function to_camel_case( string $snake_case ): string {
		return str_replace( ' ', '', ucwords( str_replace( '_', ' ', $snake_case ) ) );
	}
}
