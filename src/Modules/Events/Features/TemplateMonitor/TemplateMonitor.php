<?php
/**
 * Event Template Monitor Feature.
 *
 * @package    DeWittePrins\CoreFunctionality
 * @subpackage Modules\Events\Features\TemplateMonitor
 * @since      1.0.0
 */

declare( strict_types=1 );

namespace DeWittePrins\CoreFunctionality\Modules\Events\Features\TemplateMonitor;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use DeWittePrins\CoreFunctionality\Abstracts\AbstractFeature;
use DeWittePrins\CoreFunctionality\DTO\ComponentMeta;
use DeWittePrins\CoreFunctionality\Modules\Events\Features\TemplateFallback\TemplateFallback;
use Override;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use UnexpectedValueException;

use const HOUR_IN_SECONDS;

use function __;
use function add_action;
use function add_query_arg;
use function array_fill_keys;
use function array_filter;
use function array_keys;
use function class_exists;
use function current_user_can;
use function delete_transient;
use function esc_html;
use function esc_html__;
use function esc_url;
use function fclose;
use function file_exists;
use function fopen;
use function fread;
use function get_stylesheet_directory;
use function get_template_directory;
use function get_transient;
use function is_admin;
use function is_array;
use function is_dir;
use function is_readable;
use function md5_file;
use function preg_match;
use function remove_query_arg;
use function sanitize_key;
use function set_transient;
use function sprintf;
use function str_contains;
use function str_starts_with;
use function strlen;
use function substr;
use function trailingslashit;
use function version_compare;
use function wp_nonce_url;
use function wp_normalize_path;
use function wp_safe_redirect;
use function wp_unslash;
use function wp_verify_nonce;

/**
 * Class TemplateMonitor
 *
 * Warns administrators when a template override of The Events Calendar or Event Tickets is out of
 * date, or overrides a template the plugin no longer has. Per template it checks the version the
 * site actually uses, found as The Events Calendar finds it (child theme, parent theme, then this
 * plugin's fallback folder), against the plugin's original.
 *
 * Also reported, as less urgent: a fallback template of this plugin that is out of date while a
 * theme overrides it (it is used again after a theme switch), and a theme copy identical to the
 * fallback (it can go). A template marked @custom-template has no original by design.
 *
 * Runs in the admin only, for users who manage plugins; the result is kept for 12 hours and
 * renewed after updates, a theme switch, or the notice's rescan button.
 *
 * @since 1.0.0
 */
class TemplateMonitor extends AbstractFeature {

	/**
	 * The transient holding the last result.
	 *
	 * @var string
	 */
	private const TRANSIENT = 'dwp_cf_event_template_report';

	/**
	 * The query arg and nonce action of the rescan button.
	 *
	 * @var string
	 */
	private const RESCAN = 'dwp_cf_rescan_event_templates';

	/**
	 * Override folders (relative to a theme or the fallback folder) and the plugin they override.
	 *
	 * @var array<string, array{class: string, views: string}>
	 */
	private const FOLDERS = array(
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
	 * Returns the descriptive data of this feature.
	 *
	 * @since  1.0.0
	 * @return ComponentMeta Id, name, description and requirements.
	 */
	#[Override]
	public static function get_meta(): ComponentMeta {
		return new ComponentMeta(
			id:          'events_template_monitor',
			name:        __( 'Event template check', 'core-functionality' ),
			description: __( 'Warns when a template override of The Events Calendar or Event Tickets is out of date or overrides a template the plugin no longer has.', 'core-functionality' ),
		);
	}

	/**
	 * Hooks the check into the admin, and clears the result after updates and theme switches.
	 *
	 * @since  1.0.0
	 * @return void
	 */
	#[Override]
	public function launch(): void {
		add_action( 'upgrader_process_complete', array( $this, 'clear' ) );
		add_action( 'after_switch_theme', array( $this, 'clear' ) );

		if ( ! is_admin() ) {
			return;
		}

		add_action( 'admin_init', array( $this, 'check' ) );
	}

	/**
	 * Forgets the last result, so the next admin page scans again.
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public function clear(): void {
		delete_transient( self::TRANSIENT );
	}

	/**
	 * Handles the rescan button, gets the (kept) result and shows it when there is anything to report.
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public function check(): void {
		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Verified right below.
		if ( isset( $_GET[ self::RESCAN ], $_GET['_wpnonce'] ) && wp_verify_nonce( sanitize_key( wp_unslash( $_GET['_wpnonce'] ) ), self::RESCAN ) ) {
			$this->clear();
			wp_safe_redirect( remove_query_arg( array( self::RESCAN, '_wpnonce' ) ) );
			exit;
		}

		$report = get_transient( self::TRANSIENT );
		if ( ! is_array( $report ) ) {
			$report = $this->scan();
			set_transient( self::TRANSIENT, $report, 12 * HOUR_IN_SECONDS );
		}

		if ( array() === $report ) {
			return;
		}

		add_action( 'admin_notices', fn () => $this->render_notice( $report ) );
	}

	/**
	 * Scans the override folders and returns what to report.
	 *
	 * @since  1.0.0
	 * @return array<int, array<string, string>> The findings: type, source, path and versions.
	 */
	private function scan(): array {
		$copies = $this->collect_copies();
		$report = array();

		foreach ( $copies as $path => $found ) {
			$used     = $found[0];
			$original = $used['views'] . $used['relative'];

			// What the site uses: missing in the plugin, or older than the plugin's.
			if ( ! file_exists( $original ) ) {
				if ( ! $this->is_custom( $used['file'] ) ) {
					$report[] = $this->finding( 'missing', $used['source'], $path );
				}
			} elseif ( $this->is_older( $used['file'], $original ) ) {
				$report[] = $this->finding( 'outdated', $used['source'], $path, $this->version( $used['file'] ), $this->version( $original ) );
			}

			$fallback = $this->find_source( $found, 'fallback' );
			if ( null === $fallback || 'fallback' === $used['source'] ) {
				continue;
			}

			// A fallback a theme overrides now, but used again after a theme switch.
			if ( file_exists( $original ) && $this->is_older( $fallback['file'], $original ) ) {
				$report[] = $this->finding( 'fallback-outdated', 'fallback', $path, $this->version( $fallback['file'] ), $this->version( $original ) );
			}

			// Theme copies identical to the fallback can go.
			foreach ( $found as $copy ) {
				if ( 'fallback' !== $copy['source'] && md5_file( $copy['file'] ) === md5_file( $fallback['file'] ) ) {
					$report[] = $this->finding( 'redundant', $copy['source'], $path );
				}
			}
		}

		return $report;
	}

	/**
	 * Finds every override, per template path, in lookup order: child theme, parent theme, fallback.
	 *
	 * @since  1.0.0
	 * @return array<string, array<int, array<string, string>>> Template path => copies (source, file, relative, views).
	 */
	private function collect_copies(): array {
		$sources = array( 'child' => get_stylesheet_directory() );
		if ( get_template_directory() !== get_stylesheet_directory() ) {
			$sources['parent'] = get_template_directory();
		}
		if ( TemplateFallback::is_running() ) {
			$sources['fallback'] = TemplateFallback::get_templates_dir( $this->plugin->get_data( 'plugin-dir' ) );
		}

		$copies = array();
		foreach ( $sources as $source => $base ) {
			foreach ( self::FOLDERS as $folder => $plugin ) {
				foreach ( $this->copies_in( $source, $base, $folder, $plugin ) as $relative => $copy ) {
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
	 * @param  string $source child, parent or fallback.
	 * @param  string $base   The theme's or the fallback's folder.
	 * @param  string $folder The override folder, such as tribe/events/.
	 * @param  array  $plugin The plugin it overrides: its main class and its views folder.
	 * @return array<string, array<string, string>> Path relative to the folder => copy.
	 */
	private function copies_in( string $source, string $base, string $folder, array $plugin ): array {
		$dir = trailingslashit( wp_normalize_path( $base ) ) . $folder;
		if ( ! class_exists( $plugin['class'] ) || ! is_dir( $dir ) ) {
			return array();
		}

		$copies = array();
		foreach ( $this->php_files( $dir ) as $file ) {
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
	private function php_files( string $dir ): array {
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
	 * Returns the copy from one source, if there is one.
	 *
	 * @since  1.0.0
	 * @param  array  $found  The copies of one template.
	 * @param  string $source The source.
	 * @return array|null The copy.
	 */
	private function find_source( array $found, string $source ): ?array {
		foreach ( $found as $copy ) {
			if ( $source === $copy['source'] ) {
				return $copy;
			}
		}

		return null;
	}

	/**
	 * Whether a template has an older @version than the original.
	 *
	 * @since  1.0.0
	 * @param  string $file     The override.
	 * @param  string $original The plugin's template.
	 * @return bool True when both have a version and the override's is lower.
	 */
	private function is_older( string $file, string $original ): bool {
		$version  = $this->version( $file );
		$original = $this->version( $original );

		return '' !== $version && '' !== $original && version_compare( $version, $original, '<' );
	}

	/**
	 * Returns the @version of a template, from its first 2 kB.
	 *
	 * @since  1.0.0
	 * @param  string $file The template.
	 * @return string The version, or ''.
	 */
	private function version( string $file ): string {
		return 1 === preg_match( '/@version\s+([0-9.]+)/i', $this->head( $file ), $matches ) ? $matches[1] : '';
	}

	/**
	 * Whether a template is marked as having no original (@custom-template).
	 *
	 * @since  1.0.0
	 * @param  string $file The template.
	 * @return bool True when marked.
	 */
	private function is_custom( string $file ): bool {
		return str_contains( $this->head( $file ), '@custom-template' );
	}

	/**
	 * Reads the first 2 kB of a file, where the header is.
	 *
	 * @since  1.0.0
	 * @param  string $file The file.
	 * @return string The start of the file, or ''.
	 */
	private function head( string $file ): string {
		if ( ! is_readable( $file ) ) {
			return '';
		}
		$handle = fopen( $file, 'r' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fopen -- Only the header, not the whole file.
		if ( false === $handle ) {
			return '';
		}
		$head = (string) fread( $handle, 2048 ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fread
		fclose( $handle ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose

		return $head;
	}

	/**
	 * Builds one finding.
	 *
	 * @since  1.0.0
	 * @param  string $type     missing, outdated, fallback-outdated or redundant.
	 * @param  string $source   child, parent or fallback.
	 * @param  string $path     The template path, such as tribe/events/v2/list/event/venue.php.
	 * @param  string $version  The override's version, or ''.
	 * @param  string $original The plugin's version, or ''.
	 * @return array<string, string> The finding.
	 */
	private function finding( string $type, string $source, string $path, string $version = '', string $original = '' ): array {
		return array(
			'type'     => $type,
			'source'   => $source,
			'path'     => $path,
			'version'  => $version,
			'original' => $original,
		);
	}

	/**
	 * Shows the findings grouped by kind, each group a heading with its templates below it: what
	 * the site uses first, then what matters after a theme switch, then theme copies that can go.
	 *
	 * @since  1.0.0
	 * @param  array $report The findings.
	 * @return void
	 */
	private function render_notice( array $report ): void {
		$rescan = wp_nonce_url( add_query_arg( self::RESCAN, '1' ), self::RESCAN );

		echo '<div class="notice notice-warning"><p><strong>' . esc_html__( 'Event templates', 'core-functionality' ) . '</strong> ';
		echo '<a class="button button-small" href="' . esc_url( $rescan ) . '">' . esc_html__( 'Scan again', 'core-functionality' ) . '</a></p>';

		foreach ( $this->group( $report ) as $heading => $items ) {
			echo '<p>' . esc_html( $heading ) . '</p><ul style="list-style: disc; margin-left: 2em;">';
			foreach ( $items as $item ) {
				echo '<li><code>' . esc_html( $item ) . '</code></li>';
			}
			echo '</ul>';
		}

		echo '</div>';
	}

	/**
	 * Sorts the findings into groups, in the order they are shown.
	 *
	 * @since  1.0.0
	 * @param  array $report The findings.
	 * @return array<string, string[]> Heading => one line per template.
	 */
	private function group( array $report ): array {
		$sources  = array(
			'child'    => __( 'child theme', 'core-functionality' ),
			'parent'   => __( 'parent theme', 'core-functionality' ),
			'fallback' => __( 'Core Functionality', 'core-functionality' ),
		);
		$headings = array(
			'outdated'          => __( 'These templates are out of date:', 'core-functionality' ),
			'missing'           => __( 'These templates override one the plugin no longer has (moved or removed):', 'core-functionality' ),
			'fallback-outdated' => __( 'These templates in Core Functionality are out of date. A theme overrides them now, but they are used again after a theme switch:', 'core-functionality' ),
			'redundant-child'   => __( 'These templates in the child theme are the same as in Core Functionality and can be removed:', 'core-functionality' ),
			'redundant-parent'  => __( 'These templates in the parent theme are the same as in Core Functionality and can be removed:', 'core-functionality' ),
		);

		$groups = array_fill_keys( array_keys( $headings ), array() );
		foreach ( $report as $finding ) {
			$where = $sources[ $finding['source'] ] ?? $finding['source'];
			$key   = 'redundant' === $finding['type'] ? 'redundant-' . $finding['source'] : $finding['type'];

			$groups[ $key ][] = match ( $finding['type'] ) {
				/* translators: 1: template path, 2: where it is (child theme, parent theme, Core Functionality), 3: its version, 4: the plugin's version. */
				'outdated'          => sprintf( __( '%1$s (%2$s): v%3$s, the plugin has v%4$s', 'core-functionality' ), $finding['path'], $where, $finding['version'], $finding['original'] ),
				/* translators: 1: template path, 2: where it is. */
				'missing'           => sprintf( __( '%1$s (%2$s)', 'core-functionality' ), $finding['path'], $where ),
				/* translators: 1: template path, 2: its version, 3: the plugin's version. */
				'fallback-outdated' => sprintf( __( '%1$s: v%2$s, the plugin has v%3$s', 'core-functionality' ), $finding['path'], $finding['version'], $finding['original'] ),
				default             => $finding['path'],
			};
		}

		$grouped = array();
		foreach ( array_filter( $groups ) as $key => $items ) {
			$grouped[ $headings[ $key ] ] = $items;
		}

		return $grouped;
	}
}
