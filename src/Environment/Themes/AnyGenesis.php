<?php
/**
 * Genesis Framework / Parent Theme Verification Guard.
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
	exit; // Exit if accessed directly.
}

use DeWittePrins\CoreFunctionality\Enums\Orientation;
use DeWittePrins\CoreFunctionality\DTO\ToolbarShortcut;
use DeWittePrins\CoreFunctionality\Environment\DTO\ProofRequirement;
use Override;

use function __;
use function admin_url;

/**
 * Class AnyGenesis
 *
 * Verifies theme asset deployment patterns evaluating if the running system layout
 * theme (or parent design scaffolding framework) is securely powered by Genesis.
 *
 * @since 1.0.0
 */
class AnyGenesis extends AbstractTheme {

	/**
	 * Unique identifier token for this specific ecosystem component.
	 *
	 * @since 1.0.0
	 * @return string The blueprint identification token.
	 */
	#[Override]
	public function get_id(): string {
		return 'any_genesis_theme';
	}

	/**
	 * Declares specific technical proof criteria required for framework verification.
	 *
	 * Defensively checks for core Genesis constants to guarantee the framework is fully loaded.
	 *
	 * @since 1.0.0
	 * @return ProofRequirement The structured proof criteria object.
	 */
	#[Override]
	protected function get_proof_requirements(): ProofRequirement {
		return new ProofRequirement(
			constants: 'PARENT_THEME_VERSION' // Confirms Genesis engine is initialized and present
		);
	}

	/**
	 * Compiles and returns the available pool of toolbar shortcuts this framework can offer.
	 *
	 * Transforms your custom link data directly into a type-safe ToolbarShortcut DTO block.
	 *
	 * @since 1.0.0
	 * @return ToolbarShortcut[] Collection of theme toolbar shortcut DTOs.
	 */
	#[Override]
	protected function get_toolbar_nodes(): array {
		return array(
			new ToolbarShortcut(
				id:      'cf-genesis-theme-settings',
				title:   __( 'Genesis Framework', 'dwp-cf' ),
				href:    admin_url( 'admin.php?page=genesis' ),
				label:   __( 'Link to', 'dwp-cf' ) . ' ' . __( 'Genesis Framework', 'dwp-cf' ),
				orientation: Orientation::ADMIN,
			),
		);
	}

	/**
	 * Compiles and returns the available pool of admin menu blocks this framework can offer.
	 *
	 * @since 1.0.0
	 * @return \DeWittePrins\CoreFunctionality\DTO\AdminMenuBlock[] Collection of theme sidebar menu blocks.
	 */
	#[Override]
	protected function get_menu_nodes(): array {
		return array();
	}
}
