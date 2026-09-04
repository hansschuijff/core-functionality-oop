<?php
/**
 * Pluggable OOP Settings Section Contract.
 *
 * @package DeWittePrins\CoreFunctionality\Contracts
 * @since   4.0.0
 */

namespace DeWittePrins\CoreFunctionality\Contracts;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use WP_Error;

/**
 * Interface SettingsSectionInterface
 *
 * Dictates the required lifecycle methods for any module or component
 * wishing to register an administrative configuration section.
 *
 * @since 4.0.0
 */
interface SettingsSectionInterface {

	/**
	 * Dictates the unique URL identifier token for this specific settings section.
	 *
	 * @since  4.0.0
	 * @return string Unique slug identifier.
	 */
	public static function get_id(): string;

	/**
	 * Dictates the human-readable navigation tab label.
	 *
	 * @since  4.0.0
	 * @return string Navigation title.
	 */
	public static function get_name(): string;

	/**
	 * Pre-flight validation gate checking inputs before any data hits the database.
	 *
	 * @since  4.0.0
	 * @param  array $posted_data Raw $_POST context forwarded by the host controller.
	 * @return bool|WP_Error True if cleared to save, or a WP_Error tracking failed rules.
	 */
	public function validate( array $posted_data ): bool|WP_Error;

	/**
	 * Persists the already sanitised and validated data records to the database.
	 *
	 * @since  4.0.0
	 * @param  array $posted_data Raw $_POST context.
	 * @return void
	 */
	public function save( array $posted_data ): void;

	/**
	 * Returns the absolute disk paths for the section-specific stylesheets.
	 *
	 * @since  4.0.0
	 * @return array<string, string> Array matching unique handles to absolute file paths.
	 */
	public function get_stylesheets(): array;

	/**
	 * Returns the absolute disk paths for the section-specific javascript files.
	 *
	 * @since  4.0.0
	 * @return array<string, string> Array matching unique handles to absolute file paths.
	 */
	public function get_scripts(): array;
}
