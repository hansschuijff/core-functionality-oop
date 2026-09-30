<?php
/**
 * ThemeInspector Service Component.
 *
 * This file contains the ThemeInspector class, which evaluates the active
 * WordPress theme hierarchy and matches it against specific environment configurations.
 *
 * @package    DeWittePrins\CoreFunctionality
 * @subpackage Environment\Themes\Services
 * @author     Hans Schuijff <@hansschuijff>
 * @license    GPL-2.0
 * @since      1.0.0
 */

declare(strict_types=1);

namespace DeWittePrins\CoreFunctionality\Environment\Themes\Services;

use DeWittePrins\CoreFunctionality\Environment\DTO\ProofRequirement;
use DeWittePrins\CoreFunctionality\Environment\Plugins\Services\PluginInspector;
use WP_Theme;

use function function_exists;
use function strip_tags;
use function strtolower;
use function wp_get_theme;
use function wp_is_block_theme;

/**
 * Class ThemeInspector
 *
 * Inspects the active WordPress theme structure and matches it
 * against the configured environment requirements.
 *
 * @since 1.0.0
 */
class ThemeInspector {

	/**
	 * Checks if a specific theme is active.
	 *
	 * This method checks both the current active theme (child or standalone)
	 * and its parent theme if applicable to verify a match against the requirements.
	 *
	 * @since 1.0.0
	 *
	 * @param array{name: string, author?: string} $theme_config The configuration data of the theme.
	 * @return bool True if the theme matches either the active theme or its parent, false otherwise.
	 */
	public static function is_theme_active( array $theme_config ): bool {
		$author = $theme_config['author'] ?? '';

		if ( self::is_current_child_theme( $theme_config['name'], $author ) ) {
			return true;
		}

		if ( self::has_parent_theme() && self::is_parent_theme_active( $theme_config['name'], $author ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Checks if a theme is the currently active theme.
	 *
	 * Compares the provided name and optional author against the active theme.
	 * The matching is performed in a case-insensitive manner.
	 *
	 * @since 1.0.0
	 *
	 * @param string $theme_name   The exact expected name of the theme.
	 * @param string $theme_author Optional. The expected author of the theme. Default empty string.
	 * @return bool True if the current active theme matches the criteria, false otherwise.
	 */
	public static function is_current_child_theme( string $theme_name, string $theme_author = '' ): bool {
		$theme = wp_get_theme();
		if ( ! $theme->exists() ) {
			return false;
		}

		$current_author = strtolower( strip_tags( (string) $theme->get( 'Author' ) ) );

		return strtolower( (string) $theme->get( 'Name' ) ) === strtolower( $theme_name )
			&& ( ! $theme_author || $current_author === strtolower( $theme_author ) );
	}

	/**
	 * Checks if a theme is the parent of the currently active theme.
	 *
	 * Verifies if the active theme has a parent framework or template, and
	 * matches its properties against the given criteria (case-insensitive).
	 *
	 * @since 1.0.0
	 *
	 * @param string $theme_name   The exact expected name of the parent theme.
	 * @param string $theme_author Optional. The expected author of the parent theme. Default empty string.
	 * @return bool True if the active theme has a matching parent, false otherwise.
	 */
	public static function is_parent_theme_active( string $theme_name, string $theme_author = '' ): bool {
		$theme = wp_get_theme();
		if ( ! $theme->exists() || ! $theme->parent() ) {
			return false;
		}

		$parent_theme  = $theme->parent();
		$parent_author = strtolower( strip_tags( (string) $parent_theme->get( 'Author' ) ) );

		return strtolower( (string) $parent_theme->get( 'Name' ) ) === strtolower( $theme_name )
			&& ( ! $theme_author || $parent_author === strtolower( $theme_author ) );
	}

	/**
	 * Checks if the active theme has a parent theme template.
	 *
	 * Determines whether the currently running WordPress theme relies on a
	 * parent framework (meaning the current theme is a child theme).
	 *
	 * @since 1.0.0
	 *
	 * @return bool True if the active theme is a child theme, false otherwise.
	 */
	public static function has_parent_theme(): bool {
		$theme = wp_get_theme();
		return $theme->exists() && $theme->parent() instanceof WP_Theme;
	}

	/**
	 * Checks if the active theme is a Full Site Editing (FSE) block theme.
	 *
	 * @since 1.0.0
	 * @return bool True if the active theme is a block theme, false otherwise.
	 */
	public static function is_block_theme(): bool {
		return function_exists( 'wp_is_block_theme' ) ? wp_is_block_theme() : false;
	}

	/**
	 * Checks if the active theme is a legacy (classic) theme.
	 *
	 * @since 1.0.0
	 * @return bool True if the active theme is a classic theme, false otherwise.
	 */
	public static function is_legacy_theme(): bool {
		return ! self::is_block_theme();
	}

	/**
	 * Evaluates a structured ProofRequirement DTO to determine if theme features are fully loaded.
	 *
	 * Reuses the defensive proof validation engine from the PluginInspector.
	 *
	 * @since 1.0.0
	 * @param ProofRequirement $requirement The structured proof criteria object.
	 * @return bool True if all required elements pass, false otherwise.
	 */
	public static function must_exist( ProofRequirement $requirement ): bool {
		return PluginInspector::must_exist( $requirement );
	}
}
