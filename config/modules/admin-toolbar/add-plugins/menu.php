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

use DeWittePrins\Corefunctionality\Modules\AdminToolbar\AdminToolbar;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	// Plugins.
	array(
		'node_args'  =>
			array(
				'title'  => \__( 'Plugins', 'core-functionality-dwp' ),
				'parent' => 'site-name',
				'id'     => 'cf-shortcuts-plugins',
				'href'   => \admin_url( 'plugins.php', 'admin' ),
			),
		'visibility' => 'both',
	),
	...$this->get_config( 'admin-toolbar-add-plugins-wp-core', AdminToolbar::get_id() ),
	...$this->get_config( 'admin-toolbar-add-plugins-github', AdminToolbar::get_id() ),
	...$this->get_config( 'admin-toolbar-add-plugins-festinger-vault', AdminToolbar::get_id() ),
	...$this->get_config( 'admin-toolbar-add-plugins-nobuna', AdminToolbar::get_id() ),
);
