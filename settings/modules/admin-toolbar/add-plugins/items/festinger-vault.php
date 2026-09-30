<?php
/**
 * Runtime configuration defaults for the quick link admin-toolbar menu.
 *
 * Returns a list (array) of link attributes and dependant plugins
 *
 * @package     DeWittePrins\CoreFunctionality
 * @since       1.0.0
 * @author      Hans Schuijff
 * @link        https://dewitteprins.nl
 * @license     GNU-2.0+
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Return staat onderin.

if ( ! function_exists( 'add_fv_toolbar_links' ) ) {
	/**
	 * Return toolbar links for fv plugin up 5.0 upwards
	 *
	 * @return array
	 */
	function add_fv_toolbar_links(): array {
		/**
		 * Other pages, not in toolbar
		 *
		 * Dashboard
		 * \admin_url( 'admin.php?page=festingervault' ),
		 * Browse
		 * \admin_url( 'admin.php?page=festingervault#/browse' ),
		 * Popular
		 * \admin_url( 'admin.php?page=festingervault#/popular' ),'
		 * Requests
		 * \admin_url( 'admin.php?page=festingervault#/requests' ),
		 * Collections
		 * \admin_url( 'admin.php?page=festingervault#/collections' ),
		 * Activate license
		 * \admin_url( 'admin.php?page=festingervault#/activation' ),
		 */
		return array(
			// Plugins -> Festinger.
			array(
				'node_args'  =>
					array(
						'title'  => \__( 'Festinger Vault', 'dwp-cf' ),
						'parent' => 'cf-shortcuts-plugins',
						'id'     => 'cf-shortcuts-plugins-festinger',
						'href'   => \admin_url( 'admin.php?page=festingervault#/updates' ),
						'meta'   => array(
							'title' => \__( 'Link to', 'dwp-cf' )
									. ' '
									. \__( 'Plugin Updates', 'dwp-cf' ),
						),
					),
				'visibility' => 'both',
				'dependency' => array(
					'plugin' => 'festingervault/festingervault.php',
				),
			),
			// Plugins -> Festinger -> Plugins.
			array(
				'node_args'  =>
					array(
						'title'  => \__( 'Plugins', 'dwp-cf' ),
						'parent' => 'cf-shortcuts-plugins-festinger',
						'id'     => 'cf-shortcuts-plugins-festinger-plugins',
						'href'   => \admin_url( 'admin.php?page=festingervault#/item/wordpress-plugins' ),
						'meta'   => array(
							'title' => \__( 'Link to', 'dwp-cf' )
									. ' '
									. \__( 'Festinger Vault Plugins', 'dwp-cf' ),
						),
					),
				'visibility' => 'both',
				'dependency' => array(
					'plugin' => 'festingervault/festingervault.php',
				),
			),
			// Plugins -> Festinger -> Themes.
			array(
				'node_args'  =>
					array(
						'title'  => \__( 'Themes', 'dwp-cf' ),
						'parent' => 'cf-shortcuts-plugins-festinger',
						'id'     => 'cf-shortcuts-plugins-festinger-themes',
						'href'   => \admin_url( 'admin.php?page=festingervault#/item/wordpress-themes' ),
						'meta'   => array(
							'title' => \__( 'Link to', 'dwp-cf' )
									. ' '
									. \__( 'Festinger Vault Themes', 'dwp-cf' ),
						),
					),
				'visibility' => 'both',
				'dependency' => array(
					'plugin' => 'festingervault/festingervault.php',
				),
			),
			// Plugins -> Festinger -> Elementor Template Kits.
			array(
				'node_args'  =>
					array(
						'title'  => \__( 'Elementor Template Kits', 'dwp-cf' ),
						'parent' => 'cf-shortcuts-plugins-festinger',
						'id'     => 'cf-shortcuts-plugins-festinger-elementor-template-kits',
						'href'   => \admin_url( 'admin.php?page=festingervault#/item/elementor-template-kits' ),
						'meta'   => array(
							'title' => \__( 'Link to', 'dwp-cf' )
									. ' '
									. \__( 'Festinger Vault Elementor Template Kits', 'dwp-cf' ),
						),
					),
				'visibility' => 'both',
				'dependency' => array(
					'plugin' => 'festingervault/festingervault.php',
				),
			),
			// Plugins -> Festinger -> Updates.
			array(
				'node_args'  =>
					array(
						'title'  => \__( 'Updates', 'dwp-cf' ),
						'parent' => 'cf-shortcuts-plugins-festinger',
						'id'     => 'cf-shortcuts-plugins-festinger-plugin-updates',
						'href'   => \admin_url( 'admin.php?page=festingervault#/updates' ),
						'meta'   => array(
							'title' => \__( 'Link to', 'dwp-cf' )
									. ' '
									. \__( 'Plugin Updates', 'dwp-cf' ),
						),
					),
				'visibility' => 'both',
				'dependency' => array(
					'plugin' => 'festingervault/festingervault.php',
				),
			),
			// Plugins -> Festinger -> History.
			array(
				'node_args'  =>
					array(
						'title'  => \__( 'History', 'dwp-cf' ),
						'parent' => 'cf-shortcuts-plugins-festinger',
						'id'     => 'cf-shortcuts-plugins-festinger-history',
						'href'   => \admin_url( 'admin.php?page=festingervault#/history' ),
						'meta'   => array(
							'title' => \__( 'Link to', 'dwp-cf' )
									. ' '
									. \__( 'History', 'dwp-cf' ),
						),
					),
				'visibility' => 'both',
				'dependency' => array(
					'plugin' => 'festingervault/festingervault.php',
				),
			),
			// Plugins -> Festinger -> Settings.
			array(
				'node_args'  =>
					array(
						'title'  => \__( 'Settings', 'dwp-cf' ),
						'parent' => 'cf-shortcuts-plugins-festinger',
						'id'     => 'cf-shortcuts-plugins-festinger-settings',
						'href'   => \admin_url( 'admin.php?page=festingervault#/settings' ),
						'meta'   => array(
							'title' => \__( 'Link to', 'dwp-cf' )
									. ' '
									. \__( 'Settings', 'dwp-cf' ),
						),
					),
				'visibility' => 'both',
				'dependency' => array(
					'plugin' => 'festingervault/festingervault.php',
				),
			),
			// Plugins -> Festinger -> Need help (support).
			array(
				'node_args'  =>
					array(
						'title'  => \__( 'Support', 'dwp-cf' ),
						'parent' => 'cf-shortcuts-plugins-festinger',
						'id'     => 'cf-shortcuts-plugins-festinger-support',
						'href'   => \admin_url( 'admin.php?page=festingervault#/need-help' ),
						'meta'   => array(
							'title' => \__( 'Link to', 'dwp-cf' )
									. ' '
									. \__( 'support', 'dwp-cf' ),
						),
					),
				'visibility' => 'both',
				'dependency' => array(
					'plugin' => 'festingervault/festingervault.php',
				),
			),
		);
	}
}

if ( ! function_exists( 'add_fv_legacy_toolbar_links' ) ) {
	/**
	 * Return toolbar links to add for fv plugin up to the redesign of version 5
	 *
	 * @return array
	 */
	function add_fv_legacy_toolbar_links(): array {
		return array(
			// Plugins -> Festinger.
			array(
				'node_args'  =>
					array(
						'title'  => \__( 'Festinger Vault', 'dwp-cf' ),
						'parent' => 'cf-shortcuts-plugins',
						'id'     => 'cf-shortcuts-plugins-festinger',
						'href'   => \admin_url( 'admin.php?page=festinger-vault-updates' ),
						'meta'   => array(
							'title' => \__( 'Link to', 'dwp-cf' )
									. ' '
									. \__( 'Plugin Updates', 'dwp-cf' ),
						),
					),
				'visibility' => 'both',
				'dependency' => array(
					'plugin' => 'Festinger Vault',
				),
			),
			// Plugins -> Festinger -> Vault.
			array(
				'node_args'  =>
					array(
						'title'  => \__( 'Vault', 'dwp-cf' ),
						'parent' => 'cf-shortcuts-plugins-festinger',
						'id'     => 'cf-shortcuts-plugins-festinger-vault',
						'href'   => \admin_url( 'admin.php?page=festinger-vault' ),
						'meta'   => array(
							'title' => \__( 'Link to', 'dwp-cf' )
									. ' '
									. \__( 'Festinger Vault', 'dwp-cf' ),
						),
					),
				'visibility' => 'both',
				'dependency' => array(
					'plugin' => 'Festinger Vault',
				),
			),
			// Plugins -> Festinger -> Plugin Updates.
			array(
				'node_args'  =>
					array(
						'title'  => \__( 'Plugin Updates', 'dwp-cf' ),
						'parent' => 'cf-shortcuts-plugins-festinger',
						'id'     => 'cf-shortcuts-plugins-festinger-plugin-updates',
						'href'   => \admin_url( 'admin.php?page=festinger-vault-updates' ),
						'meta'   => array(
							'title' => \__( 'Link to', 'dwp-cf' )
									. ' '
									. \__( 'Plugin Updates', 'dwp-cf' ),
						),
					),
				'visibility' => 'both',
				'dependency' => array(
					'plugin' => 'Festinger Vault',
				),
			),
			// Plugins -> Festinger -> Theme Updates.
			array(
				'node_args'  =>
					array(
						'title'  => \__( 'Theme Updates', 'dwp-cf' ),
						'parent' => 'cf-shortcuts-plugins-festinger',
						'id'     => 'cf-shortcuts-plugins-festinger-theme-updates',
						'href'   => \admin_url( 'admin.php?page=festinger-vault-theme-updates' ),
						'meta'   => array(
							'title' => \__( 'Link to', 'dwp-cf' )
									. ' '
									. \__( 'Theme Updates', 'dwp-cf' ),
						),
					),
				'visibility' => 'both',
				'dependency' => array(
					'plugin' => 'Festinger Vault',
				),
			),
			// Plugins -> Festinger -> History.
			array(
				'node_args'  =>
					array(
						'title'  => \__( 'History', 'dwp-cf' ),
						'parent' => 'cf-shortcuts-plugins-festinger',
						'id'     => 'cf-shortcuts-plugins-festinger-history',
						'href'   => \admin_url( 'admin.php?page=festinger-vault-theme-history' ),
						'meta'   => array(
							'title' => \__( 'Link to', 'dwp-cf' )
									. ' '
									. \__( 'History', 'dwp-cf' ),
						),
					),
				'visibility' => 'both',
				'dependency' => array(
					'plugin' => 'Festinger Vault',
				),
			),
			// Plugins -> Festinger -> Settings.
			array(
				'node_args'  =>
					array(
						'title'  => \__( 'Settings', 'dwp-cf' ),
						'parent' => 'cf-shortcuts-plugins-festinger',
						'id'     => 'cf-shortcuts-plugins-festinger-settings',
						'href'   => \admin_url( 'admin.php?page=festinger-vault' ),
						'meta'   => array(
							'title' => \__( 'Link to', 'dwp-cf' )
									. ' '
									. \__( 'Settings', 'dwp-cf' ),
						),
					),
				'visibility' => 'both',
				'dependency' => array(
					'plugin' => 'Festinger Vault',
				),
			),
		);
	}
}

if ( ! function_exists( 'plugin_version' ) ) {
	/**
	 * Returns plugin version based on a plugins basename.
	 *
	 * @param string $plugin_basename Plugin basename.
	 * @return string
	 */
	function plugin_version( string $plugin_basename ): string {
		$plugin_file = trailingslashit( WP_PLUGIN_DIR ) . $plugin_basename;
		return \get_plugin_data( $plugin_file )['Version'];
	}
}

return add_fv_toolbar_links();
