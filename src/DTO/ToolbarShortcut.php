<?php
/**
 * Toolbar Shortcut DTO.
 *
 * @package DeWittePrins\CoreFunctionality\DTO
 */

declare(strict_types=1);

namespace DeWittePrins\CoreFunctionality\DTO;

use DeWittePrins\CoreFunctionality\Enums\Orientation;

use function is_callable;

/**
 * Class ToolbarShortcut
 */
readonly class ToolbarShortcut {

	/**
	 * Constructor.
	 *
	 * @param string          $id           Unique sub-identifier.
	 * @param string          $title        The title rendered in the toolbar.
	 * @param string          $label        Human-readable name for the settings page.
	 * @param Orientation     $orientation  Context indicator ('frontend', 'admin').
	 * @param string          $parent       The parent toolbar node ID.
	 * @param string|callable $href         The target URL or a lazy loading callable.
	 * @param array           $meta         Optional metadata attributes.
	 */
	public function __construct(
		public string      $id,
		public string      $title,
		public string      $label,
		public Orientation $orientation = Orientation::ADMIN,
		public string      $parent = 'site-name',
		public mixed       $href = '#',
		public array       $meta = array()
	) {}

	/**
	 * Resolves the URL dynamically at runtime, evaluating closures if supplied.
	 *
	 * @return string The actual processed URL string.
	 */
	public function get_href(): string {
		return is_callable( $this->href ) ? (string) ( $this->href )() : (string) $this->href;
	}

	/**
	 * Formats the DTO for WordPress usage within WP_Admin_Bar.
	 */
	public function to_array(): array {
		return array(
			'id'     => $this->id,
			'title'  => $this->title,
			'parent' => $this->parent,
			'href'   => $this->get_href(), // Resolves the closure right before injection!
			'meta'   => $this->meta,
		);
	}
}
