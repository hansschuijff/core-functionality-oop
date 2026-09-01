<?php
/**
 * De Witte Prins Core Platform Mapping Service.
 *
 * @package DeWittePrins\Core\Services
 * @since   4.0.0
 */

namespace DeWittePrins\Core\Services;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class EnvironmentMapper
 *
 * Resolves environmental platform abstractions into true physical WordPress basenames,
 * shielding guards from folder-renaming conflicts and allowing seamless adjustments.
 *
 * @since 4.0.0
 */
class EnvironmentMapper {

	/**
	 * Centralized register containing token-to-basename parameter strings.
	 *
	 * @since 4.0.0
	 * @var array
	 */
	private array $mapping = array();

	/**
	 * EnvironmentMapper constructor.
	 *
	 * Compiles baseline defaults and dynamically incorporates user dashboard overwrites.
	 *
	 * @since 4.0.0
	 * @see get_option()
	 */
	public function __construct() {
		$defaults = array(
			'woocommerce'                     => 'woocommerce/woocommerce.php',
			'the_events_calendar'             => 'the-events-calendar/the-events-calendar.php',
			'event_tickets_plus'              => 'event-tickets-plus/event-tickets-plus.php',
			'yoast_seo'                       => 'wordpress-seo/wp-seo.php',
			'mollie_payments_for_woocommerce' => 'mollie-payments-for-woocommerce/mollie-payments-for-woocommerce.php',
			'carbon_fields'                   => 'carbon-fields/carbon-fields.php',
			'debug_toolkit'                   => '_debug-toolkit/debug-toolkit.php',
		);

		$user_overwrites = get_option( 'dwp_platform_basenames', array() );

		$this->mapping = array_merge( $defaults, $user_overwrites );
	}

	/**
	 * Translates an abstract system key into a physical active plugin basename descriptor string.
	 *
	 * @since 4.0.0
	 * @param string $platform_id The abstract configuration reference key (e.g. 'yoast_seo').
	 * @return string The running mapping script path string, or an empty string configuration.
	 */
	public function get_basename( string $platform_id ): string {
		return $this->mapping[ $platform_id ] ?? '';
	}
}
