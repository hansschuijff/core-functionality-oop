<?php
/**
 * Genesis Framework Structural Verification Guard.
 *
 * @package DeWittePrins\Environment\Themes
 * @since   4.0.0
 */

namespace DeWittePrins\Environment\Themes;

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
class AnyGenesis {

	/**
	 * Inspects theme template registry indices to confirm Genesis footprint.
	 *
	 * @since 4.0.0
	 * @return bool True if Genesis functions as the baseline framework layout, false otherwise.
	 * @see wp_get_theme()
	 */
	public function is_available(): bool {
		if ( ! function_exists( 'wp_get_theme' ) ) {
			return false;
		}

		$current_theme = wp_get_theme();

		return 'Genesis' === $current_theme->get( 'Template' ) || 'Genesis' === $current_theme->get( 'Name' );
	}
}
