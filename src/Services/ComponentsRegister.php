<?php
/**
 * File containing the ComponentsRegister class.
 *
 * @package DeWittePrins\CoreFunctionality\Services
 * @author  Hans Schuijff <@hansschuijff>
 * @license GPL-2.0
 * @since   1.0.0
 */

namespace DeWittePrins\CoreFunctionality\Services;

use DeWittePrins\CoreFunctionality\Enums\Orientation;
use DeWittePrins\CoreFunctionality\Enums\Deployment;
use DeWittePrins\CoreFunctionality\Enums\Register;
use DeWittePrins\CoreFunctionality\Enums\RegisterSetting;
use DeWittePrins\CoreFunctionality\ValueObjects\ModuleClassValidator;
use DeWittePrins\CoreFunctionality\Services\FileSystem\FinderService;
use DeWittePrins\CoreFunctionality\Interfaces\ModuleInterface;
use DeWittePrins\CoreFunctionality\Plugin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Service responsible for scanning the file system to locate autonomous features.
 *
 * Handles both active memory loading and disk directory iterations.
 *
 * @since 1.0.0
 */
class ComponentsRegister {

	/**
	 * The current Deployment environment (Deployment::development, Deployment::staging of Deployment::production).
	 *
	 * @var Deployment
	 */
	private Deployment $current_deployment;

	/**
	 * The current orientation (Orientation::ADMIN of Orientation::FRONTEND).
	 *
	 * @var Orientation
	 */
	private Orientation $current_orientation;

	/**
	 * The base namespace for modules.
	 *
	 * @var string
	 */
	private string $modules_namespace;

	/**
	 * Registry of all enabled modules and features (FQCN => true).
	 *
	 * @var array<string, bool>|null
	 */
	public ?array $enabled_components = null;

	/**
	 * Raw structural registry blueprint of all found disk modules and features.
	 *
	 * @var array<string, array>|null
	 */
	private ?array $components_register = null;

	/**
	 * Registry tracking the targeted deployment environments for components.
	 *
	 * @var array<string, string>|null
	 */
	private ?array $components_deployment = null;

	/**
	 * Registry of modules and features with a frontend orientation.
	 *
	 * @var ModuleClassValidator[]|null
	 */
	private ?array $frontend_register = null;

	/**
	 * Registry of modules and features with an admin orientation.
	 *
	 * @var ModuleClassValidator[]|null
	 */
	private ?array $admin_register = null;

	/**
	 * Default deployment Enum.
	 *
	 * @var Deployment
	 */
	private Deployment $default_deployment = Deployment::DEVELOPMENT;

	/**
	 * ComponentsRegister Constructor.
	 *
	 * Hydrates all framework register matrices seamlessly using the unified
	 * Settings Option Vault pipeline, falling back dynamically to disk blueprints.
	 *
	 * @since 1.0.0
	 * @param Plugin $plugin Central framework orchestrator core instance.
	 */
	public function __construct(
		private readonly Plugin $plugin,
	) {
		$this->modules_namespace     = $this->plugin->get_data( 'modules-namespace' );
		$this->current_deployment    = $this->plugin->get_deployment();
		$this->current_orientation   = $this->plugin->get_orientation();

		// FIX: Added $this-> prefix to ensure correct method routing during object construction.
		$this->enabled_components    = $this->get_register( Register::ENABLED );
		$this->components_register   = $this->get_register( Register::COMPONENTS );
		$this->components_deployment = $this->get_register( Register::DEPLOYMENT );
	}

	/**
	 * Gets the register of modules and features of this plugin.
	 *
	 * In this method the components register is filtered to give only launchable
	 * modules and features, meaning:
	 * 1. Is enabled.
	 * 2. The target environment (frontend, admin) matches the current environment.
	 * 3. The deployment of the component allows launching on the current server environment.
	 *
	 * If any other register is given in de arguments, then it is returned unfiltered.
	 *
	 * @since  1.0.0
	 *
	 * @return array<string, array> The filtered array of module FQCNs with their verified feature FQCNs.
	 */
	public function get( Register $register = Register::COMPONENTS ): array {
		$data = $this->get_register( $register );
		if ( $register === Register::COMPONENTS ) {
			return $this->filter( $data );
		}

		return $data;
	}

	/**
	 * Initializes and eager-loads a specific register from the database options vault,
	 * falling back dynamically to filesystem declarative snapshot files.
	 *
	 * @since  1.0.0
	 * @param  Register $register Target architectural registration matrix key identifier.
	 * @return array              The cached configuration array payload, or a clean fallback array.
	 */
	public function get_register( Register $register ): array {

		$settings_key = $this->get_register_setting_key( $register );
		if ( ! $settings_key ) {
			return array();
		}

		// 1. Primary Strategy: Extract runtime data from our unified Option Vault service layer.
		$data = $this->plugin->settings->get( $settings_key, default: null );

		if ( null !== $data && is_array( $data ) ) {
			return $data;
		}

		// 2. Secondary Strategy: Fallback to reading declarative layout schemas from disk files.
		$data = $this->plugin->file_service->get_register( $register );
		if ( ! is_array( $data ) ) {
			$data = array();
		}

		// 3. Sync Strategy: Commit disk blueprints into the Option Vault cache for instant sub-millisecond execution next time.
		if ( ! empty( $data ) ) {
			$this->plugin->settings->save( $settings_key, $data );
		}

		return $data;
	}

	/**
	 * Translates structural Register components into their corresponding RegisterSetting option storage keys.
	 *
	 * @since  1.0.0
	 * @param  Register $register Target registration layer identifier.
	 * @return string|false       The resolved database setting storage key string, false on configuration gaps.
	 */
	private function get_register_setting_key( Register $register ): string|false {
		return match ( $register ) {
			Register::COMPONENTS => RegisterSetting::COMPONENTS->value,
			Register::ENABLED    => RegisterSetting::ENABLED->value,
			Register::DEPLOYMENT => RegisterSetting::DEPLOYMENT->value,
			Register::FRONTEND   => RegisterSetting::FRONTEND->value,
			Register::ADMIN      => RegisterSetting::ADMIN->value,

			// Failsafe: Prevent UnhandledMatchError crashes if an unmapped Enum case passes through.
			default              => false,
		};
	}

	/**
	 * Gets the register of features for a given module.
	 *
	 * This method is meant for use when you need the filtered list
	 * (launchable in current environment).
	 *
	 * @since  1.0.0
	 * @param string $module The Classname slug, or Fully Qualified Class Name, of a module.
	 * @return array<string, array> The filtered array of verified feature FQCNs for the module.
	 */
	public function get_module_features(  string $module ): array {
		// Validation and preparations.
		if ( empty( $module ) ) {
			return array();
		}
		$module_fqcn = $this->get_module_fqcn( $module );
		if ( ! $module_fqcn ) {
			return array();
		}

		// Get the unfiltered features.
		$module_features_fcqn = $this->get_module_features_unfiltered( $module_fqcn );
		if ( empty ( $module_features_fcqn ) ) {
			return $module_features_fcqn;
		}

		// Filter the output before returning it.
		$component[ $module_fqcn ] = $module_features_fcqn;
		$component                 = $this->filter( $component );

		return isset( $component[ $module_fqcn ] ) ? $component[ $module_fqcn ] : array();
	}

	/**
	 * Gets the register of features for a given module.
	 *
	 * @since  1.0.0
	 * @param string $module The Classname slug, or Fully Qualified Class Name, of a module.
	 * @return array<int, string> The complete unfiltered array of verified feature FQCNs for the module.
	 */
	public function get_module_features_unfiltered(  string $module ): array {
		if ( empty( $module ) ) {
			return array();
		}

		if ( null === $this->components_register ) {
			$this->components_register = $this->get_components_register();
		}

		$module_fqcn = $this->get_module_fqcn( $module );
		if ( ! $module_fqcn ) {
			return array();
		}

		$module_features_fcqn = isset( $this->components_register[ $module_fqcn ] ) ? $this->components_register[ $module_fqcn ] : array();

		return $module_features_fcqn;
	}

	/**
	 * Gets only the modules (without Features) from the filtered components register.
	 *
	 * @since  1.0.0
	 * @return array<int, string> Filtered list of all registered modules fully qualified modules class names.
	 */
	public function get_modules(): array {
		$components_register = $this->get_register( Register::COMPONENTS );
		if ( empty ( $components_register) ) {
			return array();
		}
		return array_keys( $components_register ) ?? array();
	}

	/**
	 * Geta only the modules (without Features) from the unfiltered components register.
	 *
	 * @since  1.0.0
	 * @return array<int, string> List of all registered modules fully qualified modules class names.
	 */
	public function get_modules_unfiltered(): array {
		$components_register = $this->get_register( Register::COMPONENTS );
		if ( empty ( $components_register) ) {
			return array();
		}
		return array_keys( $components_register ) ?? array();
	}

	/**
	 * Resolves module strings or slugs into active fully qualified class names targets.
	 *
	 * @param string $module The classname slug or Fully Qualified Classname of a module.
	 * @return string|false The validated target FQCN class string, false on mismatch.
	 */
	private function get_module_fqcn( string $module ): string|false {
		$module_fqcn = $module;
		if ( ! str_starts_with( $module, $this->modules_namespace ) ) {
			$module_fqcn = $this->trailingbackwardslashit( $this->modules_namespace ) . $module;
		}
		if ( ClassParser::is_implementation_of( $module_fqcn, ModuleInterface::class ) ) {
			return $module_fqcn;
		}
		return false;
	}

	/**
	 * Forces a physical disk rescan to compile and freeze a fresh framework configuration blueprint.
	 *
	 * This method meant only for onboarging onboarding new modules and features in a setting page.
	 *
	 * @since  1.0.0
	 * @return array<string, array> The complete actualized array of module FQCNs with their verified feature FQCNs.
	 */
	public function rebuild(): array {
		$components_register = ( new FinderService( $this->plugin ) )->find_modules_and_features();

		if ( ! $components_register ) {
			return $this->components_register;
		}

		$this->save( Register::COMPONENTS, $components_register );

		return $components_register;
	}

	/**
	 * Filters a component matrix dynamically against target environment, deployment tier,
	 * and DB activations.
	 *
	 * @since  1.0.0
	 * @param  array<string, array> $components Array of Fully Qualified Class Names of features of a module.
	 * @return array<string, array> Cleaned layout matrix containing only active and authorized targets.
	 */
	private function filter( array $components ): array {
		$components_register = array();

		foreach ($components as $module_fqcn => $features_fqcn) {

			if ( ! $this->is_enabled( $module_fqcn )
			||   ! $this->current_orientation_allowed( $module_fqcn )
			||   ! $this->current_deployment_allowed( $module_fqcn )
			) {
				continue;
			}

			$features = array();
			foreach ( $features_fqcn as $feature_fqcn ) {

				if ( ! $this->is_enabled( $feature_fqcn )
				||   ! $this->current_orientation_allowed( $feature_fqcn )
				||   ! $this->current_deployment_allowed( $feature_fqcn )
				) {
					continue;
				}

				$features[] = $feature_fqcn;
			}

			$components_register[ $module_fqcn ] = $features;
		}

		return $components_register;
	}

	/**
	 * Check if a module or feature is allowed to run on current orientation (frontend/admin).
	 *
	 * @param string $fqcn Fully Qualified Class Names of a module or feature.
	 * @return bool        True if allowed to run in current orientation, false otherwise.
	 */
	private function current_orientation_allowed( string $fqcn ): bool {

		$orientation = $fqcn::get_orientation();

		return $this->current_orientation === $orientation || Orientation::BOTH === $orientation;
	}

	/**
	 * Check if a component is authorized to execute within the active deployment tier infrastructure.
	 *
	 * @since  1.0.0
	 * @param  string $fqcn Fully Qualified Class Name of the target element.
	 * @return bool         True if authorized, false otherwise.
	 */
	private function current_deployment_allowed( string $fqcn ): bool {

		$component_deployment = $this->get_deployment( $fqcn );

		return match( $this->current_deployment ) {

			Deployment::DEVELOPMENT => true,
			Deployment::STAGING     => in_array( $component_deployment, array( Deployment::STAGING->value, Deployment::PRODUCTION->value ), true ),
			Deployment::PRODUCTION  => ( $component_deployment === Deployment::PRODUCTION->value ),
		};
	}

	/**
	 * Gets the deployment status of a specific component.
	 *
	 * @since  1.0.0
	 * @param  string $fqcn Fully Qualified Class Name of the target component.
	 * @return string       The deployment value (development, staging, production) of the component's deployment status.
	 */
	public function get_deployment( string $fqcn ): string {
		if ( null === $this->components_deployment || ! is_array( $this->components_deployment ) ) {
			$this->components_deployment = $this->get_register( Register::DEPLOYMENT );
		}

		return array_key_exists( $fqcn, $this->components_deployment )
			? $this->components_deployment[ $fqcn ]
			: $this->default_deployment->value;
	}

	/**
	 * Check if a module or feature is enabled to launch.
	 *
	 * @since  1.0.0
	 * @param  string $fqcn Fully Qualified Class Name of a module or feature.
	 * @return bool         True if enabled, false otherwise.
	 */
	public function is_enabled( string $fqcn ): bool {
		if ( null === $this->enabled_components || ! is_array( $this->enabled_components ) ) {
			$this->enabled_components = $this->get_register( Register::ENABLED );
		}

		return array_key_exists( $fqcn, $this->enabled_components );
	}

	/**
	 * Gets the component register runtime cache or hydrates the module memory registry
	 * either from compiled cache or via disk iteration.
	 *
	 * @since  1.0.0
	 * @return array A complete and validated register of all modules and features in this plugin.
	 */
	private function get_components_register(): array {

		if ( ! empty( $this->components_register ) ) {
			return $this->components_register;
		}

		// fill the runtime cache from component-register settings or file
		$this->components_register = $this->get_register( Register::COMPONENTS );

		// Still empty after reading the components register file?
		if ( empty( $this->components_register ) ) {

			// The components register file must be empty or not found.
			$this->plugin->notices->add( 'error', __('Components register file was empty or not found. Please inform support.') );

			// if component-register file was empty, try to rebuild the cache by scanning files.
			$this->components_register = $this->rebuild();

			if ( empty( $this->components_register ) ) {
				$this->plugin->notices->add( 'error', __('Something went wrong rebuilding the components registry. No component was found. Please inform support!') );
			}
		}

		return is_array( $this->components_register ) ? $this->components_register : array();
	}

	/**
	 * Serializes and commits register changes seamlessly toward
	 * both database options and physical files.
	 *
	 * @since  1.0.0
	 * @param  Register $register      Enum tracking the targeted architectural register.
	 * @param  array    $register_data The structured payload data matrix tree to persist.
	 * @return string|bool             False if both settings and file where written unsuccesfull, otherwise result of file save.
	 */
	public function save( Register $register, array $register_data ): string|bool {
		if ( empty( $register_data ) ) {
			return true;
		}

		$settings_key = $this->get_register_setting_key( $register );

		// Perform specific data restructuring schemas if the target is
		// the administrative activation list.
		if ( $register === Register::ENABLED ) {
			$register_data = $this->validate_and_restructure_enabled_list( $register_data );
		}

		$settings_written = $this->plugin->settings->save( $settings_key, $register_data );

		// Commit directly toward the physical file blueprints (The Git Source of Truth).
		$file_written = $this->plugin->file_service->save_register( $register, $register_data );

		// Flush and refresh internal runtime properties instantly to prevent lifecycle lag.
		$this->cache_refresh( $register, $register_data );

		if ( false === $settings_written && false === $file_written ) {
			return false;
		}
		return $file_written;
	}

	/**
	 * Reforms and validates flat incoming form arrays into a high-performance hash layout (FQCN => true).
	 *
 	 * Ensures lookups via array_key_exists execute at instant O(1) performance speeds.
	 *
	 * @since  1.0.0
	 * @param  array<int, string> $form_enabled_components Flat array payload matching submitted checkboxes.
	 * @return array<string, true>                         The array of enabled components, with fqcn as key-value.
	 */
	public function validate_and_restructure_enabled_list( array $form_enabled_components ): array {

		// Just in case the array is already restructured dynamically during an upstream request lifecycle.
		if ( ! is_int( array_key_first( $form_enabled_components ) ) ) {
			return $form_enabled_components;
		}

		$components_register = $this->get_register( Register::COMPONENTS );
		$enabled_components  = array();

		foreach ( $components_register as $module_fqcn => $features_fqcn ) {

			// If the module itself was checked/enabled in the form, add its full FQCN string.
			if ( ! in_array( $module_fqcn,  $form_enabled_components, true ) ) {
				// If a parent module is disabled, all nested sub-features are implicitly disabled too.
				continue;
			}

			$enabled_components[ $module_fqcn ] = true;

			if ( ! is_array( $features_fqcn ) ) {
				continue;
			}

			// Validate and loop through nested sub-features under the authorized parent shield.
			foreach ( $features_fqcn as $feature_fqcn ) {
				if ( ! in_array( $feature_fqcn, $form_enabled_components, true ) ) {
					continue;
				}
				$enabled_components[ $feature_fqcn ] = true;
			}
		}

		return $enabled_components;
	}

	private function cache_refresh( Register $register, array $register_data ): void {

		// Hydrate the matching in-memory cache properties immediately upon a successful write operation.
		match ( $register ) {
			Register::COMPONENTS => $this->components_register   = $register_data,
			Register::ENABLED    => $this->enabled_components    = $register_data,
			Register::DEPLOYMENT => $this->components_deployment = $register_data,
			Register::FRONTEND   => $this->frontend_register     = $register_data,
			Register::ADMIN      => $this->admin_register        = $register_data,

			// Failsafe: Prevent UnhandledMatchError crashes if an unmapped Enum case passes through.
			default              => null,
		};
	}

    /**
	 * Perfoms some basic checks on the component register.
	 * - classes exist
	 * - implement the correct Interfaces
	 *
	 * @since  1.0.0
	 * @return string[] $components_register Fully Qualified Class Names of components and features in the register.
	 * @return string[]                      Filtered verion of the component register, removing all fqcn's with incorrect Interfaces or non existing classes.
	 */
	private function validate( array $components_register ): array {

		$new_register = array();

		foreach ( $components_register as $module_fqcn => $features_fqcn ) {

			$feature_slugs = $this->features_fqcn_to_slug( $features_fqcn );

			/**
			 * Use the ModuleClassValidator Value Object to validate the module
			 * and its features.
			 */
			$features = ModuleClassValidator::validate( $module_fqcn, $feature_slugs, $this->modules_namespace );

			// Did any features pass the validations?
			if ( false !== $features ) {
				$new_register[ $module_fqcn ] = $features;
			}
		}

		return $new_register;
	}

	/**
	 * Remove the namespaces from a flat array containing fqcns
	 *
	 * @param array $fqcns A flat array of Fully Qualified Class Names.
	 * @return array       The input, but without namespaces.
	 */
	private function features_fqcn_to_slug( array $fqcns ): array {
		// From each array item, keep the part after the last slash and remove the rest.
		return array_map( function( $fqcn ) {
				return basename( str_replace( '\\', '/', $fqcn ) );
			}, $fqcns );
	}

	/**
	 * Make sure the input string ends with a forward slash.
	 *
	 * @param string $path
	 * @return string
	 */
	private function trailingslashit( string $path ): string {
		return $this->untrailingslashit( $path ) . '/';
	}

	/**
	 * Makes sure a namespace string ends with a double backward slash.
	 *
	 * @param string $namespace A string representing a namespace.
	 * @return string $namespace with a single double slash at the end.
	 */
	private function trailingbackwardslashit( string $namespace ): string {
		return $this->untrailingslashit( $this->untrailingslashit( $namespace ) ) . '\\';
	}

	/**
	 * Trims trailing forward- and backward slash from path.
	 *
	 * @param string $path
	 * @return string
	 */
	private function untrailingslashit( string $path ): string {
		return rtrim( $path, '/\\' );
	}
}
