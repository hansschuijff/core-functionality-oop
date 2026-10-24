<?php
/**
 * File containing the ProviderStatus Enum.
 *
 * @package DeWittePrins\CoreFunctionality\Enums
 * @author  Hans Schuijff @hansschuijff
 * @license GPL-2.0
 * @since   1.0.0
 */

declare( strict_types=1 );

namespace DeWittePrins\CoreFunctionality\Enums;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enum with the state of a plugin or theme that offers admin toolbar nodes.
 *
 * Only the nodes of active providers are shown in the toolbar; the menu builder offers all of them,
 * so a menu can be set up before a plugin is installed or activated.
 *
 * @since 1.0.0
 */
enum ProviderStatus: string {

	case ACTIVE        = 'active';
	case INACTIVE      = 'inactive';
	case NOT_INSTALLED = 'not-installed';
}
