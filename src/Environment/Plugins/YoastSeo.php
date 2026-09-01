<?php
/**
 * Yoast SEO Application Verification Guard.
 *
 * @package DeWittePrins\Environment\Plugins
 * @since   4.0.0
 */

namespace DeWittePrins\Environment\Plugins;

use DeWittePrins\Core;

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
class YoastSeo {

	/**
	 * Confirms activation signatures safely behind circuit breakers.
	 *
	 * @since 4.0.0
	 * @return bool True if active and matching core references, false otherwise.
	 */
	public function is_available(): bool {
		$mapper   = Core::get_service( 'platform_mapper' );
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
}
