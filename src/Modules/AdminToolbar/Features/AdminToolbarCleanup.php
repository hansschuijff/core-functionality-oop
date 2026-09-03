<?php
/**
 * Admin Toolbar Navigation Cleanup and Reorganization Feature.
 *
 * @package DeWittePrins\CoreFunctionality\Modules\AdminToolbar\Features
 * @since   1.0.0
 */

namespace DeWittePrins\CoreFunctionality\Modules\AdminToolbar\Features;

use DeWittePrins\CoreFunctionality\Modules\AdminToolbar\AdminToolbar;
use DeWittePrins\CoreFunctionality\Contracts\ModuleInterface;
use DeWittePrins\CoreFunctionality\Contracts\FeatureInterface;
use DeWittePrins\CoreFunctionality\Traits\OperationalStateTrait;
use DeWittePrins\CoreFunctionality\Plugin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class AdminToolbarCleanup
 *
 * Intercepts existing WordPress admin bar nodes and re-parents them into an overflow menu.
 *
 * @since 1.0.0
 */
class AdminToolbarCleanup implements FeatureInterface {

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
	 * AdminToolbarCleanup constructor.
	 *
	 * @param \DeWittePrins\CoreFunctionality\Contracts\ModuleInterface $module Parent module context.
	 */
	public function __construct( ModuleInterface $module ) {
		$this->module = $module;
		$this->plugin = $module->get_plugin();

		$this->plugin->register_config_setting( 'toolbar-move', 'dwp_toolbar_move_settings' );
	}

	/**
	 * Returns the unique identification string for this feature toggle.
	 *
	 * @return string The unique micro-feature string key.
	 */
	public function get_id(): string {
		return 'admin_toolbar_cleanup';
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
		add_action( 'wp_before_admin_bar_render', array( $this, 'cleanup_toolbar_nodes' ), 200 );
	}

	/**
	 * Creates the overflow wrapper and safely relocates configured nodes.
	 *
	 * @global \WP_Admin_Bar $wp_admin_bar Global WordPress admin bar object.
	 * @return void
	 */
	public function cleanup_toolbar_nodes(): void {
		global $wp_admin_bar;

		if ( ! is_object( $wp_admin_bar ) ) {
			return;
		}

		$overflow_id = 'cf_more_admin_bar_menus';

		$wp_admin_bar->add_node(
			array(
				'id'    => $overflow_id,
				'title' => __( 'More', 'core-functionality-dwp' ),
				'href'  => '#',
			)
		);

		// Pass $this as context to automatically pull from the module subfolder!
		$nodes_to_move = $this->plugin->get_config( 'toolbar-move', AdminToolbar::get_id() );

		if ( ! is_array( $nodes_to_move ) ) {
			return;
		}

		foreach ( $nodes_to_move as $rule ) {
			if ( ! isset( $rule['node_id'] ) ) {
				continue;
			}

			if ( isset( $rule['scope'] ) ) {
				if ( 'front' === $rule['scope'] && is_admin() ) {
					continue;
				}
				if ( 'admin' === $rule['scope'] && ! is_admin() ) {
					continue;
				}
			}

			$target_parent = $rule['target_parent_id'] ?? $overflow_id;
			$this->move_node( $rule['node_id'], $target_parent );
		}
	}

	/**
	 * Relocates a registered node by updating its parent.
	 *
	 * @param string $node_id   The target node token.
	 * @param string $parent_id The destination parent token.
	 * @return void
	 */
	private function move_node( string $node_id, string $parent_id ): void {
		global $wp_admin_bar;

		if ( $wp_admin_bar->get_node( $node_id ) ) {
			$wp_admin_bar->add_node(
				array(
					'id'     => $node_id,
					'parent' => $parent_id,
				)
			);
		}
	}

	/**
	 * Retrieves feature-specific environment prerequisites.
	 *
	 * @return array Multi-dimensional preflight checks matrix.
	 */
	public function get_preflight_checks(): array {
		return array();
	}
}
