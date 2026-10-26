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
	 * @param string          $title        The full title: clear on its own, at the top of the menu or in the pool.
	 * @param string          $label        Human-readable name for the settings page.
	 * @param Orientation     $orientation  The kind of link: to an admin page or to a page on the site.
	 *                                      Where the node is shown is set per node in the menu builder.
	 * @param string          $parent       The parent toolbar node ID.
	 * @param string|callable $href         The target URL or a lazy loading callable.
	 * @param array           $meta         Optional metadata attributes.
	 * @param string          $short_title  The title under a parent of the same group, where the context is
	 *                                      clear ("New" under Plugins); empty: always the full title.
	 * @param string          $group        What the shortcut is about, when its provider covers more than
	 *                                      one subject (plugins, posts, venues); empty: its provider.
	 */
	public function __construct(
		public string $id,
		public string $title,
		public string $label,
		public Orientation $orientation = Orientation::ADMIN,
		public string $parent = 'site-name',
		public mixed $href = '#',
		public array $meta = array(),
		public string $short_title = '',
		public string $group = ''
	) {}

	/**
	 * Returns the title for a place in the menu: the short title under a parent of the same group,
	 * otherwise the full title.
	 *
	 * @param string $group        This shortcut's group (its own, or its provider when it has none).
	 * @param string $parent_group The group of the parent node; empty at the top or under a custom submenu.
	 * @return string The title.
	 */
	public function get_title_in( string $group, string $parent_group ): string {
		return '' !== $this->short_title && '' !== $parent_group && $group === $parent_group
			? $this->short_title
			: $this->title;
	}

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
