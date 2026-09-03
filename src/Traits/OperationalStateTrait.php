<?php
/**
 * Shared Operational State Evaluation Trait for Framework Features.
 *
 * @package DeWittePrins\CoreFunctionality\Traits
 * @since   1.0.0
 */

namespace DeWittePrins\CoreFunctionality\Traits;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Trait OperationalStateTrait
 *
 * Provides a standardized, tri-state compliant implementation of the is_active check.
 */
trait OperationalStateTrait {

	/**
	 * Dictates the out-of-the-box factory behavior status for this feature.
	 *
	 * @var string
	 */
	private string $factory_state = 'active';

	/**
	 * Evaluates if the feature is operationally active based on database options and factory states.
	 * LOEPZUIVER: Voldoet direct overal aan de dwingende eis van het FeatureInterface! 🎯
	 *
	 * @return bool True if active or undetermined, false when explicitly disabled.
	 */
	public function is_active(): bool {
		$settings = get_option( 'dwp_enabled_features', array() );

		// STATE 1 & 2: De gebruiker heeft expliciet een keuze opgeslagen in de database.
		if ( isset( $settings[ $this->get_id() ] ) ) {
			return (bool) $settings[ $this->get_id() ];
		}

		// STATE 3 (ONBEPAALD): Geen database-waarde. We vallen terug op de fabriekstatus!
		return 'active' === $this->factory_state;
	}
}
