<?php
/**
 * Core Administration Component Registry and Orchestrator.
 *
 * @package DeWittePrins\Core\Services
 * @since   4.0.0
 */

namespace DeWittePrins\Core\Services;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class ComponentRegistry
 *
 * Collects standalone administrative interface components and handles human-driven
 * sorting, filtering, and conditional rendering pipelines within the WordPress ecosystem.
 *
 * @since 4.0.0
 */
class ComponentRegistry {

	/**
	 * Internal collection storing registered component blocks.
	 *
	 * @since 4.0.0
	 * @var array
	 */
	private array $registered_blocks = array();

	/**
	 * Human-configured ordering metrics pulled from the database rules.
	 *
	 * @since 4.0.0
	 * @var array
	 */
	private array $user_menu_order = array();

	/**
	 * ComponentRegistry constructor.
	 *
	 * Extracts user-defined dashboard preferences to enforce semantic sorting.
	 *
	 * @since 4.0.0
	 * @see get_option()
	 */
	public function __construct() {
		// Fetch manual sorting criteria defined dynamically by the user.
		$this->user_menu_order = get_option( 'dwp_toolbar_user_order', array() );
	}

	/**
	 * Accumulates available system component layout configurations.
	 *
	 * @since 4.0.0
	 * @param object $block Standalone configuration block implementing node metrics.
	 * @return void
	 */
	public function register_block( object $block ): void {
		if ( method_exists( $block, 'get_node_data' ) ) {
			$data = $block->get_node_data();

			if ( ! empty( $data['id'] ) ) {
				$this->registered_blocks[ $data['id'] ] = $block;
			}
		}
	}

	/**
	 * Human Synthesis Layer: Compiles and streams active components into the Admin-Bar.
	 * Actively filters out items whose active tracking checks failed during pre-flight.
	 *
	 * @since 4.0.0
	 * @return void
	 * @see WP_Admin_Bar
	 */
	public function build_toolbar(): void {
		global $wp_admin_bar;

		if ( ! is_object( $wp_admin_bar ) ) {
			return; // Admin bar layout interface is unavailable in this runtime context.
		}

		// 1. DYNAMIC USER FILTERING & ORDERING
		if ( ! empty( $this->user_menu_order ) ) {
			foreach ( $this->user_menu_order as $block_id ) {
				// If a premium plugin conflicts or is deactivated, its block won't exist here.
				if ( isset( $this->registered_blocks[ $block_id ] ) ) {
					$this->render_node( $this->registered_blocks[ $block_id ]->get_node_data() );
				}
			}
			return;
		}

		// Fallback: If no manual configuration array is detected, output all verified components.
		foreach ( $this->registered_blocks as $block ) {
			$this->render_node( $block->get_node_data() );
		}
	}

	/**
	 * Streams raw properties into the official native WordPress toolbar array engine.
	 *
	 * @since 4.0.0
	 * @param array $node_data Absolute metrics configuration tree package.
	 * @return void
	 */
	private function render_node( array $node_data ): void {
		global $wp_admin_bar;

		$wp_admin_bar->add_node( array(
			'id'     => $node_data['id'],
			'title'  => $node_data['title'],
			'parent' => $node_data['parent'] ?? false,
			'href'   => admin_url( $node_data['slug'] ),
			'meta'   => array(
				'class' => $node_data['css_class'] ?? '',
			),
		) );
	}
}
