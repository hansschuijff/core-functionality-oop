<?php
/**
 * Admin Toolbar Node ID Visualizer Developer Feature.
 *
 * @package DeWittePrins\CoreFunctionality\Modules\AdminToolbar\Features
 * @since   1.0.0
 */

namespace DeWittePrins\CoreFunctionality\Modules\AdminToolbar\Features;

use DeWittePrins\CoreFunctionality\Contracts\FeatureInterface;
use DeWittePrins\CoreFunctionality\Contracts\ModuleInterface;
use DeWittePrins\CoreFunctionality\Plugin;
use DeWittePrins\CoreFunctionality\Traits\OperationalState;


if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class AdminToolbarVisualizer
 *
 * Maps active runtime Node ID keys for development troubleshooting.
 *
 * @since 1.0.0
 */
class AdminToolbarVisualizer implements FeatureInterface {

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
	 * AdminToolbarVisualizer constructor.
	 *
	 * @param \DeWittePrins\CoreFunctionality\Contracts\ModuleInterface $module Parent module context.
	 */
	public function __construct( ModuleInterface $module ) {
		$this->module = $module;
		$this->plugin = $module->get_plugin();
	}

	/**
	 * Returns the unique identification string for this feature toggle.
	 *
	 * @return string The unique micro-feature string key.
	 */
	public static function get_id(): string {
		return 'admin_toolbar_visualizer';
	}

	/**
	 * Retrieves the human-readable name of the feature.
	 *
	 * @since  1.0.0
	 * @return string Module title.
	 */
	public static function get_name(): string {
		return 'Admin Toolbar Visualizer Feature.';
	}

	/**
	 * Retrieves the contextual description of what the feature provides.
	 *
	 * @since  1.0.0
	 * @return string Module description.
	 */
	public static function get_description(): string {
		return 'The Admin Toolbar Visualizer Feature Maps active runtime Node ID keys for development troubleshooting.';
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
	 * Launches the operational execution lifecycle for this developer tool.
	 *
	 * @return void
	 */
	public function launch(): void {
		add_action( 'wp_before_admin_bar_render', array( $this, 'render_node_inspector' ), 300 );
	}

	/**
	 * Inspects the global admin bar registry and maps nodes into a readable dropdown tree.
	 *
	 * @global \WP_Admin_Bar $wp_admin_bar Global WordPress admin bar object.
	 * @return void
	 */
	public function render_node_inspector(): void {
		global $wp_admin_bar;

		if ( ! is_object( $wp_admin_bar ) || ! current_user_can( 'administrator' ) ) {
			return;
		}

		$all_nodes = $wp_admin_bar->get_nodes();

		if ( empty( $all_nodes ) ) {
			return;
		}

		$root_inspector_id = 'dwp_node_inspector_root';

		$wp_admin_bar->add_node(
			array(
				'id'    => $root_inspector_id,
				'title' => __( 'Node ID\'s', 'core-functionality-dwp' ),
				'href'  => '#',
			)
		);

		foreach ( $all_nodes as $node ) {
			if ( ! empty( $node->parent ) ) {
				$wp_admin_bar->add_node(
					array(
						'id'     => 'node_id_' . $node->id,
						'title'  => $node->id,
						'parent' => $root_inspector_id,
					)
				);
			}
		}

		foreach ( $all_nodes as $node ) {
			$args = array(
				'id'    => 'node_id_' . $node->id,
				'title' => $node->id,
			);

			if ( ! empty( $node->parent ) ) {
				$args['parent'] = 'node_id_' . $node->parent;
			} else {
				$args['parent'] = $root_inspector_id;
			}

			$wp_admin_bar->add_node( $args );
		}
	}

	/**
	 * Retrieves feature-specific environment prerequisites.
	 *
	 * @return array Multi-dimensional preflight checks matrix.
	 */
	public static function get_preflight_checks(): array {
		return array(
			'or' => array(
				array( 'and' => array( 'local_environment' ) ),
				array( 'and' => array( 'staging_environment' ) ),
				array( 'and' => array( 'development_environment' ) ),
			),
		);
	}
}
