<?php
/**
 * Yoast SEO Application Verification Guard.
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
 * Class YoastSeo
 *
 * Evaluates core framework indices ensuring Yoast SEO (or premium variant equivalents)
 * run safely in current layouts using automated option metrics mappings.
 *
 * @since 4.0.0
 */
class YoastSeo implements EnvironmentInterface {

	/**
	 * Unique identifier token for this specific ecosystem component.
	 *
	 * @since 4.0.0
	 * @return string The blueprint identification token.
	 */
	public function get_id(): string {
		return 'yoast_seo';
	}

	/**
	 * Confirms activation signatures safely behind circuit breakers.
	 *
	 * @since 4.0.0
	 * @return bool True if active and matching core references, false otherwise.
	 */
	public function is_ready(): bool {
		$mapper   = Core::get_service( 'environment_mapper' );
		$basename = $mapper ? $mapper->get_basename( 'yoast_seo' ) : '';

		// CIRCUIT BREAKER: Immediate exit if mapping allocation strings are vacant.
		if ( empty( $basename ) ) {
			return false;
		}

		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		return is_plugin_active( $basename );
	}

	/**
	 * Returns custom administration bar node attributes for immediate rendering.
	 *
	 * @since 4.0.0
	 * @return array<string, string> Associative array of admin bar node attributes.
	 */
	public function get_node_data(): array {
		return array(
			'id'     => 'yoast_seo',
			'title'  => __( 'Yoast SEO', 'core-functionality-oop' ),
			'parent' => 'plugins',
			'href'   => admin_url( 'plugins.php#yoast-seo' ),
		);
	}
}
