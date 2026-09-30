<?php
/**
 * Master Registry Index for Core Framework Modules.
 *
 * Maps abstract module identifiers to their respective physical layout tracks on disk.
 *
 * @package DeWittePrins\CoreFunctionality
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

return array(
	'DeWittePrins\\CoreFunctionality\\Notifiers\\Drivers\\Telegram',
	'DeWittePrins\\CoreFunctionality\\Notifiers\\Drivers\\Pushover',
);
