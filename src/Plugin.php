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

use DeWittePrins\CoreFunctionality\Core;
use DeWittePrins\CoreFunctionality\Services\Config;
use DeWittePrins\CoreFunctionality\Services\ModuleLoader;
use DeWittePrins\CoreFunctionality\Services\AdminSettingsPage;
use DeWittePrins\CoreFunctionality\Services\EnvironmentMapper;
use DeWittePrins\CoreFunctionality\Services\PluginIntegrityWatch;
use DeWittePrins\CoreFunctionality\Services\Settings;
use DeWittePrins\CoreFunctionality\Services\Notice;
use DeWittePrins\CoreFunctionality\Services\SettingsSections\FeatureActivationSettings;

/**
 * Class Plugin
 */
class Plugin {

	/**
	 * Central configuration pipeline service instance (Read-Only Gateway).
	 *
	 * @var Config
	 */
	public Config $config;

	/**
	 * Universal administrative notifications registry.
	 *
	 * @var Notice
	 */
	public Notice $notice;

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
	private Settings $settings;

	/**
	 * Configuration array storing baseline directory paths and versioning metadata.
	 *
	 * @var array
	 */
	private array $plugin_data = array();

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

		$base_dir = plugin_dir_path( $root_file );

		$this->plugin_data = array(
			'version'    => get_plugin_data( $root_file )['Version'] ?? '1.0.0',
			'plugin-dir' => $base_dir,
			'plugin-url' => plugin_dir_url( $root_file ),
			'config-dir' => $base_dir . 'config/',
		);

		// Initialiseer de kerndiensten van het platform.
		$this->settings = new Settings();
		$this->config   = new Config( $this, $this->settings );
		$this->notice   = new Notice(); // De berichtendienst staat aan!

		add_action( 'plugins_loaded', array( $this, 'setup' ) );
	}

	/**
	 * Prepares core framework services early and plans execution timing.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function setup(): void {
		$mapper = new EnvironmentMapper( $this );
		Core::register_service( 'environment_mapper', $mapper );

		// INGEPLUGD: Schiet het hoofd-vinkjesblok in de fabriek zodra deze erom vraagt!
		add_action(
			'dwp_cf_register_settings_sections',
			function ( $settings_factory ) {
				$settings_factory->register_section( new FeatureActivationSettings( $this, $this->settings ) );
			}
		);

		if ( is_admin() ) {
			new AdminSettingsPage( $this, $this->settings );
		}

		// We geven Config én de nieuwe Notice service mee aan de waakhond!
		$watchdog = new PluginIntegrityWatch( $this->config, $this->notice );
		if ( ! $watchdog->monitor_integrity() ) {
			return; // DE MOTOR STOPT: De waakhond heeft de notice al geplaatst, wij breken geruisloos af!
		}

		$default_timing = array(
			'hook'     => 'init',
			'priority' => 10,
		);

		$filtered_timing = apply_filters( 'dwp_cf_load_timing', $default_timing );
		$hook            = $filtered_timing['hook'] ?? 'init';
		$priority        = $filtered_timing['priority'] ?? 10;

		add_action(
			$hook,
			function () {
				Core::set_cf_ready();
				$this->launch();
			},
			$priority
		);
	}

	/**
	 * Initiates runtime executions and boots administrative structures.
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public function launch(): void {
		$this->module_loader = new ModuleLoader( $this, $this->settings );
		$this->module_loader->launch();
	}

	/**
	 * Exposed getter allowing external services to retrieve baseline path configuration metadata.
	 *
	 * @since  1.0.0
	 * @param  string $key The targeted metadata key (e.g., 'config-dir').
	 * @return string The resolved path string, or an empty string if unconfigured.
	 */
	public function get_data( string $key ): string {
		return $this->plugin_data[ $key ] ?? '';
	}
}
