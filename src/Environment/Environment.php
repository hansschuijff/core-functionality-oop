<?php
/**
 * General entrance to the environment of this plugin.
 *
 * Actively evaluates and buffers verified ecosystem representative states in memory.
 *
 * @package DeWittePrins\CoreFunctionality\Environment
 * @author  Hans Schuijff <@hansschuijff>
 * @license GPL-2.0
 * @since   1.0.0
 */

declare(strict_types=1);

namespace DeWittePrins\CoreFunctionality\Environment;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use DeWittePrins\CoreFunctionality\Plugin;
use DeWittePrins\CoreFunctionality\Environment\Core\WordPress;
use DeWittePrins\CoreFunctionality\Environment\Interfaces\DependencyInterface;
use DeWittePrins\CoreFunctionality\Environment\Services\PreflightRestructurer;
use DeWittePrins\CoreFunctionality\Services\ClassParser;
use InvalidArgumentException;
use Throwable;

// Import global PHP and WordPress functions.
use function class_exists;
use function is_array;
use function is_string;
use function str_ends_with;
use function sprintf;
use function esc_html;
use function error_log;
use function defined;
use function wp_die;
use function trim;
use function _doing_it_wrong;

/**
 * Class Environment
 *
 * Acts as a pure, lightweight runtime evaluator for external dependencies.
 * Follows a clean, single-point caching strategy embedded strictly at the dependency execution level.
 *
 * @since 1.0.0
 */
class Environment {

	/**
	 * The environment registry lookup service.
	 *
	 * @var EnvironmentLookup
	 */
	private EnvironmentLookup $lookup;

	/**
	 * In-memory storage cache mapping verified active dependency IDs.
	 *
	 * Only stores components that have actively proven to be ready (ID => true).
	 *
	 * @var array<string, bool>
	 */
	private array $readiness_cache = array();

	/**
	 * Environment Class Constructor.
	 *
	 * Purely passive initialization to ensure full lifecycle immunity.
	 *
	 * @since 1.0.0
	 * @param Plugin $plugin The main plugin instance.
	 */
	public function __construct( private readonly Plugin $plugin ) {
		$this->lookup = new EnvironmentLookup( $this->plugin );
	}

	/**
	 * Evaluates a complex AND/OR requirements matrix to determine system readiness.
	 *
	 * @since  1.0.0
	 * @param  mixed $needs Multi-dimensional array, flat array, or string tracking requirements.
	 * @return bool         True if all checks pass and the ecosystem is ready, false otherwise.
	 */
	public function is_ready( mixed $needs ): bool {
		if ( empty( $needs ) ) {
			return true;
		}

		// 1. Route single string identifier requests straight to the core evaluation processor.
		if ( is_string( $needs ) ) {
			$needs = trim( $needs );
			if ( empty( $needs ) ) {
				return true;
			}

			return $this->dependency_is_ready( $needs );
		}

		// 2. Synthesize multi-dimensional requirement maps cleanly.
		try {
			$needs = PreflightRestructurer::restructure( $needs );
		} catch ( InvalidArgumentException $e ) {
			return $this->handle_invalid_preflight_structure( $e );
		} catch ( Throwable $e ) {
			return $this->handle_invalid_preflight_structure( $e, 'Unforeseen fatal runtime error' );
		}

		return $this->check_readiness( $needs );
	}

	/**
	 * Evaluates a structured dependency matrix array containing and/or clauses.
	 *
	 * @since  1.0.0
	 * @param  array $needs Structured dependency array with and/or clauses.
	 * @return bool         True if all checks pass, false otherwise.
	 */
	private function check_readiness( array $needs ): bool {
		if ( isset( $needs['or'] ) && is_array( $needs['or'] ) ) {
			foreach ( $needs['or'] as $dependencies ) {
				if ( $this->check_readiness( $dependencies ) ) {
					return true;
				}
			}
			return false;
		}

		if ( ! empty( $needs['and'] ) && is_array( $needs['and'] ) ) {
			return $this->dependencies_ready( $needs['and'] );
		}

		return true;
	}

	/**
	 * Evaluates an array of dependency IDs with an AND-relation.
	 *
	 * @since  1.0.0
	 * @param  array $dependencies Array of dependency IDs to be checked.
	 * @return bool                True if all dependencies are ready, false otherwise.
	 */
	private function dependencies_ready( array $dependencies ): bool {
		if ( empty( $dependencies ) ) {
			return true;
		}

		foreach ( $dependencies as $id ) {
			if ( ! $this->dependency_is_ready( $id ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Checks a single dependency if it's ready, consolidating caching logic in one place.
	 *
	 * @since  1.0.0
	 * @param  string $dependency_id The identifier of a theme, plugin, or server environment.
	 * @return bool                  True if the single dependency is verified and ready for work.
	 */
	private function dependency_is_ready( string $dependency_id ): bool {
		// FIX: Single source of truth lookup pool check. If cached as true, bypass instantly.
		if ( isset( $this->readiness_cache[ $dependency_id ] ) && true === $this->readiness_cache[ $dependency_id ] ) {
			return true;
		}

		if ( 'wordpress_core' === $dependency_id ) {
			$is_ready = ( new WordPress() )->is_ready();
			if ( true === $is_ready ) {
				$this->readiness_cache[ $dependency_id ] = true;
				return true;
			}
			return false;
		}

		$class = $this->get_dependency_class_name( $dependency_id );

		// FIX: Use the unified DependencyInterface contract to validate existence safely.
		if ( ClassParser::is_implementation_of( $class, DependencyInterface::class ) ) {
			$is_ready = ( new $class( $this->lookup ) )->is_ready();

			// FIX: Only freeze the value if the live runtime proof answers affirmatively.
			if ( true === $is_ready ) {
				$this->readiness_cache[ $dependency_id ] = true;
				return true;
			}
		} else {
			_doing_it_wrong(
				__METHOD__,
				sprintf( 'Environment Contract Violation: Class "%s" fails to implement DependencyInterface.', esc_html( $class ) ),
				'1.0.0'
			);
		}

		return false;
	}

	/**
	 * Determines the fully qualified class name for a given dependency identifier.
	 *
	 * @since  1.0.0
	 * @param  string $dependency_id The identifier of a theme, plugin, or server environment.
	 * @return string                The fully qualified class name for the dependency.
	 */
	private function get_dependency_class_name( string $dependency_id ): string {
		if ( 'wordpress_core' === $dependency_id ) {
			return WordPress::class;
		}

		$clean_id   = ClassParser::get_clean_id( $dependency_id );
		$class_name = ClassParser::to_camel_case( $clean_id );
		$namespace  = $this->get_dependency_namespace( $dependency_id );

		return $namespace . '\\' . $class_name;
	}

	/**
	 * Determines the correct namespace for a given dependency identifier.
	 *
	 * @since  1.0.0
	 * @param  string $dependency_id The identifier of a theme, plugin, or server environment.
	 * @return string                The fully qualified namespace for the dependency.
	 */
	private function get_dependency_namespace( string $dependency_id ): string {
		$namespace_base = '\\DeWittePrins\\CoreFunctionality\\Environment\\';

		if ( str_ends_with( $dependency_id, '_theme' ) ) {
			return $namespace_base . 'Themes';
		}
		if ( str_ends_with( $dependency_id, '_environment' ) ) {
			return $namespace_base . 'Servers';
		}
		return $namespace_base . 'Plugins';
	}

	/**
	 * Handles log entries and developer confrontation upon discovering corrupt preflight structures.
	 *
	 * @since  1.0.0
	 * @param  Throwable $error        The caught exception or error instance.
	 * @param  string    $error_prefix Context descriptor for the log entry prefix.
	 * @return false                   Always returns false to trigger dependency failure.
	 */
	private function handle_invalid_preflight_structure( Throwable $error, string $error_prefix = 'Programmatic error: Invalid preflight structure' ): bool {
		error_log(
			sprintf(
				'[DeWittePrins Core CRITICAL] %s supplied to Environment::is_ready(). Message: %s',
				$error_prefix,
				$error->getMessage()
			)
		);

		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			wp_die(
				sprintf(
					'<h3>[DeWittePrins Framework Error]</h3>' .
					'<p>A structural programmatic error has been detected in one of the get_dependencies() implementations.</p>' .
					'<p><strong>Error Type:</strong> %s</p>' .
					'<p><strong>Error Message:</strong> %s</p>',
					esc_html( $error_prefix ),
					esc_html( $error->getMessage() )
				),
				'Preflight Structural Error',
				array( 'response' => 500 )
			);
		}

		return false;
	}
}
