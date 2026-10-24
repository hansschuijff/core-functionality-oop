<?php
/**
 * Push Notice Interface.
 *
 * @package DeWittePrins\CoreFunctionality\Notifiers
 * @since   1.0.0
 */

declare( strict_types=1 );

namespace DeWittePrins\CoreFunctionality\Notifiers\Interfaces;

/**
 * Interface PushNoticeInterface
 */
interface PushNoticeInterface {

	/**
	 * Initiates and executes the message transmission sequence.
	 *
	 * @since  1.0.0
	 * @param  string $message The text to send.
	 * @param  string $title   Optional title, for platforms that support one.
	 * @return bool            True if the message was sent, false otherwise.
	 */
	public function send( string $message, string $title = '' ): bool;

	/**
	 * Returns the onboarding configuration fields required by this platform driver.
	 *
	 * @since  1.0.0
	 * @return array The configuration fields.
	 */
	public static function get_configuration_schema(): array;

	/**
	 * Returns the human-readable, friendly name of the platform driver.
	 *
	 * @since 1.0.0
	 * @return string The translatable display name.
	 */
	public static function get_name(): string;
}
