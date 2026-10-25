<?php
/**
 * Adds a configurable shortcuts menu to the admin toolbar.
 *
 * Automatically extracts validated shortcut configurations from the environment
 * registration pool and injects them safely into the WordPress Admin Bar.
 *
 * @package DeWittePrins\CoreFunctionality\Modules\AdminToolbar\Features\MenuBuilder
 * @author  Hans Schuijff <@hansschuijff>
 * @license GPL-2.0
 * @since   1.0.0
 */

declare(strict_types=1);

namespace DeWittePrins\CoreFunctionality\Modules\AdminToolbar\Features\MenuBuilder;

use DeWittePrins\CoreFunctionality\Abstracts\AbstractFeature;
use DeWittePrins\CoreFunctionality\DTO\ComponentMeta;
use DeWittePrins\CoreFunctionality\Modules\AdminToolbar\AdminToolbar;
use Override;
use DeWittePrins\CoreFunctionality\Environment\Registers\AdminToolbarRegister;
use DeWittePrins\CoreFunctionality\Enums\Orientation;
use DeWittePrins\CoreFunctionality\DTO\ToolbarShortcut;
use WP_Admin_Bar;

use function __;
use function current_user_can;
use function is_admin;
use function is_admin_bar_showing;
use function is_array;
use function esc_html;
use function esc_url;
use function add_action;

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
class MenuBuilder extends AbstractFeature {

	/**
	 * Configuration settings tree key inside the options vault.
	 *
	 * @var string
	 */
	private string $menu_builder_tree_key = 'menu-builder-hierarchical-tree';

	/**
	 * Returns the descriptive data of this feature.
	 *
	 * @since  1.0.0
	 * @return ComponentMeta Id, name, description and requirements.
	 */
	#[Override]
	public static function get_meta(): ComponentMeta {
		return new ComponentMeta(
			id:            'admin_toolbar_builder',
			name:          __( 'Admin Toolbar Builder Feature', 'core-functionality' ),
			description:   __( 'The Admin Toolbar Builder Feature builds a navigation shortcuts menu in the admin toolbar.', 'core-functionality' ),
		);
	}

	/**
	 * Launches the operational execution lifecycle for this micro-feature.
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public function launch(): void {
		if ( ! is_admin_bar_showing() ) {
			return;
		}

		// Securely register the configuration panel into the host page factory autonomously.
		add_action(
			'core-functionality/register-settings-sections',
			function ( $settings_factory ) {
				$settings_factory->register_section( MenuBuilderSettings::class );
			}
		);

		add_action( 'admin_bar_menu', array( $this, 'inject_toolbar_shortcuts' ), 999 );
		add_action( 'admin_bar_menu', array( $this, 'cleanup_default_toolbar_nodes' ), 1000 );
	}

	/**
	 * Processes the aggregated register and dynamically injects nodes into the toolbar.
	 *
	 * Re-engineered to recursive rendering architecture reading from the flat options tree block.
	 *
	 * @since  1.0.0
	 * @param  WP_Admin_Bar $wp_admin_bar Global WordPress admin bar object.
	 * @return void
	 */
	public function inject_toolbar_shortcuts( WP_Admin_Bar $wp_admin_bar ): void {
		if ( ! is_object( $wp_admin_bar ) || ! current_user_can( 'administrator' ) ) {
			return;
		}

		$saved_tree = $this->plugin->settings->get_whole( $this->menu_builder_tree_key, AdminToolbar::get_id(), array() );
		if ( empty( $saved_tree ) || ! is_array( $saved_tree ) ) {
			return;
		}

		// Only the nodes of active plugins and themes; the saved tree may also hold those of inactive ones.
		$pool = array();
		foreach ( AdminToolbarRegister::get_active() as $shortcuts ) {
			foreach ( $shortcuts as $shortcut ) {
				$pool[ $shortcut->id ] = $shortcut;
			}
		}

		$side = is_admin() ? Orientation::ADMIN : Orientation::FRONTEND;

		$this->render_tree_nodes_recursively( $wp_admin_bar, $saved_tree, $pool, 'site-name', $side );
	}

	/**
	 * Adds the nodes of one level of the saved tree to the toolbar, and their children.
	 *
	 * A shortcut whose provider is not active is left out, with its children. A custom submenu
	 * without a link of its own is left out when none of its children is shown.
	 *
	 * @since  1.0.0
	 * @param  WP_Admin_Bar $wp_admin_bar The WordPress admin bar.
	 * @param  array        $tree_level   One level of the saved tree: instance id => node settings.
	 * @param  array        $pool         The shortcuts of the active providers, by node id.
	 * @param  string       $parent_id    The toolbar id of the parent node.
	 * @param  Orientation  $side         Whether this is an admin page or the front end.
	 * @return int The number of nodes added on this level.
	 */
	private function render_tree_nodes_recursively( WP_Admin_Bar $wp_admin_bar, array $tree_level, array $pool, string $parent_id, Orientation $side ): int {
		$added = 0;

		foreach ( $tree_level as $instance_id => $node_meta ) {
			if ( ! $this->is_visible_on( $node_meta, $side ) ) {
				continue;
			}

			$type    = $node_meta['type'] ?? 'shortcut';
			$node_id = $node_meta['id'] ?? $instance_id;
			$title   = (string) ( $node_meta['title'] ?? '' );

			if ( 'shortcut' === $type ) {
				$shortcut = $pool[ $node_id ] ?? null;
				if ( ! $shortcut instanceof ToolbarShortcut ) {
					continue;
				}

				// An empty title follows the (translated) title of the shortcut; the URL always comes from the shortcut.
				$title = '' !== $title ? $title : $shortcut->title;
				$href  = $shortcut->get_href();
				$label = $shortcut->label;
			} else {
				$href  = (string) ( $node_meta['href'] ?? '' );
				$href  = '#' === $href ? '' : $href;
				$label = $title;
			}

			$toolbar_node_id = 'dwp-' . $node_id . '-' . $instance_id;

			$wp_admin_bar->add_node(
				array(
					'id'     => $toolbar_node_id,
					'title'  => esc_html( $title ),
					'parent' => $parent_id,
					'href'   => '' !== $href ? esc_url( $href ) : false,
					'meta'   => array(
						'title' => $label,
					),
				)
			);

			$children = ( ! empty( $node_meta['children'] ) && is_array( $node_meta['children'] ) )
				? $this->render_tree_nodes_recursively( $wp_admin_bar, $node_meta['children'], $pool, $toolbar_node_id, $side )
				: 0;

			if ( 'shortcut' !== $type && '' === $href && 0 === $children ) {
				$wp_admin_bar->remove_node( $toolbar_node_id );
				continue;
			}

			++$added;
		}

		return $added;
	}

	/**
	 * Checks whether a node of the saved tree is shown on this side (admin pages or front end).
	 *
	 * @since  1.0.0
	 * @param  array       $node_meta The node settings from the saved tree.
	 * @param  Orientation $side      Whether this is an admin page or the front end.
	 * @return bool True if the node is shown here.
	 */
	private function is_visible_on( array $node_meta, Orientation $side ): bool {
		$visibility = Orientation::tryFrom( (string) ( $node_meta['visibility'] ?? '' ) ) ?? Orientation::BOTH;

		return Orientation::BOTH === $visibility || $side === $visibility;
	}

	/**
	 * Cleans up core default WordPress toolbar nodes to make room for the custom hierarchy tree.
	 *
	 * Strips 'view-site', 'visit-store', and 'appearance' nodes to guarantee an unpolluted canvas.
	 *
	 * @since  1.0.0
	 * @param  WP_Admin_Bar $wp_admin_bar Global WordPress admin bar object.
	 * @return void
	 */
	public function cleanup_default_toolbar_nodes( WP_Admin_Bar $wp_admin_bar ): void {
		if ( ! is_object( $wp_admin_bar ) ) {
			return;
		}

		// Hardhandig verwijderen van de standaard WordPress 'Site bekijken' en 'Winkel bezoeken' sub-nodes.
		$wp_admin_bar->remove_node( 'view-site' );
		$wp_admin_bar->remove_node( 'view-store' );
		$wp_admin_bar->remove_node( 'dashboard' );
		$wp_admin_bar->remove_node( 'plugins' );

		// Verwijder de generieke core 'appearance' (Thema's/Customizer) node als we op de frontend zijn.
		if ( ! is_admin() ) {
			$wp_admin_bar->remove_node( 'appearance' );
		}
	}
}
