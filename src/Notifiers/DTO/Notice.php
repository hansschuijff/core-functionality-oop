<?php
/**
 * Individual Notice Data Transfer Object.
 *
 * @package    DeWittePrins\CoreFunctionality
 * @subpackage Notifiers\DTO
 * @author     Hans Schuijff <@hansschuijff>
 * @license    GPL-2.0
 * @since      1.0.0
 */

declare(strict_types=1);

namespace DeWittePrins\CoreFunctionality\Notifiers\DTO;

/**
 * Class Notice
 *
 * Immutable data transfer object representing a single alert state payload.
 *
 * @since 1.0.0
 */
readonly class Notice {

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 * @param string $type        The WordPress notice class type (success, error, warning, info).
	 * @param string $message     The translatable alert message content string.
	 * @param bool   $dismissible Optional. True if the banner should be closeable. Default true.
	 */
	public function __construct(
		public string $type,
		public string $message,
		public bool $dismissible = true
	) {}
}
