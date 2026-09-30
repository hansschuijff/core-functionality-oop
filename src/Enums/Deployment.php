<?php
/**
 * File containing the Deployment Enum.
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
 * Enum representing the current release and environment maturity of a Module or Feature.
 *
 * Used by the Kernel to filter component activation based on the server environment.
 *
 * @since 1.0.0
 */
enum Deployment: string {

	case DEVELOPMENT = 'development';
	case STAGING     = 'staging';
	case PRODUCTION  = 'production';

	/**
	 * Returns the localized human-readable label for the status.
	 *
	 * @since  1.0.0
	 * @return string The translated status label.
	 */
	public function get_label(): string {
		return match ( $this ) {
			self::DEVELOPMENT => __( '🧪 Development', 'dewitteprins-core' ),
			self::STAGING     => __( '🔬 Staging', 'dewitteprins-core' ),
			self::PRODUCTION  => __( '🌐 Production', 'dewitteprins-core' ),
		};
	}
}
