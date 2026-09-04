<?php
/**
 * Admin Toolbar Management Module.
 *
 * @package DeWittePrins\CoreFunctionality\Modules\AdminToolbar
 * @since   1.0.0
 */

namespace DeWittePrins\CoreFunctionality\Modules\AdminToolbar;

use DeWittePrins\CoreFunctionality\Plugin;
use DeWittePrins\CoreFunctionality\Core;
use DeWittePrins\CoreFunctionality\Contracts\ModuleInterface;
use DeWittePrins\CoreFunctionality\Contracts\FeatureInterface;
use DeWittePrins\CoreFunctionality\Data\ModuleConfigSchema;
use DeWittePrins\CoreFunctionality\Modules\AdminToolbar\Features\AdminToolbarBuilder;
use DeWittePrins\CoreFunctionality\Modules\AdminToolbar\Features\AdminToolbarCleanup;
use DeWittePrins\CoreFunctionality\Modules\AdminToolbar\Features\AdminToolbarVisualizer;
use DeWittePrins\CoreFunctionality\Modules\AdminToolbar\Features\FixAdminBarStyles;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class AdminToolbar
 *
 * Orchestrates layout adjustments, responsive flexbox fixes, and node configurations.
 */
class AdminToolbar implements ModuleInterface {

	/**
	 * Central plugin context.
	 *
	 * @var Plugin
	 */
	private Plugin $plugin;

	/**
	 * Active features collection bound to this module.
	 *
	 * @var array
	 */
	private array $features = array();

	/**
	 * Unique identifier token for this module.
	 *
	 * @return string The unique criteria key.
	 */
	public static function get_id(): string {
		return 'admin_toolbar';
	}

	/**
	 * Retrieves the human-readable name of the module.
	 *
	 * @since  1.0.0
	 * @return string Module title.
	 */
	public static function get_name(): string {
		return 'Admin Toolbar';
	}

	/**
	 * Retrieves the contextual description of what the module provides.
	 *
	 * @since  1.0.0
	 * @return string Module description.
	 */
	public static function get_description(): string {
		return 'Deze module maakt o.a. een admin toolbar menu aan.';
	}

	/**
	 * Gathers the fully qualified class names of encapsulated micro-features.
	 *
	 * @return array List of fully qualified feature class strings.
	 */
	public static function get_features(): array {
		return array(
			Features\AdminToolbarBuilder::class,
			Features\AdminToolbarCleanup::class,
			Features\AdminToolbarVisualizer::class,
			Features\FixAdminBarStyles::class,
		);
	}

	/**
	 * Returns the baseline preflight verification environment checkpoints.
	 *
	 * @return array Multi-dimensional array tracking infrastructure dependencies.
	 */
	public static function get_preflight_checks(): array {
		return array(
			'and' => array( 'wordpress_core' ),
		);
	}

	/**
	 * Defines core permission roles or capabilities needed to launch.
	 *
	 * @since  1.0.0
	 * @return array List of required WordPress capabilities.
	 */
	public static function get_required_capabilities(): array {
		return array( 'manage_options' );
	}

	/**
	 * AdminToolbar constructor.
	 *
	 * @param Plugin $plugin The global core plugin metadata shell.
	 */
	public function __construct( Plugin $plugin ) {
		$this->plugin = $plugin;

		// Volledig gekwalificeerd om compiler-frictie met de Contracts namespace te voorkomen.
		$this->features = array(
			AdminToolbarBuilder::class,
			AdminToolbarCleanup::class,
			AdminToolbarVisualizer::class,
			FixAdminBarStyles::class,
		);
	}

	/**
	 * Gets the global plugin core engine instance.
	 * LOEPZUIVER: Lost de fatal error in de AdminToolbarBuilder direct op! 🎯
	 *
	 * @return Plugin The central verkeersleider context.
	 */
	public function get_plugin(): Plugin {
		return $this->plugin;
	}

	/**
	 * Launches the module internal feature cascades.
	 *
	 * @return void
	 */
	public function launch(): void {
	}

	/**
	 * Defines the settings schema schema mapping for this specific module layer.
	 *
	 * @since  4.0.0
	 * @return ModuleConfigSchema Compiled schema mapping object.
	 */
	public static function get_config_schema(): ModuleConfigSchema {
		/**
		 * 2. Definieer de exacte configuratie-eisen van deze specifieke module.
		 */
		$schema = new ModuleConfigSchema(
			self::get_id(),
			self::get_name(),
			self::get_description()
		);
		// De module voegt zijn eigen specifieke configuratiebehoeften toe.
		$schema->add_field( 'log_retention_days', 'number', 'Aantal dagen logs bewaren', 14 )
			->add_field( 'slack_webhook_url', 'text', 'Slack Webhook URL voor notificaties', '' );

		return $schema;
	}
}
