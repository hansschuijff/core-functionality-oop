<?php
/**
 * Essence Pro Child Theme Verification Guard.
 *
 * @package DeWittePrins\Environment\Themes
 * @since   4.0.0
 */

namespace DeWittePrins\Environment\Themes;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class EssencePro
 *
 * Assesses active styling inheritance profiles to verify if the running
 * design instance matches the specialized Essence Pro layout footprint.
 *
 * @since 4.0.0
 */
class EssencePro {

	/**
	 * Inspects theme stylesheet parameters to check directory keys.
	 *
	 * @since 4.0.0
	 * @return bool True if Essence Pro is the actively loaded layout layer, false otherwise.
	 * @see wp_get_theme()
	 */
	public function is_available(): bool {
		if ( ! function_exists( 'wp_get_theme' ) ) {
			return false;
		}

		return 'essence-pro' === wp_get_theme()->get_stylesheet();
	}
}
