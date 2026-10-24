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

use DeWittePrins\CoreFunctionality\Abstracts\AbstractFeature;
use DeWittePrins\CoreFunctionality\DTO\ComponentMeta;
use Override;
use DeWittePrins\CoreFunctionality\Plugin;

use function __;
use function get_transient;
use function set_transient;
use function update_option;
use function delete_transient;

/**
 * Class KeepLicense
 *
 * Sustains a copy of the license in transient, so the plugin will keep thinking it is licensed.
 *
 * @since 1.0.0
 */
class KeepLicenseAlive extends AbstractFeature {

	/**
	 * Returns the descriptive data of this feature.
	 *
	 * @since  1.0.0
	 * @return ComponentMeta Id, name, description and requirements.
	 */
	#[Override]
	public static function get_meta(): ComponentMeta {
		return new ComponentMeta(
			id:            'active_woo_keep_license_alive',
			name:          __( 'Keep ActiveWoo License Alive', 'core-functionality' ),
			description:   __( 'Sustains a copy of the license in transient, so the plugin will keep thinking it is licensed.', 'core-functionality' ),
		);
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
	 * Save active license data as transient, so ActiveWoo will read it.
	 *
	 * @return object
	 */
	private function set_license_data() {

		$license_data = $this->plugin->settings->get( 'activewoo-license-data' );

		$transient_license_data = get_transient( 'aw_license_data' );

		if ( $license_data !== $transient_license_data ) {
			// bewaar license_data een jaar lang als transient.
			delete_transient( 'aw_license_data' );
			set_transient( 'aw_license_data', $license_data, \YEAR_IN_SECONDS );
			update_option( 'aw_license', $license_data->license_key );
		}
	}
}
