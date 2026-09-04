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
use DeWittePrins\CoreFunctionality\Traits\OperationalState;
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

	use OperationalState;

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
	 * Returns the unique identification string for this feature.
	 *
	 * @return string The unique micro-feature string key.
	 */
	public static function get_id(): string {
		return 'admin_toolbar_builder';
	}

	/**
	 * Retrieves the human-readable name of the feature.
	 *
	 * @since  1.0.0
	 * @return string Module title.
	 */
	public static function get_name(): string {
		return 'Admin Toolbar Builder Feature.';
	}

	/**
	 * Retrieves the contextual description of what the feature provides.
	 *
	 * @since  1.0.0
	 * @return string Module description.
	 */
	public static function get_description(): string {
		return 'The Admin Toolbar Builder Feature builds a navigation shortcuts menu in the admin toolbar.';
	}

	/**
	 * Retrieves feature-specific environment prerequisites.
	 *
	 * @return array Multi-dimensional preflight checks matrix.
	 */
	public static function get_preflight_checks(): array {
		return array();
	}

	/**
	 * AdminToolbarBuilder constructor.
	 *
	 * @param \DeWittePrins\CoreFunctionality\Contracts\ModuleInterface $module Parent module context.
	 */
	public function __construct( ModuleInterface $module ) {
		$this->module = $module;
		$this->plugin = $module->get_plugin();
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
		$shortcuts = $this->plugin->config->get( 'toolbar-add', AdminToolbar::get_id() );

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
	 * Exposes the parent module instance.
	 *
	 * @return ModuleInterface
	 */
	public function get_module(): ModuleInterface {
		return $this->module;
	}
}
