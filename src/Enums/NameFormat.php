<?php
/**
 * Name (Classname, Filenames) Formatting Enum Blueprint.
 *
 * @package DeWittePrins\CoreFunctionality\Enums
 * @since   1.0.0
 */
namespace DeWittePrins\CoreFunctionality\Enums;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Enum FormatSetting
 *
 * Dictates the structural token layout string formats available for export queries.
 *
 * @since 1.0.0
 */
enum NameFormat: string {
	case SLUG = 'slug'; // Name without path or namespace.
	case FQN  = 'fqn';  // Fully Qualified Name.
}
