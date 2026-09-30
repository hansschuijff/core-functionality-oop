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
	// DE KLASSENAAM IS DE ENIGE ECHTE SLEUTEL! 👑
	\DeWittePrins\CoreFunctionality\Modules\AdminToolbar\AdminToolbar::class => array(
		\DeWittePrins\CoreFunctionality\Modules\AdminToolbar\Features\MenuBuilder::class,
		\DeWittePrins\CoreFunctionality\Modules\AdminToolbar\Features\Cleaner::class,
		\DeWittePrins\CoreFunctionality\Modules\AdminToolbar\Features\Visualizer::class,
		\DeWittePrins\CoreFunctionality\Modules\AdminToolbar\Features\StylesFixer::class,
	),
);
