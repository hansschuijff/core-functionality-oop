<?php
/**
 * Admin Menu Architect Feature.
 *
 * @package DeWittePrins\CoreFunctionality\Modules\AdminMenu\Features\AdminMenuArchitect
 */

declare(strict_types=1);

namespace DeWittePrins\CoreFunctionality\Modules\AdminMenu\Features\AdminMenuArchitect;

use DeWittePrins\CoreFunctionality\Interfaces\FeatureInterface;
use DeWittePrins\CoreFunctionality\Interfaces\ModuleInterface;
use DeWittePrins\CoreFunctionality\Plugin;
use DeWittePrins\CoreFunctionality\Modules\AdminMenu\DTO\AdminMenuConfigNode;
use DeWittePrins\CoreFunctionality\Enums\Orientation;


use function add_action;
use function add_menu_page;
use function add_submenu_page;
use function remove_menu_page;
use function current_user_can;

/**
 * Class AdminMenuArchitect
 *
 * Compiles, orders, filters, and cleans up the WordPress admin sidebar navigation.
 */
class AdminMenuArchitect implements FeatureInterface {

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
	 * Launches the operational execution lifecycle for this feature.
	 *
	 * @return void
	 */
	public function launch(): void {
		// Hook for adding and managing custom menu structures.
		add_action( 'admin_menu', array( $this, 'compile_and_register_menus' ), 100 );

		// Hook for removing unwanted native/core menus (runs late to catch everything cleanly).
		add_action( 'admin_menu', array( $this, 'remove_blacklisted_menus' ), 9999 );
	}

	/**
	 * Compiles configuration and registers allowed menu items.
	 *
	 * @return void
	 */
	public function compile_and_register_menus(): void {
		return;
		$raw_items = (array) $this->plugin->settings->get( 'menu-add', $this->module->get_id() );
		$defaults  = (array) $this->plugin->settings->get( 'menu-add-defaults', $this->module->get_id() );

		if ( empty( $raw_items ) ) {
			return;
		}

		$config_nodes = array();
		foreach ( $raw_items as $item_array ) {
			$config_nodes[] = AdminMenuConfigNode::from_array( $item_array, $defaults );
		}

		/**
		 * Filters the compiled admin menu configuration nodes before registration.
		 *
		 * @param AdminMenuConfigNode[] $config_nodes Array of config node DTOs.
		 */
		$filtered_nodes = apply_filters( 'dwp_core_admin_menu_config_nodes', $config_nodes );

		foreach ( $filtered_nodes as $node ) {
			if ( ! $node instanceof AdminMenuConfigNode || ! $node->is_enabled ) {
				continue; // Here we successfully replicate the "remove/hide" logic by skipping disabled blocks.
			}

			$block = $node->block;

			if ( empty( $block->page_title ) || empty( $block->menu_title ) || ! current_user_can( $block->capability ) ) {
				continue;
			}

			if ( $block->parent_slug ) {
				add_submenu_page(
					$block->parent_slug,
					$block->page_title,
					$block->menu_title,
					$block->capability,
					$block->menu_slug,
					$block->callback,
					$block->menu_position
				);
				continue;
			}

			add_menu_page(
				$block->page_title,
				$block->menu_title,
				$block->capability,
				$block->menu_slug,
				$block->callback,
				$block->icon,
				$block->menu_position
			);
		}
	}

	/**
	 * Safely removes core WordPress menu nodes based on structural slugs.
	 *
	 * Replaces the unsafe title-matching unset($menu) logic with robust slug removal.
	 *
	 * @return void
	 */
	public function remove_blacklisted_menus(): void {
		// Read the slugs to remove (e.g., array('edit.php', 'upload.php', 'edit-comments.php'))
		$slugs_to_remove = (array) $this->plugin->settings->get( 'menu-remove', $this->module->get_id() );

		if ( empty( $slugs_to_remove ) ) {
			return;
		}

		/**
		 * Filters the admin menu slugs targeted for removal.
		 *
		 * @param string[] $slugs_to_remove Array of WordPress admin menu slug strings.
		 */
		$slugs_to_remove = apply_filters( 'dwp_core_admin_menu_removals', $slugs_to_remove );

		foreach ( $slugs_to_remove as $slug ) {
			if ( is_string( $slug ) && ! empty( $slug ) ) {
				remove_menu_page( $slug );
			}
		}
	}

	public static function get_id(): string { return 'admin_menu_architect'; }
	public static function get_name(): string { return 'Admin Menu Architect'; }
	public static function get_description(): string { return 'Architects, structures and cleans up the admin side navigation.'; }
	public function get_module(): ModuleInterface { return $this->module; }

	/**
	 * Retrieves feature-specific environment prerequisites.
	 *
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

}
