<?php
/**
 * Ads a configurable shortcuts menu to the admin toolbar (based on config
 * and availability of plugins and themes) in order to limit chaos make life more enjoyable.
 *
 * @package DeWittePrins\CoreFunctionality\Modules\AdminToolbar\Features\MenuBuilder
 * @since   1.0.0
 */

namespace DeWittePrins\CoreFunctionality\Modules\AdminToolbar\Features\LegacyMenuBuilder;

use DeWittePrins\CoreFunctionality\Abstracts\AbstractFeature;
use DeWittePrins\CoreFunctionality\DTO\ComponentMeta;
use Override;
use DeWittePrins\Corefunctionality\Modules\AdminToolbar\AdminToolbar;
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
class LegacyMenuBuilder extends AbstractFeature {

	/**
	 * Returns the descriptive data of this feature.
	 *
	 * @since  1.0.0
	 * @return ComponentMeta Id, name, description and requirements.
	 */
	#[Override]
	public static function get_meta(): ComponentMeta {
		return new ComponentMeta(
			id:            'legacy_admin_toolbar_builder',
			name:          'Legacy Admin Toolbar Builder Feature',
			description:   'The Admin Toolbar Builder Feature builds a navigation shortcuts menu in the admin toolbar.',
		);
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
			// continue;
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
}
