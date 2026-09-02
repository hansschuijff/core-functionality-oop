<?php
/**
 * Debug Toolkit Application Guard and Dashboard Component.
 *
 * @package DeWittePrins\Environment\Plugins
 * @since   4.0.0
 */

namespace DeWittePrins\Environment\Plugins;

use DeWittePrins\Core;
use DeWittePrins\Core\Contracts\ComponentInterface;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class DebugToolkit
 *
 * Evaluates the runtime tracking status of the Debug Toolkit plugin.
 * Encapsulates its own admin toolbar node component layout specifications.
 *
 * @since 4.0.0
 */
class DebugToolkit implements ComponentInterface {

	/**
	 * Verifies if the diagnostic toolkit is active on the server instance.
	 *
	 * @since 4.0.0
	 * @return bool True if accessible, false otherwise.
	 */
	public function is_available(): bool {
		$mapper   = Core::get_service( 'environment_mapper' );
		$basename = $mapper ? $mapper->get_basename( 'debug_toolkit' ) : '';

		// CIRCUIT BREAKER: Halt execution instantly if the allocation mapping is missing.
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
	 * Called dynamically by the orchestrator registry only when is_available returns true.
	 *
	 * @since 4.0.0
	 * @return array Required layout keys mapping ID, title, parent, and destination slug.
	 */
	public function get_node_data(): array {
		return array(
			'id'        => 'debug_toolkit_link',
			'title'     => __( '🛠️ Debug Toolkit', 'core-functionality-dwp' ),
			'parent'    => 'plugins.php',
			'slug'      => 'plugins.php?plugin_status=active',
			'css_class' => 'dwp-debug-bar-item',
		);
	}
}
