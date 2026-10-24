<?php
/**
 * Adds a configurable shortcuts menu to the admin toolbar.
 *
 * Automatically extracts validated shortcut configurations from the environment
 * registration pool and injects them safely into the WordPress Admin Bar.
 *
 * @package DeWittePrins\CoreFunctionality\Modules\AdminToolbar\Features\MenuBuilder
 * @author  Hans Schuijff <@hansschuijff>
 * @license GPL-2.0
 * @since   1.0.0
 */

declare(strict_types=1);

namespace DeWittePrins\CoreFunctionality\Modules\AdminToolbar\Features\MenuBuilder;

use DeWittePrins\CoreFunctionality\Abstracts\AbstractFeature;
use DeWittePrins\CoreFunctionality\DTO\ComponentMeta;
use Override;
use DeWittePrins\CoreFunctionality\Environment\Registers\AdminToolbarRegister;
use WP_Admin_Bar;

use function current_user_can;
use function is_admin;
use function is_admin_bar_showing;
use function is_array;
use function esc_attr;
use function esc_html;
use function esc_url;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class MenuBuilder
 *
 * Inject shortcut navigation elements using the decoupled Environment readiness matrix.
 *
 * @since 1.0.0
 */
class MenuBuilder extends AbstractFeature {

	/**
	 * Returns the descriptive data of this feature.
	 *
	 * @since  1.0.0
	 * @return ComponentMeta Id, name, description and requirements.
	 */
	#[Override]
	public static function get_meta(): ComponentMeta {
		return new ComponentMeta(
			id:            'admin_toolbar_builder',
			name:          'Admin Toolbar Menu Feature.',
			description:   'The Admin Toolbar Builder Feature builds a navigation shortcuts menu in the admin toolbar.',
		);
	}

	/**
	 * Launches the operational execution lifecycle for this micro-feature.
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public function launch(): void {
		if ( ! is_admin_bar_showing() ) {
			return;
		}

		// Securely register the configuration panel into the host page factory autonomously.
		add_action(
			'core-functionality/register-settings-sections',
			function( $settings_factory ) {
				$settings_factory->register_section( MenuBuilderSettings::class );
			}
		);

		add_action( 'admin_bar_menu', array( $this, 'inject_toolbar_shortcuts' ), 999 );
		add_action( 'admin_bar_menu', array( $this, 'remove_appearance_node_on_front' ), 1000 );
	}

	/**
	 * Processes the aggregated register and dynamically injects nodes into the toolbar.
	 *
	 * @since  1.0.0
	 * @param  WP_Admin_Bar $wp_admin_bar Global WordPress admin bar object.
	 * @return void
	 */
	public function inject_toolbar_shortcuts( WP_Admin_Bar $wp_admin_bar ): void {
		if ( ! is_object( $wp_admin_bar ) || ! current_user_can( 'administrator' ) ) {
			return;
		}

		// 1. Gather all environment-passed shortcuts from the centralized repository register.
		$master_registry = AdminToolbarRegister::get();

		if ( empty( $master_registry ) || ! is_array( $master_registry ) ) {
			return;
		}

		$current_orientation = is_admin() ? 'admin' : 'frontend';

		// 2. Loop through the distinct registered providers (e.g., 'wordpress-core', 'woocommerce').
		foreach ( $master_registry as $provider_id => $shortcuts ) {
			if ( empty( $shortcuts ) || ! is_array( $shortcuts ) ) {
				continue;
			}

			/** @var \DeWittePrins\CoreFunctionality\DTO\ToolbarShortcut $shortcut */
			foreach ( $shortcuts as $shortcut ) {

				// Gate 1: Enforce strict orientationual visibility filtering (admin vs frontend).
				$orientation = $shortcut->orientation ?? 'both';
				if ( 'both' !== $orientation && $current_orientation !== $orientation ) {
					continue;
				}

				// Gate 2: Resolve the target URL safely. If it maps to a closure, execute it natively.
				$href = $shortcut->get_href();
				if ( $href instanceof \Closure ) {
					$href = $href();
				}

				// 3. Map DTO parameters seamlessly into the structural array format expected by WordPress.
				$node_args = array(
					'id'     => esc_attr( $shortcut->id ),
					'title'  => esc_html( $shortcut->title ),
					'parent' => esc_attr( $shortcut->parent ?? 'site-name' ),
					'href'   => esc_url( (string) $href ),
					'meta'   => array(
						'title' => esc_attr( $shortcut->label ?? $shortcut->title ),
					),
				);

				// 4. Inject the completed node argument block directly into the WordPress admin bar registry.
				$wp_admin_bar->add_node( $node_args );
			}
		}
	}

	/**
	 * Removes the generic core 'appearance' node from the frontend view.
	 *
	 * @since  1.0.0
	 * @param  WP_Admin_Bar $wp_admin_bar Global WordPress admin bar object.
	 * @return void
	 */
	public function remove_appearance_node_on_front( WP_Admin_Bar $wp_admin_bar ): void {
		if ( is_admin() || ! is_object( $wp_admin_bar ) ) {
			return;
		}

		$wp_admin_bar->remove_node( 'appearance' );
	}
}
