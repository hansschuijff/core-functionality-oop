<?php
/**
 * Admin Toolbar Flexbox Wrapping Fix Feature.
 *
 * @package DeWittePrins\CoreFunctionality\Modules\AdminToolbar\Features
 * @since   1.0.0
 */

namespace DeWittePrins\CoreFunctionality\Modules\AdminToolbar\Features;

use DeWittePrins\CoreFunctionality\Contracts\FeatureInterface;
use DeWittePrins\CoreFunctionality\Contracts\ModuleInterface;
use DeWittePrins\CoreFunctionality\Traits\OperationalState;
use DeWittePrins\CoreFunctionality\Plugin;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class FixAdminBarStyles
 *
 * Prevents the native WordPress admin bar from wrapping on larger viewports.
 *
 * @since 1.0.0
 */
class FixAdminBarStyles implements FeatureInterface {

	use OperationalState;

	/**
	 * Central root plugin container object.
	 *
	 * @var \DeWittePrins\CoreFunctionality\Plugin
	 */
	private Plugin $plugin;

	/**
	 * Parent module container instance.
	 *
	 * @var \DeWittePrins\CoreFunctionality\Contracts\ModuleInterface
	 */
	private ModuleInterface $module;

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
	 * @return array Multi-dimensional preflight checks matrix.
	 */
	public static function get_preflight_checks(): array {
		return array();
	}

	/**
	 * FixAdminBarStyles constructor.
	 *
	 * @param \DeWittePrins\CoreFunctionality\Contracts\ModuleInterface $module Parent module context.
	 */
	public function __construct( ModuleInterface $module ) {
		$this->module = $module;
		$this->plugin = $module->get_plugin();
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
	 * @return \DeWittePrins\CoreFunctionality\Contracts\ModuleInterface
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

		$style_url = $this->plugin->get_data( 'plugin-url' ) . 'src/Modules/AdminToolbar/Assets/css/styles.css';

		wp_enqueue_style(
			'dwp-admin-bar-wrap-fix',
			$style_url,
			array(),
			$this->plugin->get_data( 'version' )
		);
	}
}
