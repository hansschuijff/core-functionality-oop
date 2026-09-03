<?php
/**
 * Genesis Framework Structural Verification Guard.
 *
 * @package DeWittePrins\CoreFunctionality\Environment\Themes
 * @since   4.0.0
 */

namespace DeWittePrins\CoreFunctionality\Environment\Themes;

use DeWittePrins\CoreFunctionality\Contracts\EnvironmentInterface;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class AnyGenesis
 *
 * Verifies theme asset deployment patterns evaluating if the running system layout
 * theme (or parent design scaffolding framework) is securely powered by Genesis.
 *
 * @since 4.0.0
 */
class AnyGenesis implements EnvironmentInterface {

	/**
	 * Unique identifier token for this specific ecosystem component.
	 *
	 * @return string The blueprint identification token.
	 */
	public function get_id(): string {
		return 'any_genesis_theme';
	}

	/**
	 * Inspects theme template registry indices to confirm Genesis footprint.
	 *
	 * @since 4.0.0
	 * @return bool True if Genesis functions as the baseline framework layout, false otherwise.
	 * @see wp_get_theme()
	 */
	public function is_ready(): bool {
		if ( ! function_exists( 'wp_get_theme' ) ) {
			return false;
		}

		$current_theme = wp_get_theme();

		return 'Genesis' === $current_theme->get( 'Template' ) || 'Genesis' === $current_theme->get( 'Name' );
	}

	/**
	 * Returns custom administration bar node attributes for immediate rendering.
	 *
	 * Called dynamically by the orchestrator registry only when is_ready returns true.
	 *
	 * @return array Required layout keys mapping ID, title, parent, and destination slug.
	 */
	public function get_node_data(): array {
		return array(
			'id'          => 'any_genesis_link',
			'title'       => __( '⚡ Any Genesis', 'core-functionality-dwp' ),
			'parent'      => 'top-secondary',
			'destination' => admin_url( 'themes.php' ),
		);
	}
}
