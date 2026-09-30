<?php
/**
 * Ads a configurable shortcuts menu to the admin toolbar (based on config
 * and availability of plugins and themes) in order to limit chaos make life more enjoyable.
 *
 * @package DeWittePrins\CoreFunctionality\Modules\AdminToolbar\Features\MenuBuilder
 * @since   1.0.0
 */

namespace DeWittePrins\CoreFunctionality\Modules\AdminToolbar\Features\LegacyMenuBuilder;

use DeWittePrins\Corefunctionality\Modules\AdminToolbar\AdminToolbar;
use DeWittePrins\CoreFunctionality\Interfaces\FeatureInterface;
use DeWittePrins\CoreFunctionality\Interfaces\ModuleInterface;
use DeWittePrins\CoreFunctionality\Plugin;
use DeWittePrins\CoreFunctionality\Enums\Orientation;
use WP_Admin_Bar;

use function current_user_can;
use function is_admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class MenuBuilder
 *
 * Inject shortcut navigation elements using the decoupled Environment readiness matrix.
 *
 * @since 1.0.0
 */
class LegacyMenuBuilder implements FeatureInterface {

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
	 * Returns the unique identification string for this feature.
	 *
	 * @return string The unique micro-feature string key.
	 */
	public static function get_id(): string {
		return 'legacy_admin_toolbar_builder';
	}

	/**
	 * Retrieves the human-readable name of the feature.
	 *
	 * @since  1.0.0
	 * @return string Module title.
	 */
	public static function get_name(): string {
		return 'Legacy Admin Toolbar Builder Feature.';
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
	 * @return string|array A single string dependency or a multi-dimensional preflight checks matrix.
	 */
	public static function get_dependencies(): string|array {
		return '';
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
	 * Requests the current usage-target of the feature (Frontend, Admin, Both).
	 *
	 * @since  1.0.0
	 * @return Orientation Enum indication if the feature is meant for use on the Frontend, Admin or both.
	 */
	public static function get_orientation(): Orientation {
		return Orientation::BOTH;
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
	 * Processes the abstract settings structure and dynamically injects nodes
	 * into the toolbar.
	 *
	 * @global WP_Admin_Bar $wp_admin_bar Global WordPress admin bar object.
	 * @return void
	 */
	public function inject_toolbar_shortcuts(): void {
		global $wp_admin_bar;
		if ( ! is_object( $wp_admin_bar ) || ! current_user_can( 'administrator' ) ) {
			return;
		}

		// Pass $this as context to automatically pull from the module subfolder!
		$shortcuts = $this->plugin->settings->get( 'toolbar-add', AdminToolbar::get_id() );
if ( \function_exists( '\d' ) ) {
	\d(
		\current_filter(),
		__METHOD__ . ':' . __LINE__,
		$shortcuts
	);
}
// error_log( print_r( $shortcuts, true ) );
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
			// if ( ! empty( $dependency_matrix ) && ! $this->plugin->kernel->environment->is_ready( $dependency_matrix ) ) {
			// 	continue;
			// }

			$wp_admin_bar->add_menu( (array) $node['node_args'] );
		}
	}

	/**
	 * Removes the generic core 'appearance' node from the frontend view.
	 *
	 * @param WP_Admin_Bar $wp_admin_bar Global WordPress admin bar object.
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
