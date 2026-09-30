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

use DeWittePrins\CoreFunctionality\Interfaces\FeatureInterface;
use DeWittePrins\CoreFunctionality\Interfaces\ModuleInterface;
use DeWittePrins\CoreFunctionality\Environment\Registers\AdminToolbarRegister;
use DeWittePrins\CoreFunctionality\Plugin;
use DeWittePrins\CoreFunctionality\Enums\Orientation;
use DeWittePrins\CoreFunctionality\DTO\ToolbarShortcut;
use WP_Admin_Bar;

use function current_user_can;
use function is_admin;
use function is_admin_bar_showing;
use function is_array;
use function esc_attr;
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
class MenuBuilder implements FeatureInterface {

	/**
	 * Configuration settings tree key inside the options vault.
	 *
	 * @var string
	 */
	private string $menu_builder_tree_key = 'menu-builder-hierarchical-tree';

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
	 * @since  1.0.0
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
	 * @since  1.0.0
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
	 * @since  1.0.0
	 * @return void
	 */
	public function launch(): void {
		if ( ! is_admin_bar_showing() ) {
			return;
		}

		// Securely register the configuration panel into the host page factory autonomously.
		add_action(
			'dwp_cf_register_settings_sections',
			function( $settings_factory ) {
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

		// 1. Gather all environment-passed shortcuts from the centralized repository register.
		$master_registry = AdminToolbarRegister::get();
		if ( empty( $master_registry ) || ! is_array( $master_registry ) ) {
			$master_registry = array();
		}

		// Flatten the registry collection to quickly look up active DTO objects by their node string keys.
		$flattened_pool = array();
		foreach ( $master_registry as $provider_id => $shortcuts ) {
			if ( is_array( $shortcuts ) ) {
				foreach ( $shortcuts as $shortcut ) {
					if ( $shortcut instanceof ToolbarShortcut ) {
						$flattened_pool[ $shortcut->id ] = $shortcut;
					}
				}
			}
		}

		// 2. Fetch the live initialized shared singleton options service out of settings.
		$saved_tree      = $this->plugin->settings->get( $this->menu_builder_tree_key, '', false, array() );

		if ( empty( $saved_tree ) || ! is_array( $saved_tree ) ) {
			return;
		}

		$current_orientation = is_admin() ? Orientation::ADMIN : Orientation::FRONTEND;

		// 3. Kickstart the cascading recursive traversal process starting at the 'site-name' root anchor level.
		$this->render_tree_nodes_recursively( $wp_admin_bar, $saved_tree, $flattened_pool, 'site-name', $current_orientation );
	}

	/**
	 * Cascades recursively down through the saved menu array tree branch structures to register WP_Admin_Bar nodes.
	 *
	 * Handles abstract parent titles, dynamic URL matching over closures, and visibility orientations tracking.
	 *
	 * @since  1.0.0
	 * @param  WP_Admin_Bar $wp_admin_bar  Global WordPress admin bar context orchestration instance.
	 * @param  array        $tree_level    Current depth layout level dictionary map containing node configs.
	 * @param  array        $pool          Flattened directory library mapping node keys to core ToolbarShortcut DTO objects.
	 * @param  string       $parent_id     The physical parent admin toolbar anchor string identification handle.
	 * @param  Orientation  $orientation   Current viewing environment signature ('admin' or 'frontend').
	 * @return void
	 */
	private function render_tree_nodes_recursively( WP_Admin_Bar $wp_admin_bar, array $tree_level, array $pool, string $parent_id, Orientation $orientation ): void {
		foreach ( $tree_level as $instance_id => $node_meta ) {
			$type    = $node_meta['type'] ?? 'shortcut';
			$node_id = $node_meta['id'] ?? $instance_id;

			$final_title = $node_meta['title'] ?? '';
			$final_href  = $node_meta['href'] ?? '#';
			$final_label = $final_title;

			// Check orientation and parameters filters if the target represents a hard code environment provider asset
			if ( 'shortcut' === $type ) {
				$shortcut_dto = $pool[ $node_id ] ?? null;

				if ( $shortcut_dto instanceof ToolbarShortcut ) {
					// Guard: Enforce visibility rules mapping current admin vs front status
					$dto_orientation = $shortcut_dto->orientation ?? Orientation::BOTH;
					if ( Orientation::BOTH !== $dto_orientation && $orientation !== $dto_orientation ) {
						continue;
					}

					// If the label override is empty or matching its original signature, fall back to the live translatable DTO value
					if ( empty( $final_title ) ) {
						$final_title = $shortcut_dto->title;
					}

					// Resolve the target URL safely if no custom overrides are configured on the card face
					if ( empty( $final_href ) || '#' === $final_href ) {
						$final_href = $shortcut_dto->get_href();
					}

					$final_label = $shortcut_dto->label;
				} else {
					// Skip rendering completely if the environment provider component has been safely decoupled or deactivated
					continue;
				}
			}

			// Generate a truly unique node ID per tree instance location to prevent toolbar registration collisions on duplications
			$wp_toolbar_node_id = 'wp-admin-bar-dwp-' . $node_id . '-' . $instance_id;

			// Map arguments directly into the native parameters structural array layout expected by WordPress core
			$node_args = array(
				'id'     => esc_attr( $wp_toolbar_node_id ),
				'title'  => esc_html( $final_title ),
				'parent' => esc_attr( $parent_id ),
				'href'   => esc_url( (string) $final_href ),
				'meta'   => array(
					'title' => esc_attr( $final_label ),
				),
			);

			$wp_admin_bar->add_node( $node_args );

			// Cascading down recursively if child entries populate the nested array dictionary layer
			if ( ! empty( $node_meta['children'] ) && is_array( $node_meta['children'] ) ) {
				$this->render_tree_nodes_recursively( $wp_admin_bar, $node_meta['children'], $pool, $wp_toolbar_node_id, $orientation );
			}
		}
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

		// Hardhandig verwijderen van de standaard WordPress 'Site bekijken' en 'Winkel bezoeken' sub-nodes
		$wp_admin_bar->remove_node( 'view-site' );
		$wp_admin_bar->remove_node( 'view-store' );

		// Verwijder de generieke core 'appearance' (Thema's/Customizer) node als we op de frontend zijn
		if ( ! is_admin() ) {
			$wp_admin_bar->remove_node( 'appearance' );
		}
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
}
