<?php
/**
 * WooCommerce Application Verification Guard.
 *
 * @package DeWittePrins\CoreFunctionality\Environment\Plugins
 * @since   4.0.0
 */

namespace DeWittePrins\CoreFunctionality\Environment\Plugins;

use DeWittePrins\CoreFunctionality\Core;
use DeWittePrins\CoreFunctionality\Contracts\EnvironmentInterface;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class Woocommerce
 *
 * Validates the runtime availability of the core WooCommerce plugin structure
 * utilizing safe translation mapping, dynamic checking, and deep facade guards.
 *
 * @since 4.0.0
 */
class Woocommerce implements EnvironmentInterface {

	/**
	 * Unique identifier token for this specific ecosystem component.
	 *
	 * @since 4.0.0
	 * @return string The blueprint identification token.
	 */
	public function get_id(): string {
		return 'woocommerce';
	}

	/**
	 * Verifies if the plugin is operational and its functional endpoints exist.
	 *
	 * @since 4.0.0
	 * @return bool True if fully accessible, false otherwise.
	 */
	public function is_ready(): bool {
		$mapper   = Core::get_service( 'environment_mapper' );
		$basename = $mapper ? $mapper->get_basename( 'woocommerce' ) : '';

		// CIRCUIT BREAKER: Halt execution instantly if the allocation mapping is missing.
		if ( empty( $basename ) ) {
			return false;
		}

		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		if ( ! is_plugin_active( $basename ) ) {
			return false;
		}

		// Core method verification checks preventing deep autoloader deception.
		return function_exists( 'WC' );
	}

	/**
	 * Returns custom administration bar node attributes for immediate rendering.
	 *
	 * @since 4.0.0
	 * @return array The node data structure for the admin toolbar.
	 */
	public function get_node_data(): array {
		return array(
			'id'     => 'woocommerce',
			'title'  => __( 'WooCommerce', 'core-functionality-oop' ),
			'parent' => 'plugins',
			'href'   => admin_url( 'plugins.php#woocommerce' ),
		);
	}
}
