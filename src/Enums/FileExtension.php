<?php
/**
 * File containing the FileExtension Enum.
 *
 * @package DeWittePrins\CoreFunctionality\Enums
 * @author  Hans Schuijff @hansschuijff
 * @license GPL-2.0
 * @since   1.0.0
 */

namespace DeWittePrins\CoreFunctionality\Enums;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enum indicating the different registers for the components register.
 *
 * @since 1.0.0
 */
enum FileExtension: string {
	case PHP  = 'php';
	case JSON = 'json';
}
