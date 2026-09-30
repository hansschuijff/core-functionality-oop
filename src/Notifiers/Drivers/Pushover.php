<?php
/**
 * Pushover Message Driver Implementation.
 *
 * @package    DeWittePrins\CoreFunctionality
 * @subpackage Notifiers\Drivers
 * @author     Hans Schuijff <@hansschuijff>
 * @license    GPL-2.0
 * @since      1.0.0
 */

declare(strict_types=1);

namespace DeWittePrins\CoreFunctionality\Notifiers\Drivers;

use DeWittePrins\CoreFunctionality\Notifiers\Interfaces\PushNoticeInterface;
use Override;

use function wp_remote_post;

/**
 * Class Pushover
 *
 * Handles the configuration profile and transmission trigger for Pushover device alerts.
 *
 * @since 1.0.0
 */
class Pushover implements PushNoticeInterface {

	/**
	 * Constructor encapsulating platform-specific routing variables.
	 *
	 * Utilizing PHP Constructor Property Promotion to make the entire array immutably readonly
	 * without declaring individual property attributes.
	 *
	 * @since 1.0.0
	 * @param array{token?: string, user?: string, title?: string} $settings Authorization and destination configurations.
	 */
	public function __construct( private readonly array $settings ) {}

	/**
	 * Executes the platform-specific Pushover API request.
	 *
	 * @since 1.0.0
	 * @param string $message The alert text payload to push.
	 * @return bool True if successfully initiated, false otherwise.
	 */
	#[Override]
	public function send( string $message, string $title = '' ): bool {
		// Extract variables directly from the immutable configuration array shield
		$token = $this->settings['token'] ?? '';
		$user  = $this->settings['user'] ?? '';
		$title = $this->settings['title'] ?? '[DeWittePrins]';

		// Guard: If credentials are empty, silently abort without crashing the core framework loop
		if ( empty( $token ) || empty( $user ) ) {
			return false;
		}

		$url = 'https://pushover.net';

		wp_remote_post( $url, array(
			'body' => array(
				'token'   => $token,
				'user'    => $user,
				'message' => $message,
				'title'   => $title,
			),
		) );

		return true;
	}

	#[Override]
	public static function get_name(): string {
		return __( 'Pushover (App Notificaties)', 'dwp-cf' );
	}

	#[Override]
	public static function get_configuration_schema(): array {
		return array(
			'token' => array(
				'type'  => 'text',
				'label' => __( 'Pushover Application Token', 'dwp-cf' ),
				'desc'  => __( 'Your unique Pushover API application token key.', 'dwp-cf' ),
			),
			'user'  => array(
				'type'  => 'text',
				'label' => __( 'Pushover User Key', 'dwp-cf' ),
				'desc'  => __( 'Your personal Pushover user account key identifier.', 'dwp-cf' ),
			),
			'title' => array(
				'type'  => 'text',
				'label' => __( 'Notifier Title', 'dwp-cf' ),
				'desc'  => __( 'Optional title prefix (Defaults to [DeWittePrins]).', 'dwp-cf' ),
			),
		);
	}
}
