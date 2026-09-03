<?php
/**
 * Global Configuration Index Registry.
 *
 * Maps abstract configuration keys used by modules and features
 * to their specific deep subfolder layout tracks on the disk.
 *
 * @package DeWittePrins\CoreFunctionality
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

return array(
	// Globals & Core Framework Index Registry.
	'environment-index' => 'environment-index',
	'modules'           => 'modules',
);
