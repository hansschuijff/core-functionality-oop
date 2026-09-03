<?php
/**
 * Global Core Framework Modules Activation Register.
 *
 * This file serves as the master blueprint registry for identifying and loading
 * independent modular building blocks into the active application workspace lifecycle.
 *
 * @package DeWittePrins\CoreFunctionality
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

return array(
	/**
	 * Admin Toolbar Management Module.
	 * Orchestrates layouts, responsive custom flexbox wrap fixes, and unified shortcuts.
	 */
	'admin_toolbar' => \DeWittePrins\CoreFunctionality\Modules\AdminToolbar\AdminToolbar::class,
);
