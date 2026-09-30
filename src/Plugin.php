<?php
/**
 * Main Plugin Orchestrator Core.
 *
 * @package DeWittePrins\CoreFunctionality
 * @since   1.0.0
 */

namespace DeWittePrins\CoreFunctionality;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use DeWittePrins\CoreFunctionality\Kernel;
use DeWittePrins\CoreFunctionality\Environment\Environment;
use DeWittePrins\CoreFunctionality\Environment\EnvironmentLookup;
use DeWittePrins\CoreFunctionality\Services\PluginIntegrityWatch;
use DeWittePrins\CoreFunctionality\Notifiers\Notices;
use DeWittePrins\CoreFunctionality\Services\InterDependenciesGuard;
use DeWittePrins\CoreFunctionality\Services\FileSystem\FileService;
use DeWittePrins\CoreFunctionality\Services\Loaders\ModuleLoader;
use DeWittePrins\CoreFunctionality\Services\Settings;
use DeWittePrins\CoreFunctionality\Diagnostics\Logger;
use DeWittePrins\CoreFunctionality\Admin\Services\Settings\AdminSettingsPage;
use DeWittePrins\CoreFunctionality\Admin\Services\Settings\Sections\FeatureActivationSettings;
use DeWittePrins\CoreFunctionality\Admin\Services\Settings\Sections\PushNoticeSettings;
use DeWittePrins\CoreFunctionality\Admin\Services\Settings\Sections\DeploymentSettings;
use DeWittePrins\CoreFunctionality\Admin\Services\Settings\Sections\OptionsManagementSettings;
use DeWittePrins\CoreFunctionality\Services\ComponentsRegister;
use DeWittePrins\CoreFunctionality\Enums\Deployment;
use DeWittePrins\CoreFunctionality\Enums\Orientation;
use Throwable;

use function add_action;
use function call_user_func;
use function is_admin;
use function trailingslashit;
use function plugin_dir_path;
use function get_plugin_data;
use function plugin_dir_url;
use function error_log;
use function esc_html;
use function esc_html_e;

/**
 * Class Plugin
 *
 * The main shell, centralized dependency injector container, and state tracker
 * for the entire application ecosystem.
 *
 * @since 1.0.0
 */
class Plugin {

	/**
	 * Central execution authorization watchdog data layers.
	 *
	 * @var Kernel
	 */
	public Kernel $kernel;

	/**
	 * Universal administrative notifications registry.
	 *
	 * @var Notices
	 */
	public Notices $notices;

	/**
	 * Runtime module and feature lifecycle executor.
	 *
	 * @var ModuleLoader
	 */
	private ModuleLoader $module_loader;

	/**
	 * Database settings layer storage service (Protected Shroud).
	 *
	 * @var Settings
	 */
	public Settings $settings;

	/**
	 * FileService for Component Register Files.
	 *
	 * @var FileService
	 */
	public FileService $file_service;

	/**
	 * Component Register Service.
	 *
	 * @var ComponentsRegister
	 */
	public ComponentsRegister $components_register;

	/**
	 * The current orientation of the website (admin or frontend).
	 *
	 * @var Orientation
	 */
	public Orientation $current_orientation;

	/**
	 * The Deployment status of the current environment
	 * (development/staging/production).
	 *
	 * @var Deployment
	 */
	public Deployment $current_deployment;

	/**
	 * Configuration array storing baseline directory paths and versioning metadata.
	 *
	 * @var array<string, string>
	 */
	private array $plugin_data = array();

	/**
	 * Global configuration register for option keys.
	 *
	 * @var array<string, string>
	 */
	private array $option_keys = array();

	/**
	 * Switch indicating if all core components have finished booting.
	 *
	 * @var bool
	 */
	private bool $is_ready = false;

	/**
	 * Plugin Constructor.
	 *
	 * @since 1.0.0
	 * @param string $root_file Absolute path to the main plugin bootstrap file.
	 */
	public function __construct( string $root_file ) {

		if ( ! function_exists( 'get_plugin_data' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$this->set_plugin_data( $root_file );
		$this->set_option_keys( $root_file );

		try {
			// 2. INITIALIZE CENTRAL PLATFORM SERVICES

			// 🎯 SCENARIO A: NORMAL SECURE BOOTSTAGE
			// Settings will crash when some files aren't found, so we use try{} to respond neatly to that.
			//
			// if it crashes everythin after this statement will not be performed and processing
			// will continue in the catch clause.
			$this->settings          = new Settings( $this->get_data( 'settings-dir' ) );

		} catch ( Throwable $e ) {
			// 🎯 SCENARIO B: EMERGENCY HALT
			// The exception was caught! We write to server log and show the notice.
			error_log( sprintf( 'CoreFunctionality Global Lockdown: %s', $e->getMessage() ) );

			$this->register_emergency_admin_notice( $e->getMessage() );

			// ◄ HARD BRAKE: De constructor stopt hier direct. Geen Kernel, geen hooks, 100% veilig!
			return;
		}

		// If settings thrown an error the method has stopped, so from here we're good.
		// These services are ONLY born when Settings successfully passed its internal constraints!
		$interdependency_checker   = new InterDependenciesGuard( new Logger() );
		$lookup                    = new EnvironmentLookup( $this );
		$this->current_deployment  = $lookup->get_current_deployment_env();
		$environment               = new Environment( $this );
		$this->file_service        = new FileService( $this );
		$this->components_register = new ComponentsRegister( $this );
		$this->kernel              = new Kernel( $this, $interdependency_checker, $environment, $this->components_register );

		add_action( 'plugins_loaded', array( $this, 'setup' ) );
	}

	/**
	 * Prepares core framework services early and plans execution timing.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function setup(): void {
		if ( is_admin() ) {
			$this->admin_setup();
		}
	}

	/**
	 * Start the adminn classes and processes.
	 *
	 * @return void
	 */
	private function admin_setup() {
		// schedule the registration of admin settings page sections.
		add_action(
			'dwp_cf_register_settings_sections',
			array( $this, 'register_settingspage_sections' )
		);

		$this->notices = new Notices();

		new AdminSettingsPage( $this );

		$watchdog = new PluginIntegrityWatch( $this );
		if ( ! $watchdog->monitor_integrity() ) {
			return;
		}
	}

	/**
	 * Fires late once the Kernel has fully dispatched and booted all micro-features.
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public function launch(): void {
		// If any other launch-related actions are needed, they can be added here.
	}

	/**
	 * Smart orchestration gateway for dependent plugins and external APIs.
	 *
	 * Immediately executes the callback if the framework kernel is already booted,
	 * or defers execution to the global 'dwp_cf_ready' broadcast event.
	 *
	 * @since  1.0.0
	 * @param  callable $callback The code or initialization logic to execute.
	 * @return void
	 */
	public function call_when_ready( callable $callback ): void {
		if ( $this->is_ready() ) {
			call_user_func( $callback );
		} else {
			add_action( 'dwp_cf_ready', $callback );
		}
	}

	/**
	 * Flip the internal initialization state switch to true.
	 *
	 * Called exclusively by the Kernel once the lifecycle boot execution has completed.
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public function set_ready(): void {
		$this->is_ready = true;
	}

	/**
	 * Public API check to verify if the plugin framework is fully ready for normal service.
	 *
	 * @since  1.0.0
	 * @return bool True if all features have safely booted, false otherwise.
	 */
	public function is_ready(): bool {
		return $this->is_ready;
	}

	/**
	 * Register the central plugins sections (to be added as tabs) in the Core Functionality Settings Page.
	 *
	 * @since  1.0.0
	 * @param  AdminSettingsPage $settings_factory The Core Functionality Settings Page that can hook settings sections.
	 * @return void
	 */
	public function register_settingspage_sections( AdminSettingsPage $settings_factory ): void {

		$settings_factory->register_section( FeatureActivationSettings::class );
		$settings_factory->register_section( PushNoticeSettings::class );
		$settings_factory->register_section( DeploymentSettings::class );
		$settings_factory->register_section( OptionsManagementSettings::class );
	}

	/**
	 * Registers a completely isolated, standalone error message on the admin screen.
	 *
	 * @since  1.0.0
	 * @param  string $error_message The technical violation details from the exception.
	 * @return void
	 */
	private function register_emergency_admin_notice( string $error_message ): void {
		add_action( 'admin_notices', function() use ( $error_message ) {
			?>
			<div class="notice notice-error">
				<p>
					<strong><?php esc_html_e( 'De Witte Prins - Core Functionality Gecrasht!', 'dwp-cf' ); ?></strong><br />
					<?php esc_html_e( 'De plugin-infrastructuur is stopgezet omdat de fysieke installatie corrupt is. De rest van de website functioneert normaal, maar alle acties van deze plugin zijn geblokkeerd.', 'dwp-cf' ); ?><br />
					<code style="display: inline-block; margin-top: 5px; background: #f6f7f7; padding: 3px 6px; border-radius: 3px;">
						<?php echo esc_html( $error_message ); ?>
					</code>
				</p>
			</div>
			<?php
		});
	}

	/**
	 * Exposed getter allowing external services to retrieve baseline path configuration metadata.
	 *
	 * @since  1.0.0
	 * @param  string $key The targeted metadata key (e.g., 'settings-dir').
	 * @return string The resolved path string, or an empty string if unconfigured.
	 */
	public function get_data( string $key ): string {
		return $this->plugin_data[ $key ] ?? '';
	}

	/**
	 * Set the base values for the plugin->get_data() requests.
	 *
	 * @since  1.0.0
	 * @param  string $root_file The absolute file path of the plugin bootstrap file.
	 * @return void
	 */
	private function set_plugin_data( string $root_file ): void {

		$base_dir = trailingslashit( plugin_dir_path( $root_file ) );

		$this->plugin_data = array(
			'version'                   => get_plugin_data( $root_file )['Version'] ?? '1.0.0',
			'plugin-url'                => plugin_dir_url( $root_file ),
			// base paths.
			'plugin-dir'                => $base_dir,
			'modules-dir'               => $base_dir . 'src/Modules/',
			// Settings directories.
			'settings-dir'              => $base_dir . 'settings/',
			'kernel-settings-dir'       => $base_dir . 'kernel/',
			'ac-settings-dir'           => $base_dir . 'kernel/',
			// Base namespaces.
			'plugin-namespace'          => 'DeWittePrins\\CoreFunctionality',
			'modules-namespace'         => 'DeWittePrins\\CoreFunctionality\\Modules',
		);
	}

	/**
	 * A getter for the keys that are used to save and retrieve runtime options.
	 *
	 * @since  1.0.0
	 * @param  string $key A string representing the requested option key (e.g., 'enabled-list').
	 * @return string The resolved option key, or an empty string if unconfigured.
	 */
	public function get_option_key( string $key ): string {
		return $this->option_keys[ $key ] ?? '';
	}

	/**
	 * Set the base values for the plugin->get_option_key() requests.
	 *
	 * @since  1.0.0
	 * @param  string $root_file The absolute file path of the plugin bootstrap file.
	 * @return void
	 */
	private function set_option_keys( string $root_file ): void {
		$this->option_keys = array(
			'enabled-components' => 'dwp_cf_kernel_whitelist',
		);
	}

	/**
	 * Retrieves the current plugin version string dynamically from the configuration payload.
	 *
	 * @since  1.0.0
	 * @return string The semantic version string (e.g., '1.0.0').
	 */
	public function version(): string {
		return (string) $this->get_data( 'version' );
	}

	/**
	 * Retrieves the active deployment tier environment framework state.
	 *
	 * Defaults strictly to production if the environment state has not been initialized.
	 *
	 * @since  1.0.0
	 * @return Deployment The active deployment environment enum case instance.
	 */
	public function get_deployment(): Deployment {
		if ( isset( $this->current_deployment ) ) {
			return $this->current_deployment;
		}

		// Default to production, since that's the most strict and secure selection gate.
		return Deployment::PRODUCTION;
	}

	/**
	 * Resolves and caches the structural orientation platform layer (ADMIN or FRONTEND).
	 *
	 * @since  1.0.0
	 * @return Orientation The resolved target execution environment layer case.
	 */
	public function get_orientation(): Orientation {
		if ( isset( $this->current_orientation ) ) {
			return $this->current_orientation;
		}

		$this->current_orientation = is_admin() ? Orientation::ADMIN : Orientation::FRONTEND;
		return $this->current_orientation;
	}
}
