<?php
/**
 * Framework Lifecycle and Runtime Authorization Kernel.
 *
 * @package DeWittePrins\CoreFunctionality
 * @author  Hans Schuijff <@hansschuijff>
 * @license GPL-2.0
 * @since   1.0.0
 */

namespace DeWittePrins\CoreFunctionality;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use DeWittePrins\CoreFunctionality\Interfaces\ModuleInterface;
use DeWittePrins\CoreFunctionality\Interfaces\FeatureInterface;
use DeWittePrins\CoreFunctionality\Services\InterDependenciesGuard;
use DeWittePrins\CoreFunctionality\Environment\Environment;
use DeWittePrins\CoreFunctionality\Services\ClassParser;
use DeWittePrins\CoreFunctionality\Services\ComponentsRegister;
use DeWittePrins\CoreFunctionality\Enums\Orientation;
use DeWittePrins\CoreFunctionality\Enums\Register;
use DeWittePrins\CoreFunctionality\Environment\Themes\Services\ThemeInspector;

// Import global functions.
use function _doing_it_wrong;
use function esc_html;
use function is_admin;
use function is_array;
use function array_flip;
use function array_key_exists;
use function apply_filters;
use function add_action;
use function doing_action;
use function did_action;
use function current_filter;
use function sprintf;
use function explode;
use function count;
use function class_exists;
use function is_subclass_of;
use function method_exists;

/**
 * Class Kernel
 *
 * Orchestrates secure module and feature initialization, enforces strict
 * lifecycle timing, and guards execution boundaries through whitelist validation.
 *
 * @since 1.0.0
 */
class Kernel {

	/**
	 * The current Orientation layer target.
	 *
	 * @var Orientation
	 */
	private Orientation $current_orientation;

	/**
	 * The hook where the schedule_launches callback is hooked.
	 *
	 * @var string
	 */
	private string $load_hook = 'plugins_loaded';

	/**
	 * The priority that is used to hook the schedule_launches callback.
	 *
	 * @var int
	 */
	private int $load_priority = 10;

	/**
	 * The hook where the launch callback is hooked.
	 *
	 * @var string
	 */
	private string $launch_hook = 'init';

	/**
	 * The priority that is used to hook the launch callback to the launch_hook.
	 *
	 * @var int
	 */
	private int $launch_priority = 10;

	/**
	 * Internal cache for live instantiated module and feature objects.
	 *
	 * @var array<string, object>
	 */
	private array $instances = array();

	/**
	 * Internal cache for features that still need to be instantiated.
	 *
	 * @var array<string, array>
	 */
	private array $features = array();

	/**
	 * In-memory runtime cache for the loaded topology components_register file.
	 *
	 * @var array<string, array<int, string>>|null
	 */
	private ?array $components_register_cache = null;

	/**
	 * State tracker indicating if the execution launch cycle has completed.
	 *
	 * @var bool
	 */
	private bool $did_launch = false;

	/**
	 * Kernel Constructor.
	 *
	 * @since 1.0.0
	 * @param Plugin                 $plugin                 Global main plugin container shell.
	 * @param InterDependenciesGuard $interdependency_guard   Shared interdependency checker service.
	 * @param Environment            $environment            Unified environment evaluator service.
	 * @param ComponentsRegister     $components_register    Dynamic configuration caching tracker register.
	 */
	public function __construct(
		private readonly Plugin $plugin,
		private readonly InterDependenciesGuard $interdependency_guard,
		private readonly Environment $environment,
		private readonly ComponentsRegister $components_register,
	) {
		$this->current_orientation = is_admin() ? Orientation::ADMIN : Orientation::FRONTEND;

		$this->set_add_action(
			hook:     $this->load_hook,
			priority: $this->load_priority,
			callback: array( $this, 'schedule_launches' ),
			filter:   'dwp_cf_plugin_load_timing'
		);

		$this->set_add_action(
			hook:     $this->launch_hook,
			priority: $this->launch_priority,
			callback: array( $this, 'launch' ),
			filter:   'dwp_cf_launch_timing'
		);
	}

	/**
	 * Registers callbacks safely wrapper around WordPress add_action logic configurations layers.
	 *
	 * @since  1.0.0
	 * @return void
	 */
	private function set_add_action( string $hook, int $priority, string|array $callback, string $filter ): void {
		$default_timing = array(
			'hook'     => $hook,
			'priority' => $priority,
		);

		$filtered_timing   = apply_filters( $filter, $default_timing );
		$filtered_hook     = $filtered_timing['hook']     ?? $hook;
		$filtered_priority = $filtered_timing['priority'] ?? $priority;

		add_action( $filtered_hook, $callback, $filtered_priority );
	}

	/**
	 * Schedules all modules and features in the filtered components register.
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public function schedule_launches(): void {
		// FIX: Trigger the complete active environment identification node harvesting pool exactly on plugins_loaded.
		$this->build_admin_nodes_data();

		$launchable = $this->get_launchable_components();
		if ( ! empty( $launchable ) && is_array( $launchable ) ) {
			foreach ( $launchable as $module_fqcn => $features_fqcn ) {
				$this->schedule_module_launch( $module_fqcn );
				$this->schedule_features_launch( $features_fqcn, $module_fqcn );
			}
		}

		$this->all_modules_are_scheduled();
	}

	/**
	 * Dispatches active environment IDs systematically to aggregate data nodes into the master registries.
	 *
	 * @since  1.0.0
	 * @return void
	 */
	private function build_admin_nodes_data(): void {
		$active_ids = $this->get_active_environment_ids();
		foreach ( $active_ids as $dependency_id ) {
			$this->environment->is_ready( $dependency_id );
		}
	}

	/**
	 * Scans WordPress parameters to establish an accurate collection map of active external resources.
	 *
	 * @since  1.0.0
	 * @return string[] Array mapping abstract dependency IDs found active in the system ecosystem.
	 */
	private function get_active_environment_ids(): array {
		$environment_index = $this->plugin->settings->get( 'environment-index', default: array() );
		if ( empty( $environment_index ) || ! is_array( $environment_index ) ) {
			return array( 'wordpress_core' );
		}

		$active_plugins = $this->plugin->settings->get_unmanaged( 'active_plugins', array() );
		if ( ! is_array( $active_plugins ) ) {
			$active_plugins = array();
		}

		$active_ids   = array();
		$active_ids[] = 'wordpress_core';

		// 1. ACCUMULATE ACTIVE PLUGIN MAPPINGS
		$plugin_pool = $environment_index['plugins'] ?? array();
		if ( is_array( $plugin_pool ) && ! empty( $plugin_pool ) ) {
			$plugin_keys = array_flip( $plugin_pool );

			foreach ( $active_plugins as $active_plugin ) {
				if ( array_key_exists( $active_plugin, $plugin_keys ) ) {
					$active_ids[] = (string) $plugin_keys[ $active_plugin ];
				}
			}
		}

		// 2. ACCUMULATE ACTIVE THEME MAPPINGS
		$theme_pool = $environment_index['themes'] ?? array();
		if ( is_array( $theme_pool ) && ! empty( $theme_pool ) ) {
			foreach ( $theme_pool as $id => $theme ) {
				if ( is_array( $theme ) && ThemeInspector::is_theme_active( $theme ) ) {
					$active_ids[] = $id . '_theme';
				}
			}
		}

		return $active_ids;
	}

	/**
	 * Filters active system topography maps to isolate components cleared for execution loops.
	 *
	 * @since  1.0.0
	 * @return array<string, array> Cleared launchable matrix layout.
	 */
	private function get_launchable_components(): array {
		$register = $this->plugin->components_register->get();
		if ( empty( $register ) || ! is_array( $register ) ) {
			return array();
		}

		$launchable = array();
		foreach ( $register as $module_fqcn => $features_fqcn ) {
			if ( ! $this->is_launchable( $module_fqcn ) ) {
				continue;
			}
			foreach ( $features_fqcn as $feature_fqcn ) {
				if ( ! $this->is_launchable( $feature_fqcn ) ) {
					continue;
				}
				$launchable[ $module_fqcn ][] = $feature_fqcn;
			}
		}
		return $launchable;
	}

	/**
	 * Runs a blueprint class string validation check against environmental requirements.
	 *
	 * @since  1.0.0
	 * @param  string $fqcn The targeted element class name context string.
	 * @return bool         True if allowed to boot, false otherwise.
	 */
	private function is_launchable( string $fqcn ): bool {
		if ( ! $this->has_launchable_orientation( $fqcn ) ) {
			return false;
		}
		return $this->environment_is_ready( $fqcn );
	}

	/**
	 * Verifies if the class matches layout presentation configuration context tiers.
	 *
	 * @since  1.0.0
	 * @param  string $fqcn Target class execution string.
	 * @return bool         True if cleared, false otherwise.
	 */
	private function has_launchable_orientation( string $fqcn ): bool {
		$fqcn_orientation = $fqcn::get_orientation();
		return $this->current_orientation === $fqcn_orientation || Orientation::BOTH === $fqcn_orientation;
	}

	/**
	 * Resolves structural dependencies parameters against the in-memory evaluator service layer.
	 *
	 * @since  1.0.0
	 * @param  string $fqcn Target class execution string.
	 * @return bool         True if configuration metrics pass, false otherwise.
	 */
	private function environment_is_ready( string $fqcn ): bool {
		$dependencies = $fqcn::get_dependencies();
		if ( empty( $dependencies ) ) {
			return true;
		}
		return $this->environment->is_ready( $dependencies );
	}

	/**
	 * Schedules a root module class name string for authorization and initialization.
	 *
	 * @since  1.0.0
	 * @param  string $module_fqcn The Fully Qualified Class Name string of the target module.
	 * @return bool                True if scheduled successfully, false on timing block violations.
	 */
	private function schedule_module_launch( string $module_fqcn ): bool {
		if ( $this->timing_violation( $module_fqcn ) ) {
			return false;
		}

		if ( empty( $this->get_instance( $module_fqcn ) ) ) {
			$this->set_instance( $module_fqcn, new $module_fqcn( $this->plugin ) );
		}
		return true;
	}

	/**
	 * Pushes sub-features systematically into an initialization loading queue array.
	 *
	 * @since  1.0.0
	 * @param  array  $feature_class_names Array containing micro-feature target class strings.
	 * @param  string $module_class_name   The Fully Qualified Class Name string of the parent module host.
	 * @return bool                        True if components successfully registered to stack, false on timing gaps.
	 */
	public function schedule_features_launch( array $feature_class_names, string $module_class_name ): bool {
		if ( $this->timing_violation( $module_class_name ) ) {
			return false;
		}

		foreach ( $feature_class_names as $feature_class_name ) {
			$this->features[ $module_class_name ][] = $feature_class_name;
		}

		return isset( $this->features[ $module_class_name ] );
	}

	/**
	 * Iterates and flushes the saved micro-features queues stack immediately once modules process completes.
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public function all_modules_are_scheduled(): void {
		foreach ( $this->features as $module_class_name => $feature_class_names ) {
			$this->schedule_features_launch_from_queue( $feature_class_names, $module_class_name );
		}
	}

	/**
	 * Instantiates features and ties them structurally with their preflight-checked parent module instance shell.
	 *
	 * @since  1.0.0
	 * @param  array  $feature_class_names Array of features class strings.
	 * @param  string $module_class_name   Parent module host class target.
	 * @return bool                        True if initialization sequence runs clean, false otherwise.
	 */
	public function schedule_features_launch_from_queue( array $feature_class_names, string $module_class_name ): bool {
		if ( $this->timing_violation( $module_class_name ) ) {
			return false;
		}

		$valid_features = true;
		foreach ( $feature_class_names as $feature_fqcn ) {
			$module_instance = $this->get_instance( $module_class_name );

			if ( empty( $module_instance ) ) {
				$valid_features = false;
				continue;
			}

			if ( empty( $this->get_instance( $feature_fqcn ) ) ) {
				$this->set_instance( $feature_fqcn, new $feature_fqcn( $module_instance, $this->plugin ) );
			}
		}

		return $valid_features;
	}

	/**
	 * Guard to signal schedule requests that occur too late.
	 *
	 * @since  1.0.0
	 * @param  string $class_name The full classname string.
	 * @return bool               True if a timing violation occurred, false otherwise.
	 */
	private function timing_violation( string $fqcn ): bool {
		if ( $this->did_launch || doing_action( $this->launch_hook ) || did_action( $this->launch_hook ) ) {
			_doing_it_wrong(
				__METHOD__,
				sprintf(
					'Lifecycle violation: Component "%1$s" called kernel launch request on "%2$s". Launch requests should be done before the "%3$s" hook fires.',
					esc_html( $fqcn ),
					current_filter(),
					$this->launch_hook
				),
				'1.0.0'
			);
			return true;
		}
		return false;
	}

	/**
	 * Secure public entry to retrieve an active live object instance.
	 *
	 * @since  1.0.0
	 * @param  string $class_name The targeted Fully Qualified Class Name target string.
	 * @return object|null        The live operational memory object, or null if unhydrated.
	 */
	public function get_instance( string $fqcn ): ?object {
		return $this->instances[ $fqcn ] ?? null;
	}

	/**
	 * Caches an operational object instance into the framework memory array pool.
	 *
	 * @since  1.0.0
	 * @param  string $class_name The targeted Fully Qualified Class Name target string.
	 * @param  object $object     The instantiated Module or Feature object context.
	 * @return void
	 */
	public function set_instance( string $fqcn, object $object ): void {
		$this->instances[ $fqcn ] = $object;
	}

	/**
	 * Finalizes the tracking cycle and safely executes all authorized boot sequences.
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public function launch(): void {
		if ( $this->did_launch || $this->launch_timing_violation() ) {
			return;
		}

		$this->instances = $this->interdependency_guard->clean_missing_interdependencies( $this->instances );

		foreach ( $this->instances as $instance ) {
			if ( method_exists( $instance, 'launch' ) ) {
				$instance->launch();
			}
		}

		$this->plugin->launch();
		$this->launch_finished();
	}

	/**
	 * Checks if the current priority of a callback equals a given value.
	 *
	 * @since  1.0.0
	 * @param  int $prio The priority value to check against.
	 * @return bool      True if the current priority matches, false otherwise.
	 */
	private function doing_priority( int $prio ): bool {
		return $prio === $this->current_priority();
	}

	/**
	 * Check if launch runs on intended hook with intended priority.
	 *
	 * @since  1.0.0
	 * @return bool True if launch runs on incorrect timing and/or priority, false otherwise.
	 */
	private function launch_timing_violation(): bool {
		return ! ( doing_action( $this->launch_hook ) && $this->doing_priority( $this->launch_priority ) );
	}

	/**
	 * Returns the priority of the filter that called the current callback.
	 *
	 * @since  1.0.0
	 * @return int The active filter priority layer.
	 */
	private function current_priority(): int {
		global $wp_filter;
		global $wp_current_filter;

		$current_filter_name = end( $wp_current_filter );
		$current_filter      = $wp_filter[ $current_filter_name ];

		return $current_filter->current_priority();
	}

	/**
	 * After launching the modules and features, also start launching the plugin
	 * and tell the world that the plugin is ready for normal service!
	 *
	 * @since  1.0.0
	 * @return void
	 */
	private function launch_finished(): void {
		if ( $this->did_launch ) {
			return;
		}

		$this->did_launch = true;
		$this->plugin->set_ready();

		do_action( 'dwp_cf_ready' );
	}

	/**
	 * Reload and refresh components register cache after a rescan is performed.
	 *
	 * @since  1.0.0
	 * @return array<int, string> List of fully qualified module class names.
	 */
	public function reload_modules_register(): array {
		$this->components_register_cache = null;
		return $this->load_components_register();
	}

	/**
	 * Loads the topography matrix from disk via the centralized finder service.
	 *
	 * @since  1.0.0
	 * @return array<string, array<int, string>> The evaluated module topography list.
	 */
	private function load_components_register(): array {
		if ( null !== $this->components_register_cache ) {
			return $this->components_register_cache;
		}

		$this->components_register_cache = $this->components_register->get();

		if ( ! is_array( $this->components_register_cache ) ) {
			$this->components_register_cache = array();
		}

		return $this->components_register_cache;
	}

	/**
	 * Guard to signal that a feature tried to schedule launch before the module was scheduled.
	 *
	 * @since  1.0.0
	 * @param  string $module_class_name The full classname string of a module.
	 * @return bool                      True if a violation occurred, false otherwise.
	 */
	private function module_not_scheduled_violation( string $module_class_name ): bool {
		if ( empty( $this->get_instance( $module_class_name ) ) ) {
			_doing_it_wrong(
				__METHOD__,
				sprintf(
					'Launch request violation: Parent module "%s" must be scheduled for launch before scheduling the module features.',
					esc_html( $module_class_name )
				),
				'1.0.0'
			);
			return true;
		}
		return false;
	}

	/**
	 * Guard to signal invalid module classnames being passed to the scheduler.
	 *
	 * @since  1.0.0
	 * @param  string $module_class_name The full classname of a module.
	 * @return bool                      True if a class violation occurred, false otherwise.
	 */
	private function module_class_violation( string $module_class_name ): bool {
		if ( ! class_exists( $module_class_name ) || ! is_subclass_of( $module_class_name, ModuleInterface::class ) ) {
			_doing_it_wrong(
				__METHOD__,
				sprintf( 'Launch request violation: Class "%s" does not exist or fails to implement ModuleInterface.', esc_html( $module_class_name ) ),
				'1.0.0'
			);
			return true;
		}
		return false;
	}

	/**
	 * Guard to signal invalid feature classnames being passed to the scheduler.
	 *
	 * @since  1.0.0
	 * @param  string $feature_class_name The full classname of a feature.
	 * @return bool                       True if a class violation occurred, false otherwise.
	 */
	private function feature_class_violation( string $feature_class_name ): bool {
		if ( ! class_exists( $feature_class_name ) || ! is_subclass_of( $feature_class_name, FeatureInterface::class ) ) {
			_doing_it_wrong(
				__METHOD__,
				sprintf( 'Launch request violation: Class "%s" does not exist or fails to implement FeatureInterface.', esc_html( $feature_class_name ) ),
				'1.0.0'
			);
			return true;
		}
		return false;
	}

	/**
	 * Guard to signal that a module class name violates strict location conventions.
	 *
	 * @since  1.0.0
	 * @param  string $module_class_name The full classname string of a module.
	 * @return bool                      True if a location violation occurred, false otherwise.
	 */
	private function invalid_module_location( string $module_class_name ): bool {
		$required_prefix = 'DeWittePrins\\CoreFunctionality\\Modules\\';
		if ( ! str_starts_with( $module_class_name, $required_prefix ) ) {
			_doing_it_wrong(
				__METHOD__,
				sprintf( 'Architectural violation: Module "%s" namespace path is positioned outside the authorized Modules directory boundary.', esc_html( $module_class_name ) ),
				'1.0.0'
			);
			return true;
		}

		$suffix = str_replace( $required_prefix, '', $module_class_name );
		$parts  = explode( '\\', $suffix );

		if ( count( $parts ) !== 2 ) {
			_doing_it_wrong(
				__METHOD__,
				sprintf( 'Architectural violation: Module "%s" cannot be launched because it resides in an unauthorized deep subfolder layer.', esc_html( $module_class_name ) ),
				'1.0.0'
			);
			return true;
		}

		return false;
	}

	/**
	 * Guard: Is the feature on the expected location according to namespace?
	 *
	 * Since the plugin autoloads through strict spr-4 autoload (composer autoload),
	 * the namespace maps directly to the location of the class-file.
	 *
	 * @since  1.0.0
	 * @param  string $feature_class_name The Fully Qualified Class Name string of the feature.
	 * @param  string $module_class_name  The Fully Qualified Class Name string of the parent module host.
	 * @return bool                       True if a location violation occurred, false otherwise.
	 */
	private function invalid_feature_location( string $feature_class_name, string $module_class_name ): bool {

		// Features should live in a sub-drectory Features of their parent Module in a sub-directory with the same name as the class slug.
		$feature_class_slug  = ClassParser::fqcn_remove_namespace( $feature_class_name );
		$expected_namespace  = ClassParser::namespace_of_fqcn( $module_class_name ) . '\\Features\\' . $feature_class_slug;
		$expected_class_name = $expected_namespace . '\\' . $feature_class_slug;

		if ( $feature_class_name !== $expected_class_name ) {
			_doing_it_wrong(
				__METHOD__,
				sprintf(
					'Architectural violation: Feature "%1$s" namespace mismatch. It must reside strictly inside the "%2$s" context subfolder.',
					esc_html( $feature_class_name ),
					esc_html( $expected_namespace )
				),
				'1.0.0'
			);
			return true;
		}

		return false;
	}
}





