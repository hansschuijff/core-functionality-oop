<?php
/**
 * Core Settings Section Contract Interface.
 *
 * @package    DeWittePrins\CoreFunctionality
 * @subpackage Admin\Services\Settings\Interfaces
 * @author     Hans Schuijff <@hansschuijff>
 * @license    GPL-2.0
 * @since      1.0.0
 */

declare(strict_types=1);

namespace DeWittePrins\CoreFunctionality\Admin\Services\Settings\Interfaces;

use DeWittePrins\CoreFunctionality\Notifiers\DTO\Notice;
use WP_Error;

interface SettingsSectionInterface {

	/**
	 * Unique URL slug identifier.
	 *
	 * @return string The unique tab identifier slug.
	 */
	public static function get_id(): string;

	/**
	 * Returns an icon for the admin settings menu.
	 *
	 * @return string.
	 */
	public static function get_icon(): string;

	/**
	 * Navigation title for the tab menu.
	 *
	 * @return string The translated display name.
	 */
	public static function get_name(): string;

	/**
	 * Short description to explain to users what the section is for.
	 *
	 * @return string The description of the Settings Section.
	 */
	public static function get_description(): string;

	/**
	 * Returns section-specific stylesheets mapping handles to paths.
	 *
	 * @return array<string, string>
	 */
	public static function get_stylesheets(): array;

	/**
	 * Returns section-specific javascript mapping handles to paths.
	 *
	 * @return array<string, string>
	 */
	public static function get_scripts(): array;

	/**
	 * Outputs the raw HTML view canvas block.
	 *
	 * @param bool $defaults_only Optional. Force bypass of database overrides. Default false.
	 */
	public function render( bool $defaults_only = false ): void;

	/**
	 * Handles early inputs preprocessing validation checks.
	 *
	 * @param array $posted_data Raw $_POST context.
	 * @return bool|WP_Error True if valid, WP_Error object on failure.
	 */
	public function validate( array $posted_data ): bool|WP_Error;

	/**
	 * Processes section-specific options submission.
	 *
	 * Note: the save method of a section may render error notices,
	 * using $this->plugin->notices->add(), but not the success notice.
	 * A success message text may be returned for the caller to render.
	 *
	 * @param array $posted_data Raw $_POST context.
	 * @return string|bool Optional success notice text, or bool indicating the succes (true) or failure (false) of the save.
	 */
	public function save( array $posted_data ): string|bool;

	/**
	 * Delivers custom context-aware action buttons to the host administration factory footer container.
	 *
	 * @since  1.0.0
	 * @return array<string, string> Dictionary indexing raw HTML button element blocks strings.
	 */
	public function get_action_buttons(): array;

}
