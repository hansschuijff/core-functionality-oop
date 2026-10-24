<?php
/**
 * Toolbar Provider Contract.
 *
 * @package    DeWittePrins\CoreFunctionality
 * @subpackage Environment\Interfaces
 * @author     Hans Schuijff <@hansschuijff>
 * @license    GPL-2.0
 * @since      1.0.0
 */

declare( strict_types=1 );

namespace DeWittePrins\CoreFunctionality\Environment\Interfaces;

use DeWittePrins\CoreFunctionality\DTO\ToolbarShortcut;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Interface ToolbarProviderInterface
 *
 * A guard that offers admin toolbar shortcuts to the menu builder catalogue. The catalogue holds
 * the shortcuts of every known plugin and theme, so get_toolbar_nodes() must also work when the
 * plugin is not active: anything that needs the plugin's own functions goes in a closure as href.
 *
 * @since 1.0.0
 */
interface ToolbarProviderInterface {

	/**
	 * Returns the admin toolbar shortcuts this provider offers.
	 *
	 * @since  1.0.0
	 * @return ToolbarShortcut[] The shortcuts.
	 */
	public function get_toolbar_nodes(): array;

	/**
	 * Checks whether the plugin or theme is installed (active or not).
	 *
	 * @since  1.0.0
	 * @return bool True if it is installed.
	 */
	public function is_installed(): bool;
}
