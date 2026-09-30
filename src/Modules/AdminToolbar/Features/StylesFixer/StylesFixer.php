<?php
/**
 * Admin Toolbar Flexbox Wrapping Fix Feature.
 *
 * Admin functions to change the styles of WordPress Admin bar
 * so that it doesn't wrap when it gets crowded.
 *
 * @package DeWittePrins\CoreFunctionality\Modules\AdminToolbar\Features\StylesFixer
 * @since   1.0.0
 */

namespace DeWittePrins\CoreFunctionality\Modules\AdminToolbar\Features\StylesFixer;

use DeWittePrins\CoreFunctionality\Interfaces\FeatureInterface;
use DeWittePrins\CoreFunctionality\Interfaces\ModuleInterface;
use DeWittePrins\CoreFunctionality\Plugin;
use DeWittePrins\CoreFunctionality\Enums\Orientation;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class StylesFixer
 *
 * Prevents the native WordPress admin bar from wrapping on larger viewports.
 *
 * @since 1.0.0
 */
class StylesFixer implements FeatureInterface {

	/**
	 * Constructor.
	 *
	 * @param ModuleInterface $module Parent module context.
	 */
	public function __construct(
		private readonly ModuleInterface $module,
		private readonly Plugin $plugin
	) {}

	/**
	 * Returns the unique identification string for this feature toggle.
	 *
	 * @return string The unique micro-feature string key.
	 */
	public static function get_id(): string {
		return 'fix_admin_bar_styles';
	}

	/**
	 * Retrieves the human-readable name of the feature.
	 *
	 * @since  1.0.0
	 * @return string Module title.
	 */
	public static function get_name(): string {
		return 'Admin Toolbar Styles Fixer Feature.';
	}

	/**
	 * Retrieves the contextual description of what the feature provides.
	 *
	 * @since  1.0.0
	 * @return string Module description.
	 */
	public static function get_description(): string {
		return 'The Admin Toolbar Styles Fixer Feature solves some styling issues in the admin toolbar.';
	}

	/**
	 * Retrieves feature-specific environment prerequisites.
	 *
	 * @return string|array A single (string) dependency or a multi-dimensional preflight checks matrix.
	 */
	public static function get_dependencies(): string|array {
		return array();
	}

	/**
	 * Returns an array with internal dependencies with other features or modules.
	 *
	 * @since  1.0.0
	 * @return array<int, string> List of fully qualified feature class strings.
	 */
	public static function uses_features(): array {
		return array();
	}

	/**
	 * Requests the current usage-target of the feature (Frontend, Admin, Both).
	 *
	 * @since  1.0.0
	 * @return Orientation Enum indication if the feature is meant for use on the Frontend, Admin or both.
	 */
	public static function get_orientation(): Orientation {
		return Orientation::BOTH;
	}

	/**
	 * Launches the operational execution lifecycle for this micro-feature.
	 *
	 * @return void
	 */
	public function launch(): void {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_fix_styles' ), 9999 );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_fix_styles' ), 9999 );
	}

	/**
	 * Exposes the parent module instance.
	 *
	 * @return \DeWittePrins\CoreFunctionality\Interfaces\ModuleInterface
	 */
	public function get_module(): ModuleInterface {
		return $this->module;
	}

	/**
	 * Enqueues the flexbox override styles safely if the admin bar is active.
	 *
	 * @return void
	 */
	public function enqueue_fix_styles(): void {
		if ( ! is_admin_bar_showing() ) {
			return;
		}

		$url = plugins_url( 'assets/css/styles-fixer.css', __FILE__ );

		wp_enqueue_style(
			'dwp-admin-bar-wrap-fix',
			$url,
			array(),
			$this->plugin->get_data( 'version' )
		);
	}
}
