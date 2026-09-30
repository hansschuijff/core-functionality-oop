<?php
/**
 * Cleans the admin toolbar by moving less used items to an overflow subnav.
 * It decides which nodes to move based on a configuration matrix, defaulting
 * to an overflow state unless explicitly chosen to be maintained on the main bar.
 *
 * @package    DeWittePrins\CoreFunctionality
 * @subpackage Modules\AdminToolbar\Features\Cleaner
 * @author     Hans Schuijff <@hansschuijff>
 * @license    GPL-2.0
 * @since      1.0.0
 */

declare( strict_types=1 );

namespace DeWittePrins\CoreFunctionality\Modules\AdminToolbar\Features\Cleaner;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use WP_Admin_Bar;
use DeWittePrins\CoreFunctionality\Plugin;
use DeWittePrins\CoreFunctionality\Interfaces\FeatureInterface;
use DeWittePrins\CoreFunctionality\Interfaces\ModuleInterface;
use DeWittePrins\CoreFunctionality\Modules\AdminToolbar\Features\Cleaner\CleanerSettings;
use DeWittePrins\CoreFunctionality\Enums\Orientation;

use function __;
use function is_admin_bar_showing;
use function is_array;
use function is_object;
use function add_action;

/**
 * Class Cleaner
 *
 * Implements the operational runtime hook lifecycle filtering the active admin bar
 * nodes using a negative-selection matrix blueprint.
 *
 * @since 1.0.0
 */
class Cleaner implements FeatureInterface {

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
	 * @since  1.0.0
	 * @return string The unique micro-feature string key.
	 */
	public static function get_id(): string {
		return 'admin_toolbar_cleaner';
	}

	/**
	 * Retrieves the human-readable name of the feature.
	 *
	 * @since  1.0.0
	 * @return string Feature title.
	 */
	public static function get_name(): string {
		return __( 'Admin Toolbar Cleaner Feature', 'dwp-cf' );
	}

	/**
	 * Retrieves the contextual description of what the feature provides.
	 *
	 * @since  1.0.0
	 * @return string Feature description.
	 */
	public static function get_description(): string {
		return __( 'Moves unwanted Admin Toolbar shortcuts to an overflow submenu, based on a negative-selection user matrix.', 'dwp-cf' );
	}

	/**
	 * Retrieves feature-specific environment prerequisites.
	 *
	 * @since  1.0.0
	 * @return string|array String dependency or multi-dimensional preflight checks matrix.
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
	 * @since  1.0.0
	 * @return void
	 */
	public function launch(): void {

		// Register the settings page section.
		$this->register_settings_section();

		// Hook the main cleaner method.
		if ( is_admin_bar_showing() ) {
			add_action( 'wp_before_admin_bar_render', array( $this, 'move_nodes_to_overflow_menu' ), 200 );
		}
	}

	/**
	 * Register the settings section of this feature.
	 *
	 * @return void
	 */
	private function register_settings_section() {
		// Securely register the configuration panel into the host page factory autonomously.
		add_action(
			'dwp_cf_register_settings_sections',
			function( $settings_factory ) {
				$settings_factory->register_section( CleanerSettings::class );
			}
		);
	}

	/**
	 * Interrogates single-source options vault matrix mappings to restructure the WordPress admin bar nodes.
	 *
	 * Intercepts the live global WP_Admin_Bar nodes, logs discovered top-levels, and forces unapproved into overflow.
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public function move_nodes_to_overflow_menu(): void {
		global $wp_admin_bar;

		if ( ! is_object( $wp_admin_bar ) ) {
			return;
		}

		// Fetch all nodes in the current admin bar.
		$all_nodes = $wp_admin_bar->get_nodes();
		if ( empty( $all_nodes ) || ! is_array( $all_nodes ) ) {
			return;
		}

		$all_movable_nodes = $this->get_movable_nodes( $all_nodes );
		if ( ! empty( $all_movable_nodes ) ) {
			$this->save_movable_nodes( $all_movable_nodes );
		}

		if ( $this->is_cleaner_settings_page() ) {
			// don't clean on the cleaner settings page, so all top level nodes stay visible.
			return;
		}

		// Pull active matrix visibility configurations out of the managed Settings service
		$keep_in_place_nodes = $this->plugin->settings->get( CleanerSettings::get_settings_key(), $this->module::get_id() );
		if ( ! is_array( $keep_in_place_nodes ) ) {
			$keep_in_place_nodes = array();
		}

		// Scan through the top-level movable nodes to see if we need an overflow menu.
		$need_to_move = false;
		foreach ( $all_movable_nodes as $node ) {
			$keep_in_place = isset( $keep_in_place_nodes[ $node->id ]['main'] )
				? (bool) $keep_in_place_nodes[ $node->id ]['main']
				: false;

			if ( ! $keep_in_place ) {
				// There is at least one node that needs to be moved.
				$need_to_move = true;
				break;
			}
		}

		// Create the overflow menu node.
		if ( $need_to_move && ! $wp_admin_bar->get_node( self::get_overflow_node_id() ) ) {
			$wp_admin_bar->add_node(
				array(
					'id'    => self::get_overflow_node_id(),
					'title' => __( 'More', 'dwp-cf' ),
					'href'  => '#',
				)
			);
		}

		// Make the movable nodes children of the overflow menu node.
		foreach ( $all_nodes as $node ) {
			// Skip lower level nodes and protected nodes.
			if ( ! empty( $node->parent ) || in_array( $node->id, self::get_protected_nodes(), true ) ) {
				continue;
			}

			$must_keep_node = isset( $keep_in_place_nodes[ $node->id ]['main'] )
				? (bool) $keep_in_place_nodes[ $node->id ]['main']
				: false;

			if ( ! $must_keep_node ) {
				$node_args = array(
					'id'     => $node->id,
					'title'  => $node->title,
					'parent' => self::get_overflow_node_id(),
					'href'   => $node->href,
					'meta'   => $node->meta,
				);
				// Adding a node that allready exists just moves it to the new parent.
				$wp_admin_bar->add_node( $node_args );
			}
		}
	}

	private function save_movable_nodes( array $all_movable_nodes ) {
		# Merge the newly found nodes with the already saved ones, to make sure no nodes are forgotten.
		$saved_nodes  = $this->plugin->settings->get( self::get_movable_nodes_key(), $this->module::get_id() );

		$saved_nodes  = is_array( $saved_nodes ) ? $saved_nodes : array();
		$merged_nodes = array_merge( $saved_nodes, $all_movable_nodes );

		$this->plugin->settings->save( self::get_movable_nodes_key(), $merged_nodes );
	}

	private function is_cleaner_settings_page(): bool {
		global $plugin_page;
		return is_admin()
			&& isset( $plugin_page ) && 'dwp-cf-settings' === $plugin_page
			&& isset( $_GET['section'] ) && CleanerSettings::get_id() === $_GET['section'];
	}

	private function get_movable_nodes( array $nodes ) {
		$movable_top_level_nodes = array();
		foreach ( $nodes as $node ) {
			if ( empty( $node->parent ) && ! in_array( $node->id, self::get_protected_nodes(), true ) ) {

				$movable_top_level_nodes[ $node->id ] = array(
					'id'    => $node->id,
					'label' => ! empty( $node->title ) ? strip_tags( (string) $node->title ) : $node->id,
				);
			}
		}
		return $movable_top_level_nodes;
	}

	/**
	 * Exposes the parent module instance.
	 *
	 * @since  1.0.0
	 * @return ModuleInterface
	 */
	public function get_module(): ModuleInterface {
		return $this->module;
	}

	public static function get_movable_nodes_key(): string {
		return 'admin-toolbar-cleaner-movable-nodes';
	}

	public static function get_overflow_node_id(): string {
		return 'cf_more_admin_bar_menus';
	}

	public static function get_protected_nodes(): array {
		return array(
			'wp-logo',
			'site-name',
			'updates',
			'comments',
			'new-content',
			'my-account',
			'menu-toggle',               // The hamburger menu icon for small screens.
			'top-secondary',             // The account panel on the right side.
			self::get_overflow_node_id() // Our overflow (more) menu node.
		);
	}
}
