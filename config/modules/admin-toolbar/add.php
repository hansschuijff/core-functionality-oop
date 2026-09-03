<?php
/**
 * Main Runtime configuration defaults for a quick link admin-toolbar menu.
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

/**
 * The $this variable is the parent module context, which is an instance of the Plugin class.
 *
 * @var \DeWittePrins\CoreFunctionality\Plugin $this
 */
return array(
	// Pull the sub-repositories dynamically with inheritance of the module context!
	...$this->get_config( 'admin-toolbar-add-plugins', AdminToolbar::get_id() ),
	...$this->get_config( 'admin-toolbar-add-site', AdminToolbar::get_id() ),
	...$this->get_config( 'admin-toolbar-add-shop', AdminToolbar::get_id() ),
	...$this->get_config( 'admin-toolbar-add-events', AdminToolbar::get_id() ),
	...$this->get_config( 'admin-toolbar-add-courses', AdminToolbar::get_id() ),
	...$this->get_config( 'admin-toolbar-add-posts', AdminToolbar::get_id() ),
	...$this->get_config( 'admin-toolbar-add-other-edits', AdminToolbar::get_id() ),
	...$this->get_config( 'admin-toolbar-add-settings', AdminToolbar::get_id() ),
	...$this->get_config( 'admin-toolbar-add-tools', AdminToolbar::get_id() ),
	...$this->get_config( 'admin-toolbar-add-view', AdminToolbar::get_id() ),
);
