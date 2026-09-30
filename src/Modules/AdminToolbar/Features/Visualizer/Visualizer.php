<?php
/**
 * Admin Toolbar Node ID Visualizer Developer Feature.
 *
 * A helper function that adds a toplevel menu to the admin toolbar showing
 * all current node-id's in the admin toolbar.
 *
 * @package DeWittePrins\CoreFunctionality\Modules\AdminToolbar\Features\Visualizer
 * @since   1.0.0
 */

namespace DeWittePrins\CoreFunctionality\Modules\AdminToolbar\Features\Visualizer;

use DeWittePrins\CoreFunctionality\Interfaces\FeatureInterface;
use DeWittePrins\CoreFunctionality\Interfaces\ModuleInterface;
use DeWittePrins\CoreFunctionality\Plugin;
use DeWittePrins\CoreFunctionality\Enums\Orientation;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Visualizer
 *
 * Maps active runtime Node ID keys for development troubleshooting.
 *
 * @since 1.0.0
 */
class Visualizer implements FeatureInterface {

	/**
	 * Constructor.
	 *
	 * @param ModuleInterface $module Parent module context.
	 */
	public function __construct(
		private readonly ModuleInterface $module,
		private readonly Plugin $plugin
	) {}

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
	 * Retrieves feature-specific environment prerequisites.
	 *
	 * @return string|array A single (string) dependency or a multi-dimensional preflight checks matrix.
	 */
	public static function get_dependencies(): string|array {
		return array();
	}

	/**
	 * Returns an array with internal dependencies with other features or modules.
	 *
	 * @since  1.0.0
	 * @return array<int, string> List of fully qualified feature class strings.
	 */
	public static function uses_features(): array {
		return array();
	}

	/**
	 * Exposes the parent module instance.
	 *
	 * @return \DeWittePrins\CoreFunctionality\Interfaces\ModuleInterface
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
	 * Requests the current usage-target of the feature (Frontend, Admin, Both).
	 *
	 * @since  1.0.0
	 * @return Orientation Enum indication if the feature is meant for use on the Frontend, Admin or both.
	 */
	public static function get_orientation(): Orientation {
		return Orientation::BOTH;
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
				'title' => __( 'Node ID\'s', 'dwp-cf' ),
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
}
