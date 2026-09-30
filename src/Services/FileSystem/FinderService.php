<?php
/**
 * Centralized Filesystem Discovery and Scanning Service.
 *
 * @package    DeWittePrins\CoreFunctionality
 * @subpackage Services\FileSystem
 * @author  Hans Schuijff <@hansschuijff>
 * @license GPL-2.0
 * @since   1.0.0
 */

declare( strict_types=1 );

namespace DeWittePrins\CoreFunctionality\Services\FileSystem;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use DirectoryIterator;
use DeWittePrins\CoreFunctionality\Plugin;
use DeWittePrins\CoreFunctionality\ValueObjects\ModuleClassValidator;
use DeWittePrins\CoreFunctionality\ValueObjects\EnvironmentValidator;

use function _doing_it_wrong;
use function __;
use function esc_html;
use function is_admin;
use function sanitize_key;

/**
 * Class FinderService
 *
 * Responsibly traverses the disk directories using standard SPL iterators
 * to discover modules, features, and environment representatives on the fly.
 *
 * @since 1.0.0
 */
class FinderService {

	/**
	 * The full absolute disk path to where modules reside.
	 *
	 * @var string
	 */
	private string $modules_dir;

	/**
	 * The base root namespace mapped for discovered modules.
	 *
	 * @var string
	 */
	private string $modules_namespace;

	/**
	 * FinderService Constructor.
	 *
	 * @since 1.0.0
	 * @param Plugin $plugin Central plugin core orchestrator instance.
	 */
	public function __construct(
		private readonly Plugin $plugin,
	) {
		$this->modules_dir       = $this->untrailingslashit( $this->plugin->get_data( 'modules-dir' ) );
		$this->modules_namespace = $this->plugin->get_data( 'modules-namespace' );
	}

	/**
	 * Scans modules and features directories to rebuild the components register tree.
	 *
	 * @since  1.0.0
	 * @return array<string, array> Complete map of verified module FQCNs with feature arrays.
	 */
	public function find_modules_and_features(): array|false {
		// Guard: Enforce strict execution context conditions before hitting the file system.
		if ( ! $this->allowed_to_run() ) {
			return false;
		}

		if ( ! is_dir( $this->modules_dir ) ) {
			return false;
		}

		$modules = array();

		// Iterate through the master modules folder structure.
		foreach ( new DirectoryIterator( $this->modules_dir ) as $item ) {

			// Skip standard file artifacts and pointer dots.
			if ( ! $item->isDir() || $item->isDot() ) {
				continue;
			}

			/**
			 * The folder directory name directly correlates to the Classname Slug.
			 * Target FQCN Blueprint: DeWittePrins\CoreFunctionality\Modules\{module_slug}\{module_slug}
			 */
			$module_slug = $item->getFilename();
			$module_fqcn = "{$this->modules_namespace}\\{$module_slug}\\{$module_slug}";
			$module_dir  = $item->getPathname();

			// Gather nested feature subdirectory components.
			$feature_slugs = $this->get_feature_slugs( $module_dir );

			// Authorize structural validation protocols using the ModuleClassValidator Value Object.
			$validated_features = ModuleClassValidator::validate( $module_fqcn, $feature_slugs, $this->modules_namespace );

			// Store registered context maps if features validate successfully.
			if ( false !== $validated_features ) {
				$modules[ $module_fqcn ] = $validated_features;
			}
		}

		return $modules;
	}

	/**
	 * Dynamically discovers all available environment dependency representatives on disk.
	 *
	 * High-performance mapping utilizing native PHP SPL DirectoryIterators.
	 *
	 * @since  1.0.0
	 * @return string[] Array mapping discovered Fully Qualified Class Name strings.
	 */
	public function find_plugins_and_themes_representatives(): array {
		$base_dir  = rtrim( dirname( __FILE__, 3 ), '/\\' ) . '/Environment';
		$namespace = 'DeWittePrins\\CoreFunctionality\\Environment';

		$discovered_classes = array();

		// Add 'Core' to the discovery loop to automatically capture WordPress.php!
		// Leave 'Servers' out to keep the architecture light, fast, and focused.
		$subfolders = array( 'Core', 'Plugins', 'Themes' );

		foreach ( $subfolders as $subfolder ) {
			$target_path = $base_dir . '/' . $subfolder;

			if ( ! is_dir( $target_path ) ) {
				continue;
			}

			$iterator = new DirectoryIterator( $target_path );

			foreach ( $iterator as $fileinfo ) {
				if ( $fileinfo->isDot() || ! $fileinfo->isFile() ) {
					continue;
				}

				if ( 'php' !== $fileinfo->getExtension() ) {
					continue;
				}

				$class_slug = $fileinfo->getBasename( '.php' );

				if ( str_starts_with( $class_slug, 'Abstract' ) ) {
					continue;
				}

				// Synthesize the definitive Fully Qualified Class Name string.
				$class = $namespace . '\\' . $subfolder . '\\' . $class_slug;

				// Leverage the specialized Value Object to filter out invalid elements cleanly.
				if ( EnvironmentValidator::validate( $class ) ) {
					$discovered_classes[] = $class;
				}
			}
		}

		return $discovered_classes;
	}

	/**
	 * Scans the /Features/ subdirectory context of a targeted module path for feature candidates.
	 *
	 * Path Blueprint: modules-path/{module_slug}/Features/{feature_slug}/{feature_slug}.php
	 *
	 * @since  1.0.0
	 * @param  string $module_dir The absolute disk path to the targeted parent module directory.
	 * @return string[] Array containing found valid feature classname slugs.
	 */
	private function get_feature_slugs( string $module_dir ): array {
		$features_dir = $this->trailingslashit( $module_dir ) . 'Features';

		if ( ! is_dir( $features_dir ) ) {
			return array();
		}

		$features = array();

		foreach ( new DirectoryIterator( $features_dir ) as $sub_item ) {
			if ( ! $sub_item->isDir() || $sub_item->isDot() ) {
				continue;
			}

			$feature_slug = $sub_item->getFilename();
			$feature_file = $sub_item->getPathname() . '/' . $feature_slug . '.php';

			if ( file_exists( $feature_file ) ) {
				$features[] = $feature_slug;
			}
		}

		return $features;
	}

	/**
	 * Enforces that a directory path string consistently terminates with a standard forward slash.
	 *
	 * @since  1.0.0
	 * @param  string $path The targeted file directory path string.
	 * @return string Normalized path ending with a clean forward slash.
	 */
	private function trailingslashit( string $path ): string {
		return $this->untrailingslashit( $path ) . '/';
	}

	/**
	 * Strips trailing cross-platform forward and backward slashes uniformly from a path string.
	 *
	 * @since  1.0.0
	 * @param  string $path The raw file directory path string.
	 * @return string Cleaned path string without trailing slash artifacts.
	 */
	private function untrailingslashit( string $path ): string {
		return rtrim( $path, '/\\' );
	}

	/**
	 * Validates runtime execution circumstances to prevent unauthorized file operations.
	 *
	 * Enforces that heavy disk iterations are exclusively restricted to this plugin's admin settings scope.
	 * Supports both standard page renders (GET) and form submissions (POST).
	 *
	 * @since  1.0.0
	 * @return bool True if conditions are met, false otherwise triggering a WordPress core warning.
	 */
	private function allowed_to_run(): bool {
		// Execute if we are in admin and either rendering or saving this specific page context.
		if ( $this->is_cf_settings_page() ) {
			return true;
		}

		// Trigger core WP developer warning if called outside authorized architectural limits.
		_doing_it_wrong(
			__METHOD__,
			__( 'Invalid method call: FinderService->find_modules_and_features() should only be executed from within the Core Functionality admin settings page context.', 'dwp-cf' ),
			esc_html( $this->plugin->get_data( 'version' ) )
		);

		return false;
	}

	/**
	 * Validates if the current execution context corresponds to this plugin's settings page.
	 *
	 * Combines early lifecycle request indicators (GET/POST) with late lifecycle screen objects
	 * to guarantee a highly resilient context validation during all boot phases.
	 *
	 * @since  1.0.0
	 * @return bool True if currently executing within this settings page context, false otherwise.
	 */
	private function is_cf_settings_page(): bool {
		if ( ! is_admin() ) {
			return false;
		}

		// 1. Early-lifecycle checks: Validate incoming raw request payloads.
		$is_settings_render = isset( $_GET['page'] ) && 'dwp-cf-settings' === sanitize_key( $_GET['page'] );
		$is_settings_save   = isset( $_POST['action'] ) && 'dwp_cf_save_options' === sanitize_key( $_POST['action'] );

		if ( $is_settings_render || $is_settings_save ) {
			return true;
		}

		// 2. Late-lifecycle check: Fallback to the official WordPress screen matrix object.
		if ( ! function_exists( 'get_current_screen' ) ) {
			return false;
		}

		$screen = get_current_screen();

		return ( $screen && 'settings_page_dwp-cf-settings' === $screen->id );
	}
}
