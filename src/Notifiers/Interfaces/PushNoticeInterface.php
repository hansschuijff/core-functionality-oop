<?php
declare(strict_types=1);

namespace DeWittePrins\CoreFunctionality\Notifiers\Interfaces;

/**
 * Interface PushNoticeInterface
 */
interface PushNoticeInterface {

	/**
	 * Initiates and executes the message transmission sequence.
	 */
	public function send( string $message, string $title = '' ): bool;

	/**
	 * Returns the onboarding configuration fields required by this platform driver.
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
