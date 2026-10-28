<?php
/**
 * Event Template Locations.
 *
 * @package    DeWittePrins\CoreFunctionality
 * @subpackage Modules\Events\Features\TemplateFallback
 * @since      1.0.0
 */

declare( strict_types=1 );

namespace DeWittePrins\CoreFunctionality\Modules\Events\Features\TemplateFallback;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use UnexpectedValueException;

use function array_keys;
use function class_exists;
use function get_stylesheet_directory;
use function get_template_directory;
use function is_dir;
use function str_contains;
use function str_ends_with;
use function str_starts_with;
use function strlen;
use function substr;
use function trailingslashit;
use function wp_normalize_path;

/**
 * Class TemplateLocations
 *
 * Where template overrides of The Events Calendar and Event Tickets are, in the order The Events
 * Calendar looks: child theme, parent theme, the CF templates folder; and which plugin each
 * override folder belongs to. Used by the template check and by the CF templates tab.
 *
 * @since 1.0.0
 */
final class TemplateLocations {

	/**
	 * Override folders (relative to a theme or the CF templates folder) and the plugin they override.
	 *
	 * @var array<string, array{class: string, views: string}>
	 */
	public const FOLDERS = array(
		'tribe/events/'       => array(
			'class' => 'Tribe__Events__Main',
			'views' => 'the-events-calendar/src/views/',
		),
		'tribe/events-pro/'   => array(
			'class' => 'Tribe__Events__Pro__Main',
			'views' => 'events-calendar-pro/src/views/',
		),
		'tribe/tickets/'      => array(
			'class' => 'Tribe__Tickets__Main',
			'views' => 'event-tickets/src/views/',
		),
		'tribe/tickets-plus/' => array(
			'class' => 'Tribe__Tickets_Plus__Main',
			'views' => 'event-tickets-plus/src/views/',
		),
		// Legacy (v1) views; Pro's v1 templates sit in its own src/views/pro/.
		'tribe-events/pro/'   => array(
			'class' => 'Tribe__Events__Pro__Main',
			'views' => 'events-calendar-pro/src/views/pro/',
		),
		'tribe-events/'       => array(
			'class' => 'Tribe__Events__Main',
			'views' => 'the-events-calendar/src/views/',
		),
	);

	/**
	 * Returns the CF templates folder: outside the plugin, so a release or update leaves it
	 * alone, and outside the uploads, where PHP files do not belong.
	 *
	 * @since  1.0.0
	 * @return string The folder, normalized, without a trailing slash.
	 */
	public static function cf_dir(): string {
		return wp_normalize_path( WP_CONTENT_DIR . '/core-functionality/templates' );
	}

	/**
	 * Returns the places to look, in lookup order.
	 *
	 * @since  1.0.0
	 * @param  bool $with_site Whether the CF templates folder takes part (the fallback feature runs).
	 * @return array<string, string> Source (child, parent, cf) => folder.
	 */
	public static function sources( bool $with_site ): array {
		$sources = array( 'child' => get_stylesheet_directory() );
		if ( get_template_directory() !== get_stylesheet_directory() ) {
			$sources['parent'] = get_template_directory();
		}
		if ( $with_site ) {
			$sources['cf'] = self::cf_dir();
		}

		return $sources;
	}

	/**
	 * Finds every override, per template path, in lookup order; the first copy is the one used.
	 *
	 * @since  1.0.0
	 * @param  bool $with_site Whether the CF templates folder takes part.
	 * @return array<string, array<int, array<string, string>>> Template path => copies (source, file, relative, views).
	 */
	public static function collect( bool $with_site ): array {
		$copies = array();
		foreach ( self::sources( $with_site ) as $source => $base ) {
			foreach ( self::FOLDERS as $folder => $plugin ) {
				foreach ( self::copies_in( $source, $base, $folder, $plugin ) as $relative => $copy ) {
					$copies[ $folder . $relative ][] = $copy;
				}
			}
		}

		return $copies;
	}

	/**
	 * Returns the overrides in one folder of one source, when the plugin they override is active.
	 *
	 * @since  1.0.0
	 * @param  string $source child, parent or cf.
	 * @param  string $base   The theme's or the CF templates folder.
	 * @param  string $folder The override folder, such as tribe/events/.
	 * @param  array  $plugin The plugin it overrides: its main class and its views folder.
	 * @return array<string, array<string, string>> Path relative to the folder => copy.
	 */
	private static function copies_in( string $source, string $base, string $folder, array $plugin ): array {
		$dir = trailingslashit( wp_normalize_path( $base ) ) . $folder;
		if ( ! class_exists( $plugin['class'] ) || ! is_dir( $dir ) ) {
			return array();
		}

		$copies = array();
		foreach ( self::php_files( $dir ) as $file ) {
			$relative = substr( $file, strlen( $dir ) );

			// tribe-events/pro/ has its own entry: keep it out of tribe-events/.
			if ( 'tribe-events/' === $folder && str_starts_with( $relative, 'pro/' ) ) {
				continue;
			}

			$copies[ $relative ] = array(
				'source'   => $source,
				'file'     => $file,
				'relative' => $relative,
				'views'    => trailingslashit( wp_normalize_path( WP_PLUGIN_DIR ) ) . $plugin['views'],
			);
		}

		return $copies;
	}

	/**
	 * Lists the PHP files under a folder.
	 *
	 * @since  1.0.0
	 * @param  string $dir The folder.
	 * @return string[] Normalized file paths.
	 */
	public static function php_files( string $dir ): array {
		if ( ! is_dir( $dir ) ) {
			return array();
		}

		$files = array();
		try {
			$iterator = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $dir, RecursiveDirectoryIterator::SKIP_DOTS ) );
			foreach ( $iterator as $file ) {
				if ( 'php' === $file->getExtension() ) {
					$files[] = wp_normalize_path( $file->getPathname() );
				}
			}
		} catch ( UnexpectedValueException $e ) {
			return array();
		}

		return $files;
	}

	/**
	 * Whether a path inside a zip or the CF templates folder is a template override this feature accepts:
	 * a PHP file in one of the override folders.
	 *
	 * @since  1.0.0
	 * @param  string $relative A path relative to the template folder.
	 * @return bool True when accepted.
	 */
	public static function is_template_path( string $relative ): bool {
		if ( ! str_ends_with( $relative, '.php' ) || str_contains( $relative, '..' ) ) {
			return false;
		}
		foreach ( array_keys( self::FOLDERS ) as $folder ) {
			if ( str_starts_with( $relative, $folder ) ) {
				return true;
			}
		}

		return false;
	}
}
