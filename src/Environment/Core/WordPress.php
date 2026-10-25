<?php
/**
 * WordPress Core Environment Data Component Guard.
 *
 * @package    DeWittePrins\CoreFunctionality
 * @subpackage Environment\Core
 * @author     Hans Schuijff <@hansschuijff>
 * @license    GPL-2.0
 * @since      1.0.0
 */

declare(strict_types=1);

namespace DeWittePrins\CoreFunctionality\Environment\Core;

use DeWittePrins\CoreFunctionality\Enums\Orientation;
use DeWittePrins\CoreFunctionality\DTO\AdminMenuBlock;
use DeWittePrins\CoreFunctionality\DTO\ToolbarShortcut;
use DeWittePrins\CoreFunctionality\Environment\Interfaces\EnvironmentInterface;
use DeWittePrins\CoreFunctionality\Environment\Interfaces\ToolbarProviderInterface;
use DeWittePrins\CoreFunctionality\Environment\Registers\AdminMenuRegister;
use Override;

use function __;
use function admin_url;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WordPress
 *
 * Serves as an infrastructure data token providing input configurations for core administration links.
 *
 * @since 1.0.0
 */
class WordPress implements EnvironmentInterface, ToolbarProviderInterface {

	/**
	 * Unique identifier token for this specific ecosystem component.
	 *
	 * @since 1.0.0
	 * @return string The blueprint identification token.
	 */
	#[Override]
	public function get_id(): string {
		return 'wordpress-core';
	}

	/**
	 * Evaluates the runtime environment state.
	 *
	 * Feeds the admin menu register; the toolbar nodes go to the catalogue through get_toolbar_nodes().
	 *
	 * @since 1.0.0
	 * @return bool Always true since the framework runs post core bootstrap.
	 */
	#[Override]
	public function is_ready(): bool {
		AdminMenuRegister::register( $this->get_id(), $this->get_menu_nodes() );

		return true;
	}

	/**
	 * Verifies if the dependency has active proof of life within the runtime environment.
	 *
	 * @since 1.0.0
	 * @return bool True if loaded proof is met, false otherwise.
	 */
	public function has_proof_of_life(): bool {
		return true;
	}

	/**
	 * The core is always installed.
	 *
	 * @since  1.0.0
	 * @return bool Always true.
	 */
	#[Override]
	public function is_installed(): bool {
		return true;
	}

	/**
	 * Compiles and returns the pool of native WordPress core toolbar shortcuts clustered by topic.
	 *
	 * @since  1.0.0
	 * @return ToolbarShortcut[] Array of clustered native core toolbar node DTO objects.
	 */
	#[Override]
	public function get_toolbar_nodes(): array {

		// CLUSTER 1: PLUGIN MANAGEMENT SHORTCUTS [5].
		$plugin_nodes = array(
			new ToolbarShortcut(
				id:      'cf-wp-plugins',
				title:   __( 'Installed Plugins', 'core-functionality' ),
				href:    admin_url( 'plugins.php' ),
				label:   __( 'Link to', 'core-functionality' ) . ' ' . __( 'Installed Plugins', 'core-functionality' ),
				orientation: Orientation::ADMIN,
			),
			new ToolbarShortcut(
				id:      'cf-wp-updates',
				title:   __( 'Updates', 'core-functionality' ),
				href:    admin_url( 'update-core.php' ),
				label:   __( 'Link to', 'core-functionality' ) . ' ' . __( 'Plugins & Themes Updates', 'core-functionality' ),
				orientation: Orientation::ADMIN,
			),
			new ToolbarShortcut(
				id:      'cf-wp-plugins-new',
				title:   __( 'Add Plugin', 'core-functionality' ),
				href:    admin_url( 'plugin-install.php' ),
				label:   __( 'Link to', 'core-functionality' ) . ' ' . __( 'Add Plugin', 'core-functionality' ),
				orientation: Orientation::ADMIN,
			),
		);

		// CLUSTER 2: SITE APPEARANCE & LAYOUT SHORTCUTS [5].
		$appearance_nodes = array(
			new ToolbarShortcut(
				id:      'cf-site-customizer',
				title:   __( 'Customizer', 'core-functionality' ),
				href:    admin_url( 'customize.php' ),
				label:   __( 'Link to', 'core-functionality' ) . ' ' . __( 'Customizer', 'core-functionality' ),
				orientation: Orientation::ADMIN,
			),
			new ToolbarShortcut(
				id:      'cf-site-widgets',
				title:   __( 'Widgets', 'core-functionality' ),
				href:    admin_url( 'widgets.php' ),
				label:   __( 'Link to', 'core-functionality' ) . ' ' . __( 'Widgets', 'core-functionality' ),
				orientation: Orientation::ADMIN,
			),
			new ToolbarShortcut(
				id:      'cf-site-menus',
				title:   __( 'Menus', 'core-functionality' ),
				href:    admin_url( 'nav-menus.php' ),
				label:   __( 'Link to', 'core-functionality' ) . ' ' . __( 'Menus', 'core-functionality' ),
				orientation: Orientation::ADMIN,
			),
			new ToolbarShortcut(
				id:      'cf-site-themes',
				title:   __( 'Themes', 'core-functionality' ),
				href:    admin_url( 'themes.php' ),
				label:   __( 'Link to', 'core-functionality' ) . ' ' . __( 'Themes', 'core-functionality' ),
				orientation: Orientation::ADMIN,
			),
		);

		// CLUSTER 3: CONTENT GENERATION (POSTS & PAGES) SHORTCUTS [5].
		$content_nodes = array(
			new ToolbarShortcut(
				id:      'cf-view-posts',
				title:   __( 'View Posts', 'core-functionality' ),
				href:    home_url( '/artikelen/' ),
				label:   __( 'Link to', 'core-functionality' ) . ' ' . __( 'View Posts', 'core-functionality' ),
				orientation: Orientation::FRONTEND,
			),
			new ToolbarShortcut(
				id:      'cf-edit-posts',
				title:   __( 'Edit Posts', 'core-functionality' ),
				href:    admin_url( 'edit.php' ),
				label:   __( 'Link to', 'core-functionality' ) . ' ' . __( 'Edit Posts', 'core-functionality' ),
				orientation: Orientation::ADMIN,
			),
			new ToolbarShortcut(
				id:      'cf-new-post',
				title:   __( 'New Post', 'core-functionality' ),
				href:    admin_url( 'post-new.php' ),
				label:   __( 'Link to', 'core-functionality' ) . ' ' . __( 'New Post', 'core-functionality' ),
				orientation: Orientation::ADMIN,
			),
			new ToolbarShortcut(
				id:      'cf-posts-categories',
				title:   __( 'Categories', 'core-functionality' ),
				href:    admin_url( 'edit-tags.php?taxonomy=category' ),
				label:   __( 'Link to', 'core-functionality' ) . ' ' . __( 'Categories', 'core-functionality' ),
				orientation: Orientation::ADMIN,
			),
			new ToolbarShortcut(
				id:      'cf-posts-tags',
				title:   __( 'Tags', 'core-functionality' ),
				href:    admin_url( 'edit-tags.php?taxonomy=post_tag' ),
				label:   __( 'Link to', 'core-functionality' ) . ' ' . __( 'Tags', 'core-functionality' ),
				orientation: Orientation::ADMIN,
			),
			new ToolbarShortcut(
				id:      'cf-reusable-blocks',
				title:   __( 'Reusable blocks', 'core-functionality' ),
				href:    admin_url( 'edit.php?post_type=wp_block' ),
				label:   __( 'Link to', 'core-functionality' ) . ' ' . __( 'Reusable blocks', 'core-functionality' ),
				orientation: Orientation::ADMIN,
			),
			new ToolbarShortcut(
				id:      'cf-pages',
				title:   __( 'Pages', 'core-functionality' ),
				href:    admin_url( 'edit.php?post_type=page' ),
				label:   __( 'Link to', 'core-functionality' ) . ' ' . __( 'Pages', 'core-functionality' ),
				orientation: Orientation::ADMIN,
			),
			new ToolbarShortcut(
				id:      'cf-comments',
				title:   __( 'Comments', 'core-functionality' ),
				href:    admin_url( 'edit-comments.php' ),
				label:   __( 'Link to', 'core-functionality' ) . ' ' . __( 'Comments', 'core-functionality' ),
				orientation: Orientation::ADMIN,
			),
			new ToolbarShortcut(
				id:      'cf-users',
				title:   __( 'Users', 'core-functionality' ),
				href:    admin_url( 'users.php' ),
				label:   __( 'Link to', 'core-functionality' ) . ' ' . __( 'Users', 'core-functionality' ),
				orientation: Orientation::ADMIN,
			),
		);

		// CLUSTER 4: CORE SETTINGS & TOOLS SHORTCUTS [5].
		// The link to this plugin's settings page comes from Core\CoreFunctionality.
		$system_nodes = array(
			new ToolbarShortcut(
				id:      'cf-view-site',
				title:   __( 'Frontpage', 'core-functionality' ),
				href:    home_url( '/' ),
				label:   __( 'Link to', 'core-functionality' ) . ' ' . __( 'Frontpage', 'core-functionality' ),
				orientation: Orientation::FRONTEND,
			),
			new ToolbarShortcut(
				id:      'cf-view-testimonials',
				title:   __( 'Testimonials', 'core-functionality' ),
				href:    home_url( '/schrijf-een-aanbeveling/' ),
				label:   __( 'Link to', 'core-functionality' ) . ' ' . __( 'Testimonials', 'core-functionality' ),
				orientation: Orientation::FRONTEND,
			),
		);

		// Merge all localized data clusters into a unified master array tree cleanly.
		return array_merge( $plugin_nodes, $appearance_nodes, $content_nodes, $system_nodes );
	}

	/**
	 * Compiles and returns the available pool of admin menu blocks the core can offer.
	 *
	 * @since 1.0.0
	 * @return AdminMenuBlock[] Collection of core sidebar menu blocks.
	 */
	protected function get_menu_nodes(): array {
		return array();
	}
}
