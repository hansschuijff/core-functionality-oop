<?php
/**
 * Admin Toolbar Core Module Controller.
 *
 * @package DeWittePrins\CoreFunctionality\Modules\AdminMenu
 * @since   1.0.0
 */

namespace DeWittePrins\CoreFunctionality\Modules\AdminMenu;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use DeWittePrins\CoreFunctionality\Abstracts\AbstractModule;
use DeWittePrins\CoreFunctionality\DTO\ComponentMeta;
use Override;
use DeWittePrins\CoreFunctionality\Enums\Orientation;

use function __;
/**
 * Class AdminMenu
 *
 * @since 1.0.0
 */
class AdminMenu extends AbstractModule {

	/**
	 * Returns the descriptive data of this module.
	 *
	 * @since  1.0.0
	 * @return ComponentMeta Id, name, description and requirements.
	 */
	#[Override]
	public static function get_meta(): ComponentMeta {
		return new ComponentMeta(
			id:            'admin_menu',
			name:          __( 'Admin Menu', 'core-functionality' ),
			description:   __( 'Provides advanced layout overrides, performance enhancements, and custom nodes filtering for the WordPress admin menu.', 'core-functionality' ),
			orientation:   Orientation::ADMIN,
		);
	}
}
