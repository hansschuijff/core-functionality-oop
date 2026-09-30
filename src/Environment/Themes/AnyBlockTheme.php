<?php
/**
 * Any Block Theme Verification Guard.
 *
 * @package    DeWittePrins\CoreFunctionality
 * @subpackage Environment\Themes
 * @author     Hans Schuijff <@hansschuijff>
 * @license    GPL-2.0
 * @since      1.0.0
 */

declare(strict_types=1);

namespace DeWittePrins\CoreFunctionality\Environment\Themes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use DeWittePrins\CoreFunctionality\Enums\Orientation;
use DeWittePrins\CoreFunctionality\DTO\ToolbarShortcut;
use DeWittePrins\CoreFunctionality\Environment\Themes\Services\ThemeInspector;
use Override;

use function __;
use function admin_url;

/**
 * Class AnyBlockTheme
 *
 * Evaluates whether the currently running system layout is securely powered
 * by any modern WordPress Full Site Editing (FSE) block theme architecture.
 *
 * @since 1.0.0
 */
class AnyBlockTheme extends AbstractTheme {

	/**
	 * Unique identifier token for this specific ecosystem component.
	 *
	 * @since 1.0.0
	 * @return string The blueprint identification token.
	 */
	#[Override]
	public function get_id(): string {
		return 'any_block_theme';
	}

	/**
	 * Overrides the default proof of life check to poll for FSE block theme presence.
	 *
	 * Bypasses the default lookup constraints since this applies globally to any FSE setup.
	 *
	 * @since 1.0.0
	 * @return bool True if an FSE block theme is active, false otherwise.
	 */
	#[Override]
	public function has_proof_of_life(): bool {
		return ThemeInspector::is_block_theme();
	}

	/**
	 * Compiles and returns the available pool of toolbar shortcuts this theme can offer.
	 *
	 * Transforms your link data into a type-safe ToolbarShortcut DTO block.
	 *
	 * @since 1.0.0
	 * @return ToolbarShortcut[] Collection of theme toolbar shortcut DTOs.
	 */
	#[Override]
	protected function get_toolbar_nodes(): array {
		return array(
			new ToolbarShortcut(
				id:      'cf-site-editor',
				title:   __( 'Site editor', 'dwp-cf' ),
				href:    admin_url( 'site-editor.php' ),
				label:   __( 'Link to', 'dwp-cf' ) . ' ' . __( 'Site editor', 'dwp-cf' ),
				orientation: Orientation::ADMIN,
			),
		);
	}

	/**
	 * Compiles and returns the available pool of admin menu blocks this theme can offer.
	 *
	 * @since 1.0.0
	 * @return \DeWittePrins\CoreFunctionality\DTO\AdminMenuBlock[] Collection of theme sidebar menu blocks.
	 */
	#[Override]
	protected function get_menu_nodes(): array {
		return array();
	}
}
