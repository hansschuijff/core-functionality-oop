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
use DeWittePrins\CoreFunctionality\Enums\ProviderStatus;
use DeWittePrins\CoreFunctionality\Environment\Core\WordPress;
use DeWittePrins\CoreFunctionality\Environment\Interfaces\DependencyInterface;
use DeWittePrins\CoreFunctionality\Environment\Plugins\Interfaces\PluginInterface;
use DeWittePrins\CoreFunctionality\Environment\Registers\AdminToolbarRegister;
use DeWittePrins\CoreFunctionality\Environment\Services\PreflightRestructurer;
use DeWittePrins\CoreFunctionality\Environment\Themes\Interfaces\ThemeInterface;
use DeWittePrins\CoreFunctionality\Services\ClassParser;
use InvalidArgumentException;
use Throwable;

// Import global PHP and WordPress functions.
use function array_key_exists;
use function basename;
use function glob;
use function is_array;
use function str_starts_with;
use function is_string;
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
	 * The plugin guards created so far (null: no guard class for that id).
	 *
	 * @var array<string, PluginInterface|null>
	 */
	private array $plugins = array();

	/**
	 * The theme guards created so far (null: no guard class for that id).
	 *
	 * @var array<string, ThemeInterface|null>
	 */
	private array $themes = array();

	/**
	 * The guard of the WordPress core, once created.
	 *
	 * @var WordPress|null
	 */
	private ?WordPress $wordpress = null;

	/**
	 * Environment Class Constructor.
	 *
	 * Purely passive initialization: the toolbar catalogue is only filled when it is first asked for.
	 *
	 * @since 1.0.0
	 * @param Plugin $plugin The main plugin instance.
	 */
	public function __construct( private readonly Plugin $plugin ) {
		$this->lookup = new EnvironmentLookup( $this->plugin );

		AdminToolbarRegister::set_loader( array( $this, 'load_toolbar_catalogue' ) );
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
	 * @param  string $dependency_id The identifier of a theme or plugin.
	 * @return bool                  True if the single dependency is verified and ready for work.
	 */
	private function dependency_is_ready( string $dependency_id ): bool {
		// FIX: Single source of truth lookup pool check. If cached as true, bypass instantly.
		if ( isset( $this->readiness_cache[ $dependency_id ] ) && true === $this->readiness_cache[ $dependency_id ] ) {
			return true;
		}

		$guard = $this->get_guard( $dependency_id );

		if ( null === $guard ) {
			_doing_it_wrong(
				__METHOD__,
				sprintf( 'Environment Contract Violation: no guard class implementing DependencyInterface for "%s".', esc_html( $dependency_id ) ),
				'1.0.0'
			);
			return false;
		}

		// Only freeze the value if the live runtime proof answers affirmatively.
		if ( true === $guard->is_ready() ) {
			$this->readiness_cache[ $dependency_id ] = true;
			return true;
		}

		return false;
	}

	/**
	 * Returns the guard of the WordPress core.
	 *
	 * @since  1.0.0
	 * @return WordPress The core guard.
	 */
	public function get_wordpress(): WordPress {
		$this->wordpress ??= new WordPress();

		return $this->wordpress;
	}

	/**
	 * Returns the guard of a plugin, created once per request.
	 *
	 * @since  1.0.0
	 * @param  string $plugin_id The plugin id (the guard's class name in kebab-case).
	 * @return PluginInterface|null The guard, or null if there is no guard class for this id.
	 */
	public function get_plugin( string $plugin_id ): ?PluginInterface {
		if ( ! array_key_exists( $plugin_id, $this->plugins ) ) {
			$class = $this->get_dependency_class_name( $plugin_id );

			$this->plugins[ $plugin_id ] = ClassParser::is_implementation_of( $class, PluginInterface::class )
				? new $class( $this->lookup )
				: null;
		}

		return $this->plugins[ $plugin_id ];
	}

	/**
	 * Returns the guard of a theme, created once per request.
	 *
	 * @since  1.0.0
	 * @param  string $theme_id The theme id (the guard's class name in kebab-case plus "-theme").
	 * @return ThemeInterface|null The guard, or null if there is no guard class for this id.
	 */
	public function get_theme( string $theme_id ): ?ThemeInterface {
		if ( ! array_key_exists( $theme_id, $this->themes ) ) {
			$class = $this->get_dependency_class_name( $theme_id );

			$this->themes[ $theme_id ] = ClassParser::is_implementation_of( $class, ThemeInterface::class )
				? new $class( $this->lookup )
				: null;
		}

		return $this->themes[ $theme_id ];
	}

	/**
	 * Fills the admin toolbar catalogue with the nodes of the core and every known plugin and theme.
	 *
	 * Known means: a guard class exists in Environment/Plugins or Environment/Themes; this plugin's
	 * own guard (Plugins\CoreFunctionality) is one of them and always ready. Each provider is
	 * registered with its status, so the settings page can offer all nodes and the toolbar shows
	 * only those of active providers. Called by AdminToolbarRegister on first use.
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public function load_toolbar_catalogue(): void {
		$core = $this->get_wordpress();
		AdminToolbarRegister::register( $core->get_id(), $core->get_toolbar_nodes(), ProviderStatus::ACTIVE, $core->get_name() );

		foreach ( $this->get_known_guard_ids() as $dependency_id ) {
			$guard = ClassParser::is_theme_id( $dependency_id )
				? $this->get_theme( $dependency_id )
				: $this->get_plugin( $dependency_id );

			if ( null === $guard ) {
				continue;
			}

			$status = ProviderStatus::NOT_INSTALLED;
			if ( $this->is_ready( $dependency_id ) ) {
				$status = ProviderStatus::ACTIVE;
			} elseif ( $guard->is_installed() ) {
				$status = ProviderStatus::INACTIVE;
			}

			// The name as a callable: a plugin's name comes from its header, read only when asked for.
			AdminToolbarRegister::register( $guard->get_id(), $guard->get_toolbar_nodes(), $status, array( $guard, 'get_name' ) );
		}
	}

	/**
	 * Returns the ids of all plugin and theme guard classes.
	 *
	 * @since  1.0.0
	 * @return string[] Plugin ids and theme ids (with the "-theme" suffix).
	 */
	private function get_known_guard_ids(): array {
		$ids = array();

		$folders = array(
			'Plugins' => '',
			'Themes'  => '-theme',
		);

		foreach ( $folders as $folder => $suffix ) {
			$files = glob( __DIR__ . '/' . $folder . '/*.php' );

			foreach ( false !== $files ? $files : array() as $file ) {
				$class_name = basename( $file, '.php' );
				if ( str_starts_with( $class_name, 'Abstract' ) ) {
					continue;
				}

				$ids[] = ClassParser::camel_to_kebab( $class_name ) . $suffix;
			}
		}

		return $ids;
	}

	/**
	 * Returns the guard for a dependency id: the core, a plugin or a theme.
	 *
	 * @since  1.0.0
	 * @param  string $dependency_id The identifier of the core, a theme or a plugin.
	 * @return DependencyInterface|null The guard, or null if there is no guard class for this id.
	 */
	private function get_guard( string $dependency_id ): ?DependencyInterface {
		if ( 'wordpress-core' === $dependency_id ) {
			return $this->get_wordpress();
		}
		if ( ClassParser::is_theme_id( $dependency_id ) ) {
			return $this->get_theme( $dependency_id );
		}

		return $this->get_plugin( $dependency_id );
	}

	/**
	 * Determines the fully qualified class name for a given dependency identifier.
	 *
	 * @since  1.0.0
	 * @param  string $dependency_id The identifier of a theme or plugin.
	 * @return string                The fully qualified class name for the dependency.
	 */
	private function get_dependency_class_name( string $dependency_id ): string {
		$clean_id   = ClassParser::get_clean_id( $dependency_id );
		$class_name = ClassParser::to_camel_case( $clean_id );
		$namespace  = $this->get_dependency_namespace( $dependency_id );

		return $namespace . '\\' . $class_name;
	}

	/**
	 * Determines the correct namespace for a given dependency identifier.
	 *
	 * @since  1.0.0
	 * @param  string $dependency_id The identifier of a theme or plugin.
	 * @return string                The fully qualified namespace for the dependency.
	 */
	private function get_dependency_namespace( string $dependency_id ): string {
		$namespace_base = '\\DeWittePrins\\CoreFunctionality\\Environment\\';

		if ( ClassParser::is_theme_id( $dependency_id ) ) {
			return $namespace_base . 'Themes';
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
