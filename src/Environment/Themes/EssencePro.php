<?php
/**
 * Essence Pro Child Theme Verification Guard.
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
 * Class EssencePro
 *
 * Assesses active styling inheritance profiles to verify if the running
 * design instance matches the specialized Essence Pro layout footprint.
 *
 * @since 4.0.0
 */
class EssencePro implements EnvironmentInterface {

	/**
	 * Unique identifier token for this specific ecosystem component.
	 *
	 * @return string The blueprint identification token.
	 */
	public function get_id(): string {
		return 'essence_pro_theme';
	}

	/**
	 * Inspects theme stylesheet parameters to check directory keys.
	 *
	 * @since 4.0.0
	 * @return bool True if Essence Pro is the actively loaded layout layer, false otherwise.
	 * @see wp_get_theme()
	 */
	public function is_ready(): bool {
		if ( ! function_exists( 'wp_get_theme' ) ) {
			return false;
		}

		return 'essence-pro' === wp_get_theme()->get_stylesheet();
	}

	/**
	 * Get data with options related to this them to build menu's with.
	 *
	 * @return array
	 */
	public function get_node_data(): array {
		return array(
			'id'          => 'essence_pro_link',
			'title'       => __( '⚡ Essence Pro', 'core-functionality-dwp' ),
			'parent'      => 'top-secondary',
			'destination' => admin_url( 'themes.php' ),
		);
	}
}
