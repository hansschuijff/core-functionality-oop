<?php
/**
 * Admin Toolbar Core Module Controller.
 *
 * @package DeWittePrins\CoreFunctionality\Modules\AdminToolbar
 * @since   1.0.0
 */

namespace DeWittePrins\CoreFunctionality\Modules\AdminToolbar;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use DeWittePrins\CoreFunctionality\Plugin;
use DeWittePrins\CoreFunctionality\Interfaces\ModuleInterface;
use DeWittePrins\CoreFunctionality\Enums\Orientation;

use function __;
/**
 * Class AdminToolbar
 *
 * @since 1.0.0
 */
class AdminToolbar implements ModuleInterface {

	/**
	 * Retrieves the unique administrative string identifier key.
	 *
	 * @return string The unique module key.
	 */
	public static function get_id(): string {
		return 'admin_toolbar';
	}

	/**
	 * Retrieves the localized visual title name.
	 *
	 * @return string The human-readable module name.
	 */
	public static function get_name(): string {
		return __( 'Admin Toolbar', 'dwp-cf' );
	}

	/**
	 * Retrieves the contextual description of what the module provides.
	 *
	 * @since  1.0.0
	 * @return string Module description.
	 */
	public static function get_description(): string {
		return __( 'Provides advanced layout overrides, performance enhancements, and custom nodes filtering for the WordPress admin bar.', 'dwp-cf' );
	}

	/**
	 * Requests the current usage-target of the feature (Frontend, Admin, Both).
	 *
	 * @since  1.0.0
	 * @return Orientation Enum indication if the feature is meant for use on the Frontend, Admin or both.
	 */
	public static function get_orientation(): Orientation {
		return Orientation::BOTH;
	}

	/**
	 * Requests the structural environment requirements for the module base layer.
	 *
	 * @since  1.0.0
	 * @return string|array String or multi-dimensional array tracking environmental requirements.
	 */
	public static function get_dependencies(): string|array {
		return array();
	}

	/**
	 * Returns an array with internal dependencies with other features or modules.
	 *
	 * @since  1.0.0
	 * @return array<int, string> List of fully qualified feature class strings.
	 */
	public static function uses_features(): array {
		return array();
	}

	/**
	 * AdminToolbar Constructor.
	 *
	 * @since 1.0.0
	 * @param Plugin $plugin The central plugin class instance acting as a provider.
	 */
	public function __construct(
		private readonly Plugin $plugin,
	) {}

	/**
	 * Orchestrates late operational hook executions for the module base layer.
	 *
	 * Executed autonomously by the Kernel once the centralized init hook fires.
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public function launch(): void {
		// De features zijn al veilig in de constructor gescheduled en geïnstantiëerd!
		// Hier hoeft alleen nog maar specifieke, late module-logica te staan indien nodig.
	}
}
