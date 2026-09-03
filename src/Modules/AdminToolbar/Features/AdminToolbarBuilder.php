<?php
/**
 * Admin Toolbar Abstract Shortcut Builder Feature.
 *
 * @package DeWittePrins\CoreFunctionality\Modules\AdminToolbar\Features
 * @since   1.0.0
 */

namespace DeWittePrins\CoreFunctionality\Modules\AdminToolbar\Features;

use DeWittePrins\Corefunctionality\Modules\AdminToolbar\AdminToolbar;
use DeWittePrins\CoreFunctionality\Contracts\FeatureInterface;
use DeWittePrins\CoreFunctionality\Contracts\ModuleInterface;
use DeWittePrins\CoreFunctionality\Traits\OperationalStateTrait;
use DeWittePrins\CoreFunctionality\Plugin;
use DeWittePrins\CoreFunctionality\Environment;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class AdminToolbarBuilder
 *
 * Inject shortcut navigation elements using the decoupled Environment readiness matrix.
 *
 * @since 1.0.0
 */
class AdminToolbarBuilder implements FeatureInterface {

	use OperationalStateTrait;

	/**
	 * Central root plugin container object.
	 *
	 * @var \DeWittePrins\CoreFunctionality\Plugin
	 */
	private Plugin $plugin;

	/**
	 * Parent module container instance.
	 *
	 * @var \DeWittePrins\CoreFunctionality\Contracts\ModuleInterface
	 */
	private ModuleInterface $module;

	/**
	 * AdminToolbarBuilder constructor.
	 *
	 * @param \DeWittePrins\CoreFunctionality\Contracts\ModuleInterface $module Parent module context.
	 */
	public function __construct( ModuleInterface $module ) {
		$this->module = $module;
		$this->plugin = $module->get_plugin();

		// Register this feature has settings in wp-options that need to be matched.
		$this->plugin->register_config_setting( 'toolbar-add', 'dwp_toolbar_add_settings' );
	}

	/**
	 * Returns the unique identification string for this feature.
	 *
	 * @return string The unique micro-feature string key.
	 */
	public function get_id(): string {
		return 'admin_toolbar_builder';
	}

	/**
	 * Exposes the parent module instance.
	 *
	 * @return \DeWittePrins\CoreFunctionality\Contracts\ModuleInterface
	 */
	public function get_module(): ModuleInterface {
		return $this->module;
	}

	/**
	 * Launches the operational execution lifecycle for this micro-feature.
	 *
	 * @return void
	 */
	public function launch(): void {
		add_action( 'wp_before_admin_bar_render', array( $this, 'inject_toolbar_shortcuts' ), 200 );
		add_action( 'admin_bar_menu', array( $this, 'remove_appearance_node_on_front' ), 999 );
	}

	/**
	 * Processes the abstract config structure and dynamically injects nodes
	 * into the toolbar.
	 *
	 * @global \WP_Admin_Bar $wp_admin_bar Global WordPress admin bar object.
	 * @return void
	 */
	public function inject_toolbar_shortcuts(): void {
		global $wp_admin_bar;

		if ( ! is_object( $wp_admin_bar ) || ! current_user_can( 'administrator' ) ) {
			return;
		}

		// Pass $this as context to automatically pull from the module subfolder!
		$shortcuts = $this->plugin->get_config( 'toolbar-add', AdminToolbar::get_id() );
		if ( ! is_array( $shortcuts ) ) {
			return;
		}

		foreach ( $shortcuts as $node ) {
			if ( ! isset( $node['visibility'] ) || ! isset( $node['node_args'] ) ) {
				continue;
			}

			if ( 'front' === $node['visibility'] && is_admin() ) {
				continue;
			}
			if ( 'admin' === $node['visibility'] && ! is_admin() ) {
				continue;
			}

			$dependency_matrix = $node['dependency'] ?? array();
			if ( ! empty( $dependency_matrix ) && ! Environment::is_ready( $dependency_matrix ) ) {
				continue;
			}

			$wp_admin_bar->add_menu( (array) $node['node_args'] );
		}
	}

	/**
	 * Removes the generic core 'appearance' node from the frontend view.
	 *
	 * @param \WP_Admin_Bar $wp_admin_bar Global WordPress admin bar object.
	 * @return void
	 */
	public function remove_appearance_node_on_front( $wp_admin_bar ): void {
		if ( is_admin() || ! is_object( $wp_admin_bar ) ) {
			return;
		}
		$wp_admin_bar->remove_node( 'appearance' );
	}

	/**
	 * Retrieves feature-specific environment prerequisites.
	 *
	 * @return array Multi-dimensional preflight checks matrix.
	 */
	public function get_preflight_checks(): array {
		return array();
	}

	/**
	 * Dictates the out-of-the-box factory behavior status for this feature.
	 *
	 * @var string
	 */
	private string $factory_state = 'active';

	/**
	 * Evaluates if the feature is operationally active.
	 * LOEPZUIVER: Voldoet nu 100% aan de dwingende eis van het FeatureInterface! 🎯
	 *
	 * @return bool True if active or undetermined, false when explicitly disabled.
	 */
	public function is_active(): bool {
		// Haal de opgeslagen dashboard-keuzes op via de parent module en de Verkeersleider.
		$settings = get_option( 'dwp_enabled_features', array() );

		// STATE 1 & 2: De gebruiker heeft expliciet een keuze opgeslagen in de database.
		if ( isset( $settings[ $this->get_id() ] ) ) {
			return (bool) $settings[ $this->get_id() ];
		}

		// STATE 3 (ONBEPAALD): Geen database-waarde. We vallen terug op de fabriekstatus!
		return 'active' === $this->factory_state;
	}
}
