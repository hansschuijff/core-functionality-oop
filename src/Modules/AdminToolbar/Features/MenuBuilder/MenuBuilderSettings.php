<?php
/**
 * Admin Toolbar Menu Builder Drag-and-Drop Tree Configuration Section.
 *
 * @package    DeWittePrins\CoreFunctionality
 * @subpackage Modules\AdminToolbar\Features\MenuBuilder
 * @author     Hans Schuijff <@hansschuijff>
 * @license    GPL-2.0
 * @since      1.0.0
 */

declare( strict_types=1 );

namespace DeWittePrins\CoreFunctionality\Modules\AdminToolbar\Features\MenuBuilder;

use DeWittePrins\CoreFunctionality\Admin\Services\Settings\Interfaces\SettingsSectionInterface;
use DeWittePrins\CoreFunctionality\Environment\Registers\AdminToolbarRegister;
use DeWittePrins\CoreFunctionality\Enums\Orientation;
use DeWittePrins\CoreFunctionality\DTO\ToolbarShortcut;
use DeWittePrins\CoreFunctionality\Plugin;
use WP_Error;
use Override;

// Import global PHP and WordPress core abstraction functions.
use function __;
use function esc_html;
use function esc_attr;
use function is_array;
use function sanitize_key;
use function sanitize_text_field;
use function count;
use function sprintf;
use function _n;
use function wp_unslash;
use function current_user_can;
use function json_decode;
use function array_keys;
use function plugins_url;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class MenuBuilderSettings
 *
 * Implements an advanced multi-instance menu tree orchestrator with dynamic submenu additions.
 *
 * @since 1.0.0
 */
class MenuBuilderSettings implements SettingsSectionInterface {

	/**
	 * Configuration settings keys inside the options vault.
	 *
	 * @var string
	 */
	private string $menu_builder_tree_option_token = 'menu-builder-hierarchical-tree';

	/**
	 * MenuBuilderSettings Constructor.
	 *
	 * @since 1.0.0
	 * @param Plugin $plugin The central plugin class instance acting as a provider.
	 */
	public function __construct( private readonly Plugin $plugin ) {}

	#[Override]
	public static function get_id(): string {
		return 'cf-menu-builder-settings';
	}

	#[Override]
	public static function get_icon(): string {
		return 'dashicons-menu-alt';
	}

	#[Override]
	public static function get_name(): string {
		return __( 'Admin Toolbar Menu Builder', 'dwp-cf' );
	}

	#[Override]
	public static function get_description(): string {
		return __( 'Design your custom WordPress Admin Toolbar. Filter shortcuts by source provider, select elements from the pool to clone them, and manage hierarchy sequence fields.', 'dwp-cf' );
	}

	#[Override]
	public static function get_stylesheets(): array {
		return array();
	}

	#[Override]
	public static function get_scripts(): array {
		$base_url = plugins_url( 'assets/js/', __FILE__ );

		return array(
			'dwp-cf-admin-toolbar-menu-builder-js' => $base_url . 'menu-builder-settings.js',
		);
	}

	#[Override]
	public function render( bool $defaults_only = false ): void {
		$saved_tree = $this->plugin->settings->get( $this->menu_builder_tree_option_token, '', false, array() );
		if ( ! is_array( $saved_tree ) ) {
			$saved_tree = array();
		}

		$live_registry = AdminToolbarRegister::get();
		$providers     = array_keys( $live_registry );

		echo '<div class="dwp-menu-builder-canvas-wrapper" style="display: flex; flex-direction: column; gap: 25px; margin-top: 15px;">';
		$this->render_builder_drag_tree_grid( $live_registry, $providers, $saved_tree );
		echo '</div>';
	}

	/**
	 * Renders the structural layout workspace optimized for tree nestings and provider filtering.
	 */
	private function render_builder_drag_tree_grid( array $live_registry, array $providers, array $saved_tree ): void {
		$col_pool_title   = esc_html__( 'Available Shortcuts Pool', 'dwp-cf' );
		$col_pool_desc    = esc_html__( 'Filter by source plugin. Check items to add them, or drag nodes to build your menu skeleton blueprint.', 'dwp-cf' );
		$col_active_title = esc_html__( 'Active Admin Toolbar Menu Structure (Max 3 Levels)', 'dwp-cf' );
		$col_active_desc  = esc_html__( 'Drag nodes vertically to reorder. Expand cards via the arrow to tweak live titles, custom URLs, and adjust indentation levels.', 'dwp-cf' );
		$filter_label     = esc_html__( 'Filter by Source Provider:', 'dwp-cf' );
		$all_sources_txt  = esc_html__( 'All Source Providers', 'dwp-cf' );

		echo <<<HTML
		<div class="dwp-menu-builder-columns" style="display: flex; gap: 20px; flex-wrap: wrap;">

			<!-- LEFT POOL COLUMN -->
			<div class="dwp-builder-column" style="flex: 1; min-width: 320px; background: #fff; border: 1px solid #ccd0d4; padding: 20px; border-radius: 4px;">
				<h3 style="margin-top: 0; margin-bottom: 5px; font-size: 15px; font-weight: 600; color: #1d2327;">{$col_pool_title}</h3>
				<p class="description" style="margin-bottom: 15px;">{$col_pool_desc}</p>

				<div class="dwp-provider-filter-wrapper" style="margin-bottom: 15px; padding: 10px; background: #f1f5f9; border-radius: 3px; border: 1px solid #e2e8f0;">
					<label style="display: block; font-weight: 600; font-size: 12px; color: #475569; margin-bottom: 6px;">{$filter_label}</label>
					<select id="js-dwp-provider-filter" style="width: 100%; height: 32px; border-color: #cbd5e1;">
						<option value="all">{$all_sources_txt}</option>
		HTML;

		foreach ( $providers as $provider_id ) {
			$clean_id = esc_attr( $provider_id );
			$friendly_provider = esc_html( ucwords( str_replace( array( '_', '-' ), ' ', $provider_id ) ) );
			echo "<option value='{$clean_id}'>{$friendly_provider}</option>";
		}

		echo <<<HTML
					</select>
				</div>

				<!-- UNIFIED GREY CONTAINER BOX: Houses actions controls and node elements lists together -->
				<div class="js-dwp-bulk-management-box" style="background: #f8fafc; border: 1px solid #cbd5e1; padding: 15px; border-radius: 4px; display: flex; flex-direction: column; gap: 12px; box-sizing: border-box;">

					<!-- DYNAMIC GENERATED CONTROLS WILL INJECT HERE VIA JS -->

					<div class="dwp-builder-items-pool js-dwp-builder-pool" style="display: flex; flex-direction: column; gap: 8px; max-height: 450px; overflow-y: auto; padding: 5px; background: #fff; border: 1px solid #e2e8f0; border-radius: 3px;">
		HTML;

		// Inject dynamic virtual submenu header template node inside the stencil list
		$virtual_submenu_dto = new ToolbarShortcut(
			id:          'custom-link-node',
			title:       __( 'Empty Submenu / Custom Link', 'dwp-cf' ),
			label:       __( 'Generates an abstract menu parent node group or redirection asset url link', 'dwp-cf' ),
			orientation: Orientation::BOTH,
			href:        '#'
		);
		$this->render_draggable_tree_node( $virtual_submenu_dto, 'custom_link', 0, false, '' );

		// Render the active environment registered shortcuts pool items
		foreach ( $live_registry as $provider_id => $shortcuts ) {
			if ( is_array( $shortcuts ) ) {
				foreach ( $shortcuts as $shortcut ) {
					if ( $shortcut instanceof ToolbarShortcut ) {
						$this->render_draggable_tree_node( $shortcut, $provider_id, 0, false, '' );
					}
				}
			}
		}

		echo <<<HTML
					</div>
				</div>
			</div>

			<!-- RIGHT ACTIVE BLUEPRINT CANVAS TREE COLUMN -->
			<div class="dwp-builder-column" style="flex: 1; min-width: 380px; background: #fff; border: 1px solid #ccd0d4; padding: 20px; border-radius: 4px; border-top: 4px solid #2271b1;">
				<h3 style="margin-top: 0; margin-bottom: 5px; font-size: 15px; font-weight: 600; color: #1c3d5a;">{$col_active_title}</h3>
				<p class="description" style="margin-bottom: 15px;">{$col_active_desc}</p>

				<div class="dwp-builder-active-tree-canvas js-dwp-active-tree-canvas" style="display: flex; flex-direction: column; gap: 8px; min-height: 480px; max-height: 650px; overflow-y: auto; padding: 15px; background: #f0f4f8; border: 2px dashed #cbd5e1; border-radius: 3px; position: relative;">
		HTML;

		if ( empty( $saved_tree ) ) {
			$empty_txt = esc_html__( 'Toolbar menu is empty. Add shortcuts from the pool and manage your dropdown matrix submenus nesting groups.', 'dwp-cf' );
			echo "<div class='dwp-canvas-placeholder-msg' style='color: #64748b; font-style: italic; text-align: center; padding: 50px 10px;'>{$empty_txt}</div>";
		} else {
			$flattened_pool = array();
			foreach ( $live_registry as $p_id => $shortcuts ) {
				if ( is_array( $shortcuts ) ) {
					foreach ( $shortcuts as $s ) {
						if ( $s instanceof ToolbarShortcut ) {
							$flattened_pool[ $s->id ] = array( 'shortcut' => $s, 'provider' => $p_id );
						}
					}
				}
			}
			$this->render_recursive_tree_branches( $saved_tree, $flattened_pool, 0 );
		}

		echo <<<HTML
				</div>
				<input type="hidden" name="dwp_menu_serialized_tree" id="js-dwp-serialized-tree-input" value="" />
			</div>

		</div>
		HTML;
	}

	/**
	 * Recursively renders nested hierarchy paths based on unique instance configurations maps.
	 *
	 * Supports mixed node rendering (both real cloned shortcuts and custom virtual submenu links).
	 *
	 * @since  1.0.0
	 * @param  array $tree_level      The active layout data hierarchy layer.
	 * @param  array $flattened_pool  Flattened reference maps collections indexing all live shortcuts DTOs.
	 * @param  int   $depth           Current indentation hierarchy depth index tracking markers.
	 * @return void
	 */
	private function render_recursive_tree_branches( array $tree_level, array $flattened_pool, int $depth ): void {
		foreach ( $tree_level as $instance_id => $meta ) {

			$type = $meta['type'] ?? 'shortcut';

			if ( 'submenu' === $type ) {
				$shortcut = new ToolbarShortcut(
					id:          $meta['id'] ?? $instance_id,
					title:       $meta['title'] ?? 'Custom Submenu',
					label:       __( 'Custom Submenu Header Group', 'dwp-cf' ),
					orientation: Orientation::BOTH,
					href:        $meta['href'] ?? '#'
				);
				$provider_id = 'custom_link';
			} else {
				$node_id   = $meta['id'] ?? '';
				$pool_item = $flattened_pool[ $node_id ] ?? null;

				if ( is_array( $pool_item ) ) {
					$shortcut = new ToolbarShortcut(
						id:          $pool_item['shortcut']->id,
						title:       $meta['title'] ?? $pool_item['shortcut']->title,
						label:       $pool_item['shortcut']->label,
						orientation: $pool_item['shortcut']->orientation,
						parent:      $pool_item['shortcut']->parent,
						href:        $meta['href'] ?? $pool_item['shortcut']->get_href()
					);
					$provider_id = $pool_item['provider'];
				} else {
					$shortcut    = new ToolbarShortcut(
							id: $node_id,
							title: $node_id,
							label: __( 'Inactive Component Residual Node', 'dwp-cf' ),
							orientation: Orientation::BOTH
						);
					$provider_id = 'unknown';
				}
			}

			$this->render_draggable_tree_node( $shortcut, $provider_id, $depth, true, $instance_id );

			if ( ! empty( $meta['children'] ) && is_array( $meta['children'] ) && $depth < 2 ) {
				$this->render_recursive_tree_branches( $meta['children'], $flattened_pool, $depth + 1 );
			}
		}
	}

	/**
	 * Outputs an individual dynamic node element container block, fully supporting multiple duplicate instances.
	 *
	 * FIXED OVERRIDE: Added data-href attribute tag onto the root face layout block to ensure dynamic script translation lookups.
	 *
	 * @since  1.0.0
	 * @param  ToolbarShortcut $shortcut    The shortcut node configuration DTO parameters.
	 * @param  string          $provider_id The dynamic component origin source identifier string handle.
	 * @param  int             $depth       Current nested hierarchical indentation depth layer index tracking.
	 * @param  bool            $is_active   True if rendering inside the canvas workspace, false inside pool lists.
	 * @param  string          $instance_id Optional. Cryptographic temporary execution identity string key.
	 * @return void
	 */
	private function render_draggable_tree_node( ToolbarShortcut $shortcut, string $provider_id, int $depth, bool $is_active, string $instance_id = '' ): void {
		$id_attr         = esc_attr( $shortcut->id );
		$p_id_attr       = esc_attr( $provider_id );
		$inst_attr       = esc_attr( $instance_id );
		$title_html      = esc_html( $shortcut->title );
		$href_attr       = esc_attr( $shortcut->get_href() );
		$friendly_source = esc_html( strtoupper( str_replace( array( '_', '-' ), ' ', $provider_id ) ) );

		$margin_left = $is_active ? ( $depth * 30 ) . 'px' : '0px';
		$border_left = ( 'custom_link' === $p_id_attr ) ? '4px solid #16a34a' : ( $is_active ? '4px solid #2271b1' : '4px solid #94a3b8' );
		$opacity     = ( 'unknown' === $p_id_attr ) ? '0.5' : '1';
		$type_attr   = ( 'custom_link' === $p_id_attr ) ? 'submenu' : 'shortcut';

		echo <<<HTML
		<div class="dwp-tree-node-item js-dwp-tree-node" data-id="{$id_attr}" data-instance="{$inst_attr}" data-type="{$type_attr}" data-provider="{$p_id_attr}" data-depth="{$depth}" data-href="{$href_attr}" draggable="true" style="background: #fff; border: 1px solid #cbd5e1; border-left: {$border_left}; margin-left: {$margin_left}; border-radius: 3px; display: flex; flex-direction: column; box-shadow: 0 1px 2px rgba(0,0,0,0.05); opacity: {$opacity}; transition: margin-left 0.15s ease, opacity 0.2s;">
			<div style="display: flex; align-items: center; justify-content: space-between; padding: 10px 15px; width: 100%; box-sizing: border-box;">
				<div style="display: flex; align-items: center; gap: 10px; max-width: 65%;">
		HTML;

		if ( ! $is_active ) {
			echo "<input type='checkbox' class='js-pool-checkbox' value='{$id_attr}' style='margin: 0;' />";
		}

		echo <<<HTML
					<div style="display: flex; flex-direction: column; gap: 2px;">
						<strong class="js-node-title" style="font-size: 13px; color: #1e293b;">{$title_html}</strong>
						<span style="font-size: 10px; background: #e2e8f0; color: #475569; padding: 1px 5px; border-radius: 3px; font-weight: 600; width: fit-content; margin-top: 3px; font-family: sans-serif;">{$friendly_source}</span>
					</div>
				</div>
				<div style="display: flex; align-items: center; gap: 10px; color: #94a3b8;">
		HTML;

		if ( $is_active ) {
			echo "<span class='dashicons dashicons-arrow-left-alt2 js-dwp-move-outward" . "' title='" . esc_attr__( 'Move Outward (Reduce Depth)', 'dwp-cf' ) . "' style='cursor: pointer; font-size: 16px; width: 16px; height: 16px; color: #475569;'></span>";
			echo "<span class='dashicons dashicons-arrow-right-alt2 js-dwp-move-inward" . "' title='" . esc_attr__( 'Move Inward (Increase Depth)', 'dwp-cf' ) . "' style='cursor: pointer; font-size: 16px; width: 16px; height: 16px; color: #2271b1;'></span>";
			echo "<span class='dashicons dashicons-arrow-down-alt2 js-dwp-toggle-edit' title='" . esc_attr__( 'Toggle configuration panel', 'dwp-cf' ) . "' style='cursor: pointer; font-size: 16px; width: 16px; height: 16px; color: #475569; transition: transform 0.2s; margin-left: 4px;'></span>";
			echo "<span class='dashicons dashicons-trash js-dwp-remove-node' title='" . esc_attr__( 'Remove node item', 'dwp-cf' ) . "' style='cursor: pointer; font-size: 16px; width: 16px; height: 16px; color: #ef4444;'></span>";
		}

		echo <<<HTML
					<span class="dashicons dashicons-editor-justify" style="font-size: 18px; width: 18px; height: 18px; margin-left: 2px;"></span>
				</div>
			</div>
		HTML;

		if ( $is_active ) {
			$lbl_title = esc_html__( 'Navigation Label Override', 'dwp-cf' );
			$lbl_url   = esc_html__( 'Resource URL Reference (Read-Only Copy Source)', 'dwp-cf' );
			$i18n_warn = esc_html__( '⚠️ Notice: Custom label overrides bypass system i18n core translation files.', 'dwp-cf' );

			$is_readonly_attr = ( 'custom_link' === $p_id_attr ) ? '' : 'readonly="readonly" style="background: #e2e8f0; color: #475569;"';

			echo <<<HTML
			<div class="js-dwp-edit-panel" style="display: none; background: #f8fafc; border-top: 1px solid #e2e8f0; padding: 15px; box-sizing: border-box; width: 100%;">
				<div style="margin-bottom: 10px;">
					<label style="display: block; font-size: 11px; font-weight: 600; color: #475569; margin-bottom: 4px;">{$lbl_title}</label>
					<input type="text" class="js-dwp-edit-title regular-text" value="{$title_html}" style="width: 100%; height: 28px; font-size: 12px;" />
					<span style="display: block; font-size: 10px; color: #b91c1c; margin-top: 4px; font-style: italic;">{$i18n_warn}</span>
				</div>
				<div>
					<label style="display: block; font-size: 11px; font-weight: 600; color: #475569; margin-bottom: 4px;">{$lbl_url}</label>
					<div style="display: flex; gap: 6px;">
						<input type="text" class="js-dwp-edit-url regular-text" id="url-{$inst_attr}" value="{$href_attr}" {$is_readonly_attr} style="flex: 1; height: 28px; font-size: 12px; font-family: monospace;" />
						<button type="button" class="button button-small" onclick="navigator.clipboard.writeText(document.getElementById('url-{$inst_attr}').value); alert('URL successfully copied to clipboard!');" style="height: 28px; line-height: 26px; font-size: 11px;">📋 Copy</button>
					</div>
				</div>
			</div>
			HTML;
		}

		echo '</div>';
	}

	#[Override]
	public function validate( array $posted_data ): bool|WP_Error {
		return true;
	}

	#[Override]
	public function save( array $posted_data ): string|bool {
		if ( ! current_user_can( 'manage_options' ) ) {
			$this->plugin->notices->add( 'error', __( 'Critical Error: You do not have sufficient permissions to modify the Admin Menu Builder settings.', 'dwp-cf' ) );
			return false;
		}

		$json_payload = $posted_data['dwp_menu_serialized_tree'] ?? '';
		if ( empty( $json_payload ) ) {
			$this->plugin->notices->add( 'warning', __( 'Save Aborted: No changes detected or serialization data stream is empty.', 'dwp-cf' ) );
			return true;
		}

		$decoded_tree = json_decode( wp_unslash( $json_payload ), true );
		if ( ! is_array( $decoded_tree ) ) {
			$this->plugin->notices->add( 'error', __( 'Save Failed: Transmitted layout configuration matrix map is corrupt.', 'dwp-cf' ) );
			return false;
		}

		$sanitized_tree  = $this->sanitize_recursive_tree_payload( $decoded_tree );

		$this->plugin->settings->save( $this->menu_builder_tree_option_token, $sanitized_tree );

		return __( 'Admin toolbar multi-level menu hierarchy blueprint saved successfully.', 'dwp-cf' );
	}

	/**
	 * Recursively sanitizes keys, attributes, and children nested inside the json payload layout tree.
	 */
	private function sanitize_recursive_tree_payload( array $tree_level ): array {
		$sanitized = array();
		foreach ( $tree_level as $instance_id => $meta ) {
			$clean_instance = sanitize_key( $instance_id );
			$type           = sanitize_text_field( $meta['type'] ?? 'shortcut' );

			$node_id = ( 'submenu' === $type ) ? sanitize_title( $meta['title'] ?? 'custom-link' ) : sanitize_key( $meta['id'] ?? '' );

			$sanitized[ $clean_instance ] = array(
				'id'       => $node_id,
				'type'     => $type,
				'title'    => sanitize_text_field( $meta['title'] ?? '' ),
				'href'     => sanitize_text_field( $meta['href'] ?? '#' ),
				'children' => ( ! empty( $meta['children'] ) && is_array( $meta['children'] ) )
					? $this->sanitize_recursive_tree_payload( $meta['children'] )
					: array(),
			);
		}
		return $sanitized;
	}

	/**
	 * Delivers custom context-aware action buttons to the host administration factory footer container.
	 *
	 * Satisfies the contract requirements overriding standard generic framework saving triggers.
	 *
	 * @since  1.0.0
	 * @return array<string, string> Dictionary indexing raw HTML button element blocks strings.
	 */
	#[Override]
	public function get_action_buttons(): array {
		$btn_save = esc_attr__( 'Save Toolbar Menu Order Hierarchy', 'dwp-cf' );

		return array(
			'save_order' => <<<HTML
				<button type="submit" name="dwp_vault_matrix_submit" value="1" class="button button-primary" style="height: 35px; background: #2271b1; border-color: #1d5c8f;">
					{$btn_save}
				</button>
			HTML,
		);
	}
}
