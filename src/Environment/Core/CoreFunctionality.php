<?php
/**
 * Core Functionality Toolbar Node Provider.
 *
 * @package    DeWittePrins\CoreFunctionality
 * @subpackage Environment\Core
 * @author     Hans Schuijff <@hansschuijff>
 * @license    GPL-2.0
 * @since      1.0.0
 */

declare(strict_types=1);

namespace DeWittePrins\CoreFunctionality\Environment\Core;

use DeWittePrins\CoreFunctionality\Admin\Services\Settings\AdminSettingsPage;
use DeWittePrins\CoreFunctionality\DTO\ToolbarShortcut;
use DeWittePrins\CoreFunctionality\Enums\Orientation;
use DeWittePrins\CoreFunctionality\Environment\Interfaces\ToolbarProviderInterface;
use Override;

use function __;
use function admin_url;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class CoreFunctionality
 *
 * Supplies the admin toolbar nodes of this plugin itself, such as the link to its settings page.
 * Unlike the guards it is no dependency and is not checked: the plugin is always active when it
 * runs, so the catalogue registers it as active, next to the WordPress core.
 *
 * @since 1.0.0
 */
class CoreFunctionality implements ToolbarProviderInterface {

	/**
	 * The provider id in the toolbar catalogue.
	 *
	 * @since  1.0.0
	 * @return string The id.
	 */
	public function get_id(): string {
		return 'core-functionality';
	}

	/**
	 * The plugin is installed: it is running.
	 *
	 * @since  1.0.0
	 * @return bool Always true.
	 */
	#[Override]
	public function is_installed(): bool {
		return true;
	}

	/**
	 * Returns the toolbar shortcuts of this plugin.
	 *
	 * @since  1.0.0
	 * @return ToolbarShortcut[] The shortcuts.
	 */
	#[Override]
	public function get_toolbar_nodes(): array {
		return array(
			new ToolbarShortcut(
				id:          'cf-settings-core-functionality',
				title:       __( 'Core Functionality', 'core-functionality' ),
				href:        admin_url( 'options-general.php?page=' . AdminSettingsPage::SLUG ),
				label:       __( 'Link to', 'core-functionality' ) . ' ' . __( 'Core functionality settings', 'core-functionality' ),
				orientation: Orientation::ADMIN,
			),
		);
	}
}
