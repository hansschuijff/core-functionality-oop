<?php
/**
 * Admin Toolbar Module Internal Configuration Router Index.
 *
 * Maps abstract feature configuration keys to localized file system targets.
 *
 * @package DeWittePrins\CoreFunctionality
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

return array(
	// Admin Toolbar Module Configuration Tracks.
	'toolbar-move'                                     => 'move',
	'toolbar-add'                                      => 'add',
	// Admin Toolbar Plugin submenu items.
	'admin-toolbar-add-plugins'                        => 'add-plugins/menu',
	'admin-toolbar-add-plugins-festinger-vault'        => 'add-plugins/items/festinger-vault',
	'admin-toolbar-add-plugins-github'                 => 'add-plugins/items/github',
	'admin-toolbar-add-plugins-nobuna'                 => 'add-plugins/items/nobuna',
	'admin-toolbar-add-plugins-wp-core'                => 'add-plugins/items/wp-core',
	// Admin Toolbar Site submenu items.
	'admin-toolbar-add-site'                           => 'add-site/menu',
	// Admin Toolbar Shop submenu items.
	'admin-toolbar-add-shop'                           => 'add-shop/menu',
	'admin-toolbar-add-shop-front'                     => 'add-shop/items/front',
	'admin-toolbar-add-shop-admin'                     => 'add-shop/items/admin',
	'admin-toolbar-add-shop-settings'                  => 'add-shop/items/settings',
	'admin-toolbar-add-shop-settings-woocommerce'      => 'add-shop/items/settings/woocommerce',
	'admin-toolbar-add-shop-settings-pdf-invoices'     => 'add-shop/items/settings/pdf-invoices',
	'admin-toolbar-add-shop-settings-eu-vat-assistant' => 'add-shop/items/settings/eu-vat-assistant',
	'admin-toolbar-add-shop-settings-activewoo'        => 'add-shop/items/settings/activewoo',
	// Admin Toolbar Events submenu items.
	'admin-toolbar-add-events'                         => 'add-events/menu',
	// Admin Toolbar Courses submenu items.
	'admin-toolbar-add-courses'                        => 'add-courses/menu',
	// Admin Toolbar Posts submenu items.
	'admin-toolbar-add-posts'                          => 'add-posts/menu',
	// Admin Toolbar Other Edits submenu items.
	'admin-toolbar-add-other-edits'                    => 'add-other-edits/menu',
	// Admin Toolbar Settings submenu items.
	'admin-toolbar-add-settings'                       => 'add-settings/menu',
	// Admin Toolbar Tools submenu items.
	'admin-toolbar-add-tools'                          => 'add-tools/menu',
	// Admin Toolbar View submenu items.
	'admin-toolbar-add-view'                           => 'add-view/menu',
	'admin-toolbar-add-view-events'                    => 'add-view/items/events',
	'admin-toolbar-add-view-courses'                   => 'add-view/items/courses',
	'admin-toolbar-add-view-shop'                      => 'add-view/items/shop',
);
