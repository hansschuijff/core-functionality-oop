<?php
/**
 * Plugin Environment Data Provider Contract.
 *
 * @package    DeWittePrins\CoreFunctionality
 * @subpackage Environment\Plugins\Interfaces
 * @author     Hans Schuijff <@hansschuijff>
 * @license    GPL-2.0
 * @since      1.0.0
 */

namespace DeWittePrins\CoreFunctionality\Environment\Plugins\Interfaces;

use DeWittePrins\CoreFunctionality\Environment\Interfaces\DependencyInterface;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Interface PluginInterface
 *
 * @since 1.0.0
 */
interface PluginInterface extends DependencyInterface {
	// The abstract protected nodes reside exclusively inside AbstractPlugin now.
}
