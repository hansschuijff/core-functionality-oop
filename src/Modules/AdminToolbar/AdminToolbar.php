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
	 * AdminToolbar constructor.
	 *
	 * @param Plugin $plugin The global core plugin metadata shell.
	 */
	public function __construct( Plugin $plugin ) {
		$this->plugin = $plugin;

		// Volledig gekwalificeerd om compiler-frictie met de Contracts namespace te voorkomen.
		$this->features = array(
			\DeWittePrins\CoreFunctionality\Modules\AdminToolbar\Features\AdminToolbarBuilder::class,
			\DeWittePrins\CoreFunctionality\Modules\AdminToolbar\Features\AdminToolbarCleanup::class,
		);
	}

	/**
	 * Unique identifier token for this module.
	 *
	 * @return string The unique criteria key.
	 */
	public static function get_id(): string {
		return 'admin_toolbar';
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
	 * Returns the baseline preflight verification environment checkpoints.
	 *
	 * @return array Multi-dimensional array tracking infrastructure dependencies.
	 */
	public function get_preflight_checks(): array {
		return array(
			'and' => array( 'wordpress_core' ),
		);
	}

	/**
	 * Gathers the fully qualified class names of encapsulated micro-features.
	 *
	 * @return array List of fully qualified feature class strings.
	 */
	public function get_features(): array {
		return $this->features;
	}

	/**
	 * Launches the module internal feature cascades.
	 *
	 * @return void
	 */
	public function launch(): void {
		foreach ( $this->get_features() as $feature_class ) {
			if ( ! class_exists( $feature_class ) ) {
				continue;
			}

			$feature = new $feature_class( $this );

			if ( ! $feature instanceof FeatureInterface ) {
				continue;
			}

			// De feature beslist autonoom via haar tri-state logic of ze aan staat!
			if ( $feature->is_active() ) {

				$feature->launch();

				Core::register_active_feature(
					$this->get_id(),
					$feature->get_id()
				);
			}
		}
	}
}
