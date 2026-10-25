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
use DeWittePrins\CoreFunctionality\Modules\AdminToolbar\AdminToolbar;
use DeWittePrins\CoreFunctionality\Enums\Orientation;
use DeWittePrins\CoreFunctionality\Enums\ProviderStatus;
use DeWittePrins\CoreFunctionality\DTO\ToolbarShortcut;
use DeWittePrins\CoreFunctionality\Plugin;
use WP_Error;
use Override;

// Import global PHP and WordPress core abstraction functions.
use function __;
use function esc_attr__;
use function esc_html__;
use function esc_url_raw;
use function is_array;
use function json_decode;
use function plugins_url;
use function sanitize_key;
use function sanitize_text_field;
use function sanitize_title;
use function str_replace;
use function ucwords;
use function current_user_can;
use function wp_json_encode;
use function wp_unslash;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class MenuBuilderSettings
 *
 * The settings tab where the toolbar menu is composed from the shortcuts of all known plugins,
 * themes and the core. The tab renders a skeleton and the data as JSON; menu-builder-settings.js
 * builds the pool and the menu tree from that data.
 *
 * The saved tree holds per node: id, type (shortcut or submenu), title (empty: the shortcut's own,
 * translated title), href (submenus only), visibility (admin, frontend or both) and children.
 *
 * @since 1.0.0
 */
class MenuBuilderSettings implements SettingsSectionInterface {

	/**
	 * The maximum depth of the menu (0-based), so three levels.
	 *
	 * @var int
	 */
	private const MAX_DEPTH = 2;

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

	/**
	 * Returns the id of this settings section (used in the page URL).
	 *
	 * @since  1.0.0
	 * @return string The section id.
	 */
	#[Override]
	public static function get_id(): string {
		return 'cf-menu-builder-settings';
	}

	/**
	 * Returns the dashicon class shown next to the tab title.
	 *
	 * @since  1.0.0
	 * @return string The dashicon class.
	 */
	#[Override]
	public static function get_icon(): string {
		return 'dashicons-menu-alt';
	}

	/**
	 * Returns the title of this settings tab.
	 *
	 * @since  1.0.0
	 * @return string The tab title.
	 */
	#[Override]
	public static function get_name(): string {
		return __( 'Admin Toolbar Menu Builder', 'core-functionality' );
	}

	/**
	 * Returns the description shown at the top of this tab.
	 *
	 * @since  1.0.0
	 * @return string The description.
	 */
	#[Override]
	public static function get_description(): string {
		return __( 'Compose the shortcuts menu of the admin toolbar from the links of all known plugins and themes. Links of plugins that are not active are kept, but only shown once the plugin is active.', 'core-functionality' );
	}

	/**
	 * Returns the stylesheets this tab needs.
	 *
	 * @since  1.0.0
	 * @return array The stylesheets to enqueue.
	 */
	#[Override]
	public static function get_stylesheets(): array {
		return array(
			'dwp-cf-admin-toolbar-menu-builder-css' => plugins_url( 'assets/css/menu-builder-settings.css', __FILE__ ),
		);
	}

	/**
	 * Returns the scripts this tab needs.
	 *
	 * @since  1.0.0
	 * @return array The scripts to enqueue.
	 */
	#[Override]
	public static function get_scripts(): array {
		return array(
			'dwp-cf-admin-toolbar-menu-builder-js' => plugins_url( 'assets/js/menu-builder-settings.js', __FILE__ ),
		);
	}

	/**
	 * Renders the content of this tab.
	 *
	 * @since  1.0.0
	 * @param  bool $defaults_only Whether to show the default values instead of the saved ones.
	 * @return void
	 */
	#[Override]
	public function render( bool $defaults_only = false ): void {
		$saved_tree = $defaults_only
			? $this->plugin->settings->get_default( $this->menu_builder_tree_option_token, AdminToolbar::get_id() )
			: $this->plugin->settings->get_whole( $this->menu_builder_tree_option_token, AdminToolbar::get_id(), array() );
		if ( ! is_array( $saved_tree ) ) {
			$saved_tree = array();
		}

		$data = array(
			'maxDepth'  => self::MAX_DEPTH,
			'i18n'      => $this->get_script_strings(),
			'providers' => $this->get_provider_rows(),
			'pool'      => $this->get_pool_rows(),
			'tree'      => array(),
		);
		$this->flatten_saved_tree( $saved_tree, $this->get_catalogue_index(), 0, $data['tree'] );

		$pool_title = esc_html__( 'Available shortcuts', 'core-functionality' );
		$pool_desc  = esc_html__( 'Shortcuts of all known plugins and themes, per source. Check them and add them, or drag them into the menu.', 'core-functionality' );
		$tree_title = esc_html__( 'Toolbar menu (max. 3 levels)', 'core-functionality' );
		$tree_desc  = esc_html__( 'Drag to reorder and use the arrows to indent. Admin, Both and Site set where a node is shown; a node is only shown where its parents are shown too.', 'core-functionality' );
		$status     = esc_html( $this->get_default_status_text() );
		$json       = wp_json_encode( $data, JSON_HEX_TAG | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE );

		// The data is JSON in a script block; JSON_HEX_TAG keeps it from closing the block.
		// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
		echo <<<HTML
		<div class="dwp-mb-wrap">
		<div class="dwp-menu-builder js-dwp-menu-builder">
			<div class="dwp-mb-column">
				<h3>{$pool_title}</h3>
				<p class="description">{$pool_desc}</p>
				<div class="dwp-mb-pool-tools js-dwp-mb-pool-tools"></div>
				<div class="dwp-mb-pool-actions js-dwp-mb-pool-actions"></div>
				<div class="dwp-mb-pool js-dwp-mb-pool"></div>
			</div>
			<div class="dwp-mb-column dwp-mb-column--tree">
				<h3>{$tree_title}</h3>
				<p class="description">{$tree_desc}</p>
				<p class="dwp-mb-status">{$status}</p>
				<div class="dwp-mb-tree js-dwp-mb-tree"></div>
				<input type="hidden" name="dwp_menu_serialized_tree" class="js-dwp-mb-serialized" value="" />
			</div>
			<script type="application/json" class="js-dwp-mb-data">{$json}</script>
		</div>
		</div>
		HTML;
		// phpcs:enable WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Says whether the site uses the default menu that comes with the plugin.
	 *
	 * @since  1.0.0
	 * @return string The status line above the tree.
	 */
	private function get_default_status_text(): string {
		$settings = $this->plugin->settings;
		$default  = $settings->get_default( $this->menu_builder_tree_option_token, AdminToolbar::get_id() );

		if ( ! is_array( $default ) ) {
			return $settings->can_save_default( $this->menu_builder_tree_option_token, AdminToolbar::get_id() )
				? __( 'There is no default menu yet. "Save as default" makes this menu the starting point for other sites: it goes into the plugin and along with the next release.', 'core-functionality' )
				: __( 'There is no default menu yet. A default can be saved on a site where the plugin folder is writable, such as the development site.', 'core-functionality' );
		}

		$saved = $settings->get_whole( $this->menu_builder_tree_option_token, AdminToolbar::get_id(), array() );

		return $saved === $default
			? __( 'This menu is the default that comes with the plugin.', 'core-functionality' )
			: __( 'This menu differs from the default that comes with the plugin. "Restore default" takes the default again.', 'core-functionality' );
	}

	/**
	 * Returns the providers for the source filter, with their status.
	 *
	 * @since  1.0.0
	 * @return array<int, array{id: string, name: string, status: string}> The providers.
	 */
	private function get_provider_rows(): array {
		$rows = array();
		foreach ( AdminToolbarRegister::get_all() as $provider_id => $shortcuts ) {
			if ( empty( $shortcuts ) ) {
				continue;
			}

			$rows[] = array(
				'id'     => $provider_id,
				'name'   => $this->get_provider_name( $provider_id ),
				'status' => ( AdminToolbarRegister::get_status( $provider_id ) ?? ProviderStatus::NOT_INSTALLED )->value,
			);
		}

		return $rows;
	}

	/**
	 * Returns the pool: a custom submenu and every shortcut in the catalogue.
	 *
	 * @since  1.0.0
	 * @return array<int, array<string, mixed>> The pool rows.
	 */
	private function get_pool_rows(): array {
		$rows = array(
			array(
				'id'           => 'custom-submenu',
				'type'         => 'submenu',
				'provider'     => '',
				'providerName' => __( 'Custom', 'core-functionality' ),
				'status'       => ProviderStatus::ACTIVE->value,
				'kind'         => Orientation::BOTH->value,
				'defaultTitle' => __( 'Submenu or custom link', 'core-functionality' ),
				'label'        => __( 'A parent for other nodes, or a link of your own', 'core-functionality' ),
				'href'         => '',
			),
		);

		foreach ( AdminToolbarRegister::get_all() as $provider_id => $shortcuts ) {
			foreach ( $shortcuts as $shortcut ) {
				$rows[] = $this->get_shortcut_row( $shortcut, $provider_id );
			}
		}

		return $rows;
	}

	/**
	 * Returns the data of a shortcut for the script.
	 *
	 * @since  1.0.0
	 * @param  ToolbarShortcut $shortcut    The shortcut.
	 * @param  string          $provider_id The plugin, theme or core that offers it.
	 * @return array<string, mixed> The row.
	 */
	private function get_shortcut_row( ToolbarShortcut $shortcut, string $provider_id ): array {
		return array(
			'id'           => $shortcut->id,
			'type'         => 'shortcut',
			'provider'     => $provider_id,
			'providerName' => $this->get_provider_name( $provider_id ),
			'status'       => ( AdminToolbarRegister::get_status( $provider_id ) ?? ProviderStatus::NOT_INSTALLED )->value,
			'kind'         => $shortcut->orientation->value,
			'defaultTitle' => $shortcut->title,
			'label'        => $shortcut->label,
			'href'         => $shortcut->get_href(),
		);
	}

	/**
	 * Turns the saved tree into a flat list in display order, with the depth of each node.
	 *
	 * @since  1.0.0
	 * @param  array $tree_level One level of the saved tree.
	 * @param  array $index      The catalogue: node id => array( shortcut, provider ).
	 * @param  int   $depth      The depth of this level.
	 * @param  array $rows       The list, filled by reference.
	 * @return void
	 */
	private function flatten_saved_tree( array $tree_level, array $index, int $depth, array &$rows ): void {
		foreach ( $tree_level as $instance_id => $meta ) {
			if ( ! is_array( $meta ) ) {
				continue;
			}

			$type  = $meta['type'] ?? 'shortcut';
			$id    = (string) ( $meta['id'] ?? '' );
			$title = (string) ( $meta['title'] ?? '' );

			if ( 'submenu' === $type ) {
				$row = array(
					'id'           => $id,
					'type'         => 'submenu',
					'provider'     => '',
					'providerName' => __( 'Custom', 'core-functionality' ),
					'status'       => ProviderStatus::ACTIVE->value,
					'kind'         => Orientation::BOTH->value,
					'defaultTitle' => '',
					'label'        => '',
					'href'         => '#' === ( $meta['href'] ?? '' ) ? '' : (string) ( $meta['href'] ?? '' ),
				);
			} elseif ( isset( $index[ $id ] ) ) {
				$row = $this->get_shortcut_row( $index[ $id ]['shortcut'], $index[ $id ]['provider'] );
				// A title equal to the shortcut's own title is no override; it keeps following the translation.
				$title = $title === $row['defaultTitle'] ? '' : $title;
			} else {
				$row = array(
					'id'           => $id,
					'type'         => 'shortcut',
					'provider'     => '',
					'providerName' => __( 'Unknown', 'core-functionality' ),
					'status'       => 'unknown',
					'kind'         => Orientation::BOTH->value,
					'defaultTitle' => '' !== $title ? $title : $id,
					'label'        => __( 'No known plugin or theme offers this shortcut any more.', 'core-functionality' ),
					'href'         => '',
				);
			}

			$rows[] = $row + array(
				'instance'   => (string) $instance_id,
				'title'      => $title,
				'visibility' => ( Orientation::tryFrom( (string) ( $meta['visibility'] ?? '' ) ) ?? Orientation::BOTH )->value,
				'depth'      => $depth,
			);

			if ( ! empty( $meta['children'] ) && is_array( $meta['children'] ) && $depth < self::MAX_DEPTH ) {
				$this->flatten_saved_tree( $meta['children'], $index, $depth + 1, $rows );
			}
		}
	}

	/**
	 * Returns every shortcut in the catalogue by node id, with its provider.
	 *
	 * @since  1.0.0
	 * @return array<string, array{shortcut: ToolbarShortcut, provider: string}> The index.
	 */
	private function get_catalogue_index(): array {
		$index = array();
		foreach ( AdminToolbarRegister::get_all() as $provider_id => $shortcuts ) {
			foreach ( $shortcuts as $shortcut ) {
				$index[ $shortcut->id ] ??= array(
					'shortcut' => $shortcut,
					'provider' => $provider_id,
				);
			}
		}

		return $index;
	}

	/**
	 * Returns a readable name for a provider id.
	 *
	 * @since  1.0.0
	 * @param  string $provider_id The plugin, theme or core id.
	 * @return string The name.
	 */
	private function get_provider_name( string $provider_id ): string {
		if ( 'wordpress-core' === $provider_id ) {
			return 'WordPress';
		}

		return ucwords( str_replace( '-', ' ', $provider_id ) );
	}

	/**
	 * Returns the strings the script shows.
	 *
	 * @since  1.0.0
	 * @return array<string, string> Key => translated string.
	 */
	private function get_script_strings(): array {
		return array(
			'treeLabel'             => __( 'Toolbar menu', 'core-functionality' ),
			'shortcutsButton'       => __( 'Keyboard shortcuts', 'core-functionality' ),
			'shortcutsTitle'        => __( 'Keyboard shortcuts in the menu', 'core-functionality' ),
			'shortcutsNote'         => __( 'Click an item or Tab into the menu to start. When the focused item is selected, moving, indenting and removing act on the whole selection, each item with the items below it.', 'core-functionality' ),
			'shortcuts'             => $this->get_shortcut_rows(),
			'close'                 => __( 'Close', 'core-functionality' ),
			'selectBelow'           => __( 'Include items below', 'core-functionality' ),
			'selectBelowTip'        => __( 'Also select the items below the selected items (Ctrl-click selects an item with the items below it).', 'core-functionality' ),
			'search'                => __( 'Search shortcuts', 'core-functionality' ),
			'onlyActive'            => __( 'Only active', 'core-functionality' ),
			'collapse'              => __( 'Collapse', 'core-functionality' ),
			'expand'                => __( 'Expand', 'core-functionality' ),
			'addSelectedTip'        => __( 'Adds below the item selected in the menu (inside it when it is a submenu without a link), or at the end.', 'core-functionality' ),
			'statusActive'          => __( 'Active', 'core-functionality' ),
			'statusInactive'        => __( 'Inactive', 'core-functionality' ),
			'statusMissing'         => __( 'Not installed', 'core-functionality' ),
			'statusUnknown'         => __( 'Unknown', 'core-functionality' ),
			'kindAdmin'             => __( 'Admin', 'core-functionality' ),
			'kindFrontend'          => __( 'Front', 'core-functionality' ),
			'kindAdminTip'          => __( 'Links to an admin page', 'core-functionality' ),
			'kindFrontendTip'       => __( 'Links to a page on the site', 'core-functionality' ),
			'visibleAdmin'          => __( 'Only on admin pages', 'core-functionality' ),
			'visibleFrontend'       => __( 'Only on the front end', 'core-functionality' ),
			'visibleBoth'           => __( 'On admin pages and the front end', 'core-functionality' ),
			'visibleBothShort'      => __( 'Both', 'core-functionality' ),
			/* translators: %s: Admin or Front, the restriction of a parent node. */
			'inheritedVisibility'   => __( 'parent: %s', 'core-functionality' ),
			/* translators: %s: Admin or Front, the node's own visibility. */
			'visibilityConflict'    => __( 'Its own setting (%s) conflicts with a parent, so this node is never shown.', 'core-functionality' ),
			'visibility'            => __( 'Show', 'core-functionality' ),
			'addSelected'           => __( 'Add selected', 'core-functionality' ),
			'selectAll'             => __( 'Select all visible', 'core-functionality' ),
			'selectFirst'           => __( 'Select at least one shortcut first.', 'core-functionality' ),
			'emptyTree'             => __( 'The menu is empty. Add shortcuts from the list.', 'core-functionality' ),
			'title'                 => __( 'Title', 'core-functionality' ),
			'titleHint'             => __( 'Leave empty to use the title of the shortcut, which follows the translation.', 'core-functionality' ),
			'submenuTitle'          => __( 'Submenu', 'core-functionality' ),
			'url'                   => __( 'URL', 'core-functionality' ),
			'urlHint'               => __( 'Leave empty for a submenu without a link of its own; it is hidden when none of its items is shown.', 'core-functionality' ),
			'copy'                  => __( 'Copy', 'core-functionality' ),
			'copied'                => __( 'Copied', 'core-functionality' ),
			'moveOut'               => __( 'Move out (one level up)', 'core-functionality' ),
			'moveIn'                => __( 'Move in (one level down)', 'core-functionality' ),
			'edit'                  => __( 'Settings', 'core-functionality' ),
			'remove'                => __( 'Remove', 'core-functionality' ),
			/* translators: %d: number of items below the node. */
			'removeConfirm'         => __( 'This node has %d items below it. Remove the node together with those items?', 'core-functionality' ),
			'inactiveHint'          => __( 'Shown once this plugin or theme is active.', 'core-functionality' ),
			'select'                => __( 'Select (Shift-click: a range, Ctrl-click: with the items below)', 'core-functionality' ),
			/* translators: %d: number of selected items. */
			'selectedCount'         => __( '%d selected', 'core-functionality' ),
			'resetTitles'           => __( 'Reset titles', 'core-functionality' ),
			'resetTitlesTip'        => __( 'Use the shortcuts\' own titles again, which follow the translation.', 'core-functionality' ),
			'clearSelection'        => __( 'Clear selection', 'core-functionality' ),
			/* translators: %d: number of items that will be removed. */
			'removeSelectedConfirm' => __( 'Remove %d items (the selected items and the items below them)?', 'core-functionality' ),
			'restoreConfirm'        => __( 'Replace this menu by the default that comes with the plugin? Changes made on this site are lost.', 'core-functionality' ),
		);
	}

	/**
	 * Returns the keyboard shortcuts of the menu tree for the overview dialog.
	 *
	 * @since  1.0.0
	 * @return array<int, array{0: string, 1: string}> Keys => what they do.
	 */
	private function get_shortcut_rows(): array {
		$space = __( 'Space', 'core-functionality' );

		return array(
			array( '↑ / ↓', __( 'Move the focus to the previous or next item', 'core-functionality' ) ),
			array( 'Home / End', __( 'Move the focus to the first or last item', 'core-functionality' ) ),
			array( 'Shift + ↑ / ↓', __( 'Move the focus and select along', 'core-functionality' ) ),
			array( $space, __( 'Select or deselect the item', 'core-functionality' ) ),
			array( 'Shift + ' . $space, __( 'Select from the last selected item to here', 'core-functionality' ) ),
			array( 'Ctrl + ' . $space, __( 'Select or deselect the item with the items below it', 'core-functionality' ) ),
			array( 'Alt + ↑ / ↓', __( 'Move up or down, past the neighbouring item', 'core-functionality' ) ),
			array( 'Alt + ← / →', __( 'Move out or in one level', 'core-functionality' ) ),
			array( '← / →', __( 'Collapse or expand; on an item without children, ← goes to its parent', 'core-functionality' ) ),
			array( 'Enter', __( 'Open or close the settings of the item', 'core-functionality' ) ),
			array( 'Delete', __( 'Remove, with the items below it', 'core-functionality' ) ),
			array( 'Esc', __( 'Clear the selection', 'core-functionality' ) ),
			array( '?', __( 'Show this overview', 'core-functionality' ) ),
		);
	}

	/**
	 * Validates the submitted form data before it is saved.
	 *
	 * @since  1.0.0
	 * @param  array $posted_data The submitted form data.
	 * @return bool|WP_Error True when valid, an error otherwise.
	 */
	#[Override]
	public function validate( array $posted_data ): bool|WP_Error {
		return true;
	}

	/**
	 * Saves the submitted form data.
	 *
	 * @since  1.0.0
	 * @param  array $posted_data The submitted form data.
	 * @return string|bool A success message, true, or false on failure.
	 */
	#[Override]
	public function save( array $posted_data ): string|bool {
		if ( ! current_user_can( 'manage_options' ) ) {
			$this->plugin->notices->add( 'error', __( 'Critical Error: You do not have sufficient permissions to modify the Admin Menu Builder settings.', 'core-functionality' ) );
			return false;
		}

		$action = sanitize_key( (string) ( $posted_data['dwp_mb_action'] ?? 'save' ) );

		// Removing the saved menu makes the site follow the default again.
		if ( 'restore-default' === $action ) {
			$this->plugin->settings->reset( $this->menu_builder_tree_option_token );
			return __( 'The default menu has been restored.', 'core-functionality' );
		}

		$json_payload = $posted_data['dwp_menu_serialized_tree'] ?? '';
		if ( empty( $json_payload ) ) {
			$this->plugin->notices->add( 'warning', __( 'Save Aborted: No changes detected or serialization data stream is empty.', 'core-functionality' ) );
			return true;
		}

		$decoded_tree = json_decode( wp_unslash( $json_payload ), true );
		if ( ! is_array( $decoded_tree ) ) {
			$this->plugin->notices->add( 'error', __( 'Save Failed: Transmitted layout configuration matrix map is corrupt.', 'core-functionality' ) );
			return false;
		}

		$sanitized_tree = $this->sanitize_recursive_tree_payload( $decoded_tree, $this->get_catalogue_index(), 0 );

		$this->plugin->settings->save( $this->menu_builder_tree_option_token, $sanitized_tree );

		if ( 'save-default' === $action ) {
			$written = $this->plugin->settings->save_default(
				$this->menu_builder_tree_option_token,
				AdminToolbar::get_id(),
				$sanitized_tree,
				'Default menu of the admin toolbar menu builder, written by "Save as default" on its settings tab.'
			);

			if ( ! $written ) {
				$this->plugin->notices->add( 'error', __( 'The menu is saved, but not as the default: the settings folder of the plugin cannot be written on this site.', 'core-functionality' ) );
				return false;
			}

			// The site now follows the default, like every other site that gets it with a release.
			$this->plugin->settings->reset( $this->menu_builder_tree_option_token );

			return __( 'The menu has been saved as the default (settings/modules/admin-toolbar/menu-builder.php). Commit that file to take the menu along with the next release.', 'core-functionality' );
		}

		return __( 'The toolbar menu has been saved.', 'core-functionality' );
	}

	/**
	 * Sanitizes one level of the submitted tree, and its children.
	 *
	 * A shortcut's title is only kept when it differs from the shortcut's own title, and a
	 * shortcut never keeps a URL: both then follow the plugin (translations, changed URLs).
	 *
	 * @since  1.0.0
	 * @param  array $tree_level One level of the submitted menu tree.
	 * @param  array $index      The catalogue: node id => array( shortcut, provider ).
	 * @param  int   $depth      The depth of this level.
	 * @return array             The sanitized level.
	 */
	private function sanitize_recursive_tree_payload( array $tree_level, array $index, int $depth ): array {
		$sanitized = array();
		foreach ( $tree_level as $instance_id => $meta ) {
			if ( ! is_array( $meta ) ) {
				continue;
			}

			$type  = 'submenu' === ( $meta['type'] ?? '' ) ? 'submenu' : 'shortcut';
			$title = sanitize_text_field( (string) ( $meta['title'] ?? '' ) );

			if ( 'submenu' === $type ) {
				$title   = '' !== $title ? $title : __( 'Submenu', 'core-functionality' );
				$node_id = sanitize_title( $title );
				$href    = esc_url_raw( (string) ( $meta['href'] ?? '' ) );
			} else {
				$node_id = sanitize_key( (string) ( $meta['id'] ?? '' ) );
				$href    = '';
				if ( isset( $index[ $node_id ] ) && $title === $index[ $node_id ]['shortcut']->title ) {
					$title = '';
				}
			}

			$sanitized[ sanitize_key( (string) $instance_id ) ] = array(
				'id'         => $node_id,
				'type'       => $type,
				'title'      => $title,
				'href'       => $href,
				'visibility' => ( Orientation::tryFrom( (string) ( $meta['visibility'] ?? '' ) ) ?? Orientation::BOTH )->value,
				'children'   => ( ! empty( $meta['children'] ) && is_array( $meta['children'] ) && $depth < self::MAX_DEPTH )
					? $this->sanitize_recursive_tree_payload( $meta['children'], $index, $depth + 1 )
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
		$settings = $this->plugin->settings;
		$token    = $this->menu_builder_tree_option_token;
		$module   = AdminToolbar::get_id();
		$btn_save = esc_html__( 'Save menu', 'core-functionality' );

		$buttons = array(
			'save_order' => <<<HTML
				<button type="submit" name="dwp_vault_matrix_submit" value="1" class="button button-primary button-large">{$btn_save}</button>
			HTML,
		);

		if ( $settings->can_save_default( $token, $module ) ) {
			$label = esc_html__( 'Save as default', 'core-functionality' );
			$tip   = esc_attr__( 'Saves the menu and writes it into the plugin as the default for other sites.', 'core-functionality' );

			$buttons['save_default'] = <<<HTML
				<button type="submit" name="dwp_mb_action" value="save-default" class="button button-large" title="{$tip}">{$label}</button>
			HTML;
		}

		if ( null !== $settings->get_default( $token, $module ) && $settings->has_saved( $token ) ) {
			$label = esc_html__( 'Restore default', 'core-functionality' );
			$tip   = esc_attr__( 'Replaces this menu by the default that comes with the plugin.', 'core-functionality' );

			$buttons['restore_default'] = <<<HTML
				<button type="submit" name="dwp_mb_action" value="restore-default" class="button button-large js-dwp-mb-restore" title="{$tip}">{$label}</button>
			HTML;
		}

		return $buttons;
	}
}
