<?php
/**
 * AutomateWoo Verification Guard.
 *
 * @package DeWittePrins\CoreFunctionality\Environment\Plugins
 * @author  Hans Schuijff <@hansschuijff>
 * @license GPL-2.0
 * @since   1.0.0
 */

declare( strict_types=1 );

namespace DeWittePrins\CoreFunctionality\Environment\Plugins;

use DeWittePrins\CoreFunctionality\Enums\Orientation;
use DeWittePrins\CoreFunctionality\DTO\ToolbarShortcut;
use DeWittePrins\CoreFunctionality\Environment\DTO\ProofRequirement;
use Override;

use function __;
use function admin_url;

/**
 * Class Automatewoo
 *
 * Reports whether AutomateWoo is active and started. AutomateWoo replaces WooCommerce
 * Follow-Up Emails on Hans's sites.
 *
 * @since 1.0.0
 */
class Automatewoo extends AbstractPlugin {

	/**
	 * Returns the environment id of AutomateWoo.
	 *
	 * @since  1.0.0
	 * @return string The id, also the key in environment-index.php.
	 */
	#[Override]
	public function get_id(): string {
		return 'automatewoo';
	}

	/**
	 * Returns what must exist to prove AutomateWoo has started.
	 *
	 * @since  1.0.0
	 * @return ProofRequirement The proof criteria.
	 */
	#[Override]
	protected function get_proof_requirements(): ProofRequirement {
		// AutomateWoo only loads these (plugins_loaded 8) when its PHP, WordPress and WooCommerce
		// requirements are met, so they prove the plugin really started.
		return new ProofRequirement(
			classes:   'AutomateWoo',
			functions: 'AW'
		);
	}

	/**
	 * Returns the admin toolbar links AutomateWoo offers for the quick menu.
	 *
	 * @since  1.0.0
	 * @return ToolbarShortcut[] The toolbar links.
	 */
	#[Override]
	public function get_toolbar_nodes(): array {
		// The first node is the menu head; its id is in saved menus, so it keeps it. The others
		// follow AutomateWoo's own admin menu.
		return array(
			new ToolbarShortcut(
				id:          'cf-automatewoo-dashboard',
				title:       __( 'AutomateWoo', 'core-functionality' ),
				href:        admin_url( 'admin.php?page=automatewoo-dashboard' ),
				label:       __( 'Link to', 'core-functionality' ) . ' ' . __( 'AutomateWoo Dashboard', 'core-functionality' ),
				orientation: Orientation::ADMIN,
			),
			new ToolbarShortcut(
				id:          'cf-automatewoo-dashboard-page',
				title:       __( 'Dashboard', 'core-functionality' ),
				href:        admin_url( 'admin.php?page=automatewoo-dashboard' ),
				label:       __( 'Link to', 'core-functionality' ) . ' ' . __( 'AutomateWoo Dashboard', 'core-functionality' ),
				orientation: Orientation::ADMIN,
			),
			new ToolbarShortcut(
				id:          'cf-automatewoo-workflows',
				title:       __( 'Workflows', 'core-functionality' ),
				href:        admin_url( 'edit.php?post_type=aw_workflow' ),
				label:       __( 'Link to', 'core-functionality' ) . ' ' . __( 'AutomateWoo Workflows', 'core-functionality' ),
				orientation: Orientation::ADMIN,
			),
			new ToolbarShortcut(
				id:          'cf-automatewoo-logs',
				title:       __( 'Logs', 'core-functionality' ),
				href:        admin_url( 'admin.php?page=automatewoo-logs' ),
				label:       __( 'Link to', 'core-functionality' ) . ' ' . __( 'AutomateWoo Logs', 'core-functionality' ),
				orientation: Orientation::ADMIN,
			),
			new ToolbarShortcut(
				id:          'cf-automatewoo-queue',
				title:       __( 'Queue', 'core-functionality' ),
				href:        admin_url( 'admin.php?page=automatewoo-queue' ),
				label:       __( 'Link to', 'core-functionality' ) . ' ' . __( 'AutomateWoo Queue', 'core-functionality' ),
				orientation: Orientation::ADMIN,
			),
			new ToolbarShortcut(
				id:          'cf-automatewoo-carts',
				title:       __( 'Carts', 'core-functionality' ),
				href:        admin_url( 'admin.php?page=automatewoo-carts' ),
				label:       __( 'Link to', 'core-functionality' ) . ' ' . __( 'AutomateWoo Carts', 'core-functionality' ),
				orientation: Orientation::ADMIN,
			),
			new ToolbarShortcut(
				id:          'cf-automatewoo-guests',
				title:       __( 'Guests', 'core-functionality' ),
				href:        admin_url( 'admin.php?page=automatewoo-guests' ),
				label:       __( 'Link to', 'core-functionality' ) . ' ' . __( 'AutomateWoo Guests', 'core-functionality' ),
				orientation: Orientation::ADMIN,
			),
			new ToolbarShortcut(
				id:          'cf-automatewoo-opt-ins',
				title:       __( 'Opt-ins', 'core-functionality' ),
				href:        admin_url( 'admin.php?page=automatewoo-opt-ins' ),
				label:       __( 'Link to', 'core-functionality' ) . ' ' . __( 'AutomateWoo Opt-ins', 'core-functionality' ),
				orientation: Orientation::ADMIN,
			),
			new ToolbarShortcut(
				id:          'cf-automatewoo-reports',
				title:       __( 'Reports', 'core-functionality' ),
				href:        admin_url( 'admin.php?page=automatewoo-reports' ),
				label:       __( 'Link to', 'core-functionality' ) . ' ' . __( 'AutomateWoo Reports', 'core-functionality' ),
				orientation: Orientation::ADMIN,
			),
			new ToolbarShortcut(
				id:          'cf-automatewoo-tools',
				title:       __( 'Tools', 'core-functionality' ),
				href:        admin_url( 'admin.php?page=automatewoo-tools' ),
				label:       __( 'Link to', 'core-functionality' ) . ' ' . __( 'AutomateWoo Tools', 'core-functionality' ),
				orientation: Orientation::ADMIN,
			),
			new ToolbarShortcut(
				id:          'cf-automatewoo-settings',
				title:       __( 'Settings', 'core-functionality' ),
				href:        admin_url( 'admin.php?page=automatewoo-settings' ),
				label:       __( 'Link to', 'core-functionality' ) . ' ' . __( 'AutomateWoo Settings', 'core-functionality' ),
				orientation: Orientation::ADMIN,
			),
			new ToolbarShortcut(
				id:          'cf-automatewoo-preview',
				title:       __( 'Preview', 'core-functionality' ),
				href:        admin_url( 'admin.php?page=automatewoo-preview' ),
				label:       __( 'Link to', 'core-functionality' ) . ' ' . __( 'AutomateWoo Preview', 'core-functionality' ),
				orientation: Orientation::ADMIN,
			),
			new ToolbarShortcut(
				id:          'cf-automatewoo-data-upgrade',
				title:       __( 'AutomateWoo Data Update', 'core-functionality' ),
				href:        admin_url( 'admin.php?page=automatewoo-data-upgrade' ),
				label:       __( 'Link to', 'core-functionality' ) . ' ' . __( 'AutomateWoo Data Update', 'core-functionality' ),
				orientation: Orientation::ADMIN,
			),
			new ToolbarShortcut(
				id:          'cf-automatewoo-manual-workflow-runner',
				title:       __( 'Manual Workflow Runner', 'core-functionality' ),
				href:        admin_url( 'admin.php?page=wc-admin&path=/automatewoo/manual-workflow-runner' ),
				label:       __( 'Link to', 'core-functionality' ) . ' ' . __( 'AutomateWoo Manual Workflow Runner', 'core-functionality' ),
				orientation: Orientation::ADMIN,
			),
		);
	}

	/**
	 * Returns the admin menu blocks AutomateWoo offers (none yet).
	 *
	 * @since  1.0.0
	 * @return array Empty array.
	 */
	#[Override]
	protected function get_menu_nodes(): array {
		return array();
	}
}
