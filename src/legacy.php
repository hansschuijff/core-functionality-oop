<?php
/**
 * Legacy Backwards Compatibility Layer for De Witte Prins Core Functionality.
 *
 * This file maps old namespace calls to the new Core infrastructure to prevent crashes
 * during the migration phase, while triggering native WordPress deprecated notices.
 *
 * @package DeWittePrins\CoreFunctionality
 */

namespace DeWittePrins\CoreFunctionality;

if ( ! function_exists( __NAMESPACE__ . '\log' ) ) {
	/**
	 * Deprecated logging adapter mapping old procedure calls to the modern architecture.
	 *
	 * @since 4.0.0
	 * @param array $data Extracted error and tracking metrics payload.
	 * @return void
	 * @see _deprecated_function()
	 */
	function log( array $data ): void {
		if ( function_exists( '_deprecated_function' ) ) {
			_deprecated_function(
				__NAMESPACE__ . '\log',
				'4.0.0',
				'\DeWittePrins\Core\log'
			);
		}

		if ( function_exists( '\DeWittePrins\Core\is_cf_ready' ) && \DeWittePrins\Core\is_cf_ready() ) {
			\DeWittePrins\Core\log( $data );
		}
	}
}
