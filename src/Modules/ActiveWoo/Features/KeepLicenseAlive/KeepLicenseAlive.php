<?php
/**
 * Plugin hack that keeps license data in transient so it will not expire.
 *
 * @package    DeWittePrins\CoreFunctionality
 * @subpackage Modules\ActiveWoo\Features\KeepLicense
 * @author     Hans Schuijff <@hansschuijff>
 * @license    GPL-2.0
 * @since      1.0.0
 */

declare( strict_types=1 );

namespace DeWittePrins\CoreFunctionality\Modules\ActiveWoo\Features\KeepLicenseAlive;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use DeWittePrins\CoreFunctionality\Plugin;
use DeWittePrins\CoreFunctionality\Enums\Orientation;
use DeWittePrins\CoreFunctionality\Interfaces\FeatureInterface;
use DeWittePrins\CoreFunctionality\Interfaces\ModuleInterface;

use function __;
use function \get_transient;
use function \set_transient;
use function \update_option;
use function \delete_transient;

/**
 * Class KeepLicense
 *
 * Sustains a copy of the license in transient, so the plugin will keep thinking it is licensed.
 *
 * @since 1.0.0
 */
class KeepLicenseAlive implements FeatureInterface {

	/**
	 * Constructor.
	 *
	 * @param ModuleInterface $module Parent module context.
	 */
	public function __construct(
		private readonly ModuleInterface $module,
		private readonly Plugin $plugin
	) {}

	/**
	 * Returns the unique identification string for this feature toggle.
	 *
	 * @since  1.0.0
	 * @return string The unique micro-feature string key.
	 */
	public static function get_id(): string {
		return 'active_woo_keep_license_alive';
	}

	/**
	 * Retrieves the human-readable name of the feature.
	 *
	 * @since  1.0.0
	 * @return string Feature title.
	 */
	public static function get_name(): string {
		return __( 'Keep ActiveWoo License Alive', 'dwp-cf' );
	}

	/**
	 * Retrieves the contextual description of what the feature provides.
	 *
	 * @since  1.0.0
	 * @return string Feature description.
	 */
	public static function get_description(): string {
		return __( 'Sustains a copy of the license in transient, so the plugin will keep thinking it is licensed.', 'dwp-cf' );
	}

	/**
	 * Retrieves feature-specific environment prerequisites.
	 *
	 * @since  1.0.0
	 * @return string|array String dependency or multi-dimensional preflight checks matrix.
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
	 * Requests the current usage-target of the feature (Frontend, Admin, Both).
	 *
	 * @since  1.0.0
	 * @return Orientation Enum indication if the feature is meant for use on the Frontend, Admin or both.
	 */
	public static function get_orientation(): Orientation {
		return Orientation::BOTH;
	}

	/**
	 * Launches the operational execution lifecycle for this micro-feature.
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public function launch(): void {
		$this->set_license_data();
	}

	/**
	 * Exposes the parent module instance.
	 *
	 * @since  1.0.0
	 * @return ModuleInterface
	 */
	public function get_module(): ModuleInterface {
		return $this->module;
	}

	/**
	 * Save active license data as transient, so ActiveWoo will read it.
	 *
	 * @return object
	 */
	private function set_license_data() {

		$license_data = $this->plugin->settings->get('activewoo-license-data');

		$transient_license_data = get_transient( 'aw_license_data' );

		if ( $license_data !== $transient_license_data ) {
			// bewaar license_data een jaar lang als transient
			delete_transient( 'aw_license_data' );
			set_transient( 'aw_license_data', $license_data, \YEAR_IN_SECONDS );
			update_option( 'aw_license', $license_data->license_key );
		}

	}
}
