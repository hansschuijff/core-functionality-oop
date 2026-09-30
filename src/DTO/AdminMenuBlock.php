<?php
/**
 * Admin Menu Block DTO.
 *
 * @package DeWittePrins\CoreFunctionality\DTO
 */

declare(strict_types=1);

namespace DeWittePrins\CoreFunctionality\DTO;

/**
 * Class AdminMenuBlock
 */
readonly class AdminMenuBlock {

	/**
	 * Constructor.
	 *
	 * @param string          $id            Unique identifier for sorting.
	 * @param string          $label         Human readable label for the settings block pool.
	 * @param string|callable $page_title    Page title string or callable for runtime evaluation.
	 * @param string|callable $menu_title    Menu title string or callable for runtime evaluation.
	 * @param string          $capability    Required user capability.
	 * @param string          $menu_slug     The WordPress menu slug identifier.
	 * @param mixed           $callback      Menu content rendering callback.
	 * @param string          $group         Group taxonomy ('core', 'plugins', 'custom').
	 * @param string|null     $parent_slug   Parent menu slug if it represents a submenu.
	 * @param string          $icon          Dashicon class or custom icon link.
	 * @param int|null        $menu_position Target display position index.
	 */
	public function __construct(
		public string $id,
		public string $label,
		public mixed $page_title,
		public mixed $menu_title,
		public string $capability,
		public string $menu_slug,
		public mixed $callback,
		public string $group = 'custom',
		public ?string $parent_slug = null,
		public string $icon = 'dashicons-admin-generic',
		public ?int $menu_position = null
	) {}

	/**
	 * Resolves the page title string at runtime.
	 *
	 * @return string
	 */
	public function get_page_title(): string {
		return is_callable( $this->page_title ) ? (string) call_user_func( $this->page_title ) : $this->page_title;
	}

	/**
	 * Resolves the menu title string at runtime.
	 *
	 * @return string
	 */
	public function get_menu_title(): string {
		return is_callable( $this->menu_title ) ? (string) call_user_func( $this->menu_title ) : $this->menu_title;
	}
}
