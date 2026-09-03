<?php
/**
 * WordPress Core Environment Data Component Guard.
 *
 * @package DeWittePrins\CoreFunctionality\Environment
 * @since   1.0.0
 */

namespace DeWittePrins\CoreFunctionality\Environment;

use DeWittePrins\CoreFunctionality\Contracts\EnvironmentInterface;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WordPressCore
 *
 * Serves as an infrastructure data token providing input configurations for core administration links.
 *
 * @since 1.0.0
 */
class WordPressCore implements EnvironmentInterface {

	/**
	 * Unique identifier token for this specific ecosystem component.
	 *
	 * @return string The blueprint identification token.
	 */
	public function get_id(): string {
		return 'wordpress_core';
	}

	/**
	 * Evaluates the runtime environment state.
	 *
	 * @return bool Always true since the framework runs post core bootstrap.
	 */
	public function is_ready(): bool {
		return true;
	}

	/**
	 * Returns custom administration bar node attributes for immediate rendering.
	 *
	 * Called dynamically by the orchestrator registry only when is_ready returns true.
	 *
	 * @return array Required layout keys mapping ID, title, parent, and destination slug.
	 */
	/**
	 * Returns custom administration bar node attributes with its registry key.
	 *
	 * @return array Required layout keys mapping ID, title, parent, and destination slug.
	 */
	public function get_node_data(): array {
		return array(
			'environment_key' => $this->get_id(), // ◄── HIER STAAT DE LINK-KEY ('wordpress_core')! 🎯
			'id'              => 'wordpress_core_link',
			'title'           => __( '⚡ WordPress Core', 'core-functionality-dwp' ),
			'parent'          => 'top-secondary',
			'destination'     => admin_url( 'update-core.php' ),
		);
	}
}
