<?php
/**
 * Core Feature Activation Settings Section Component.
 *
 * @package DeWittePrins\CoreFunctionality\Services\Settings
 * @since   4.0.0
 */

namespace DeWittePrins\CoreFunctionality\Services\SettingsSections;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use WP_Error;
use DeWittePrins\CoreFunctionality\Plugin;
use DeWittePrins\CoreFunctionality\Services\Settings;
use DeWittePrins\CoreFunctionality\Contracts\SettingsSectionInterface;

/**
 * Class FeatureActivationSettings
 */
class FeatureActivationSettings implements SettingsSectionInterface {

	/**
	 * Central plugin controller reference.
	 *
	 * @var Plugin
	 */
	private Plugin $plugin;

	/**
	 * Isolated database storage service layer reference.
	 *
	 * @var Settings
	 */
	private Settings $settings;

	/**
	 * FeatureActivationSettings Constructor.
	 *
	 * @since 4.0.0
	 * @param Plugin   $plugin   The central orchestrator instance.
	 * @param Settings $settings De specifieke database kluis voor schrijf-acties.
	 */
	public function __construct( Plugin $plugin, Settings $settings ) {
		$this->plugin   = $plugin;
		$this->settings = $settings;
	}

	/**
	 * URL slug identifier.
	 */
	public static function get_id(): string {
		return 'cf-feature-activation';
	}

	/**
	 * Navigation title.
	 */
	public static function get_name(): string {
		return __( 'Onderdelen', 'dwp-cf' );
	}

	/**
	 * Returns the absolute disk paths for the section-specific stylesheets.
	 *
	 * @since  4.0.0
	 * @return array<string, string> Array matching unique handles to absolute file paths.
	 */
	public function get_stylesheets(): array {
		return array(
			'dwp-cf-core-activation-css' => $this->plugin->get_data( 'plugin-dir' ) . 'assets/css/admin-settings.css',
		);
	}

	/**
	 * Returns the absolute disk paths for the section-specific javascript files.
	 *
	 * @since  4.0.0
	 * @return array<string, string> Array matching unique handles to absolute file paths.
	 */
	public function get_scripts(): array {
		return array(
			'dwp-cf-core-activation-js' => $this->plugin->get_data( 'plugin-dir' ) . 'assets/js/admin-settings.js',
		);
	}

	/**
	 * Outputs the wrapping HTML matrix shell.
	 */
	public function render(): void {
		?>
		<div class="dewitteprins-global-actions" style="margin: 20px 0;">
			<button type="button" class="button button-secondary js-dwp-toggle-all" data-state="select">
				<?php esc_html_e( 'Alles inschakelen', 'dwp-cf' ); ?>
			</button>
		</div>

		<div class="dwp-modules-grid">
			<?php $this->render_modules_form_fields(); ?>
		</div>
		<?php
	}

	/**
	 * Iterates over registered modules and features to output the visual cards grid.
	 */
	private function render_modules_form_fields(): void {
		$registered_modules = $this->plugin->config->get( 'modules' );

		if ( ! is_array( $registered_modules ) ) {
			return;
		}

		foreach ( $registered_modules as $module_class ) {
			if ( ! class_exists( $module_class ) ) {
				continue;
			}

			$module_id      = $module_class::get_id();
			$module_name    = $module_class::get_name();
			$module_enabled = $this->settings->is_module_enabled( $module_id );
			?>
			<div class="dwp-module-card" style="border: 1px solid #ccd0d4; padding: 20px; margin-bottom: 20px; background: #fff; max-width: 800px;">
				<label style="font-size: 15px; font-weight: bold; display: block; margin-bottom: 5px;">
					<input type="checkbox" class="js-dwp-module-toggle" name="dwp_modules[<?php echo esc_attr( $module_id ); ?>][enabled]" value="1" <?php checked( $module_enabled, true ); ?> />
					<?php echo esc_html( $module_name ); ?>
				</label>

				<?php
				$features = $module_class::get_features();
				if ( is_array( $features ) && ! empty( $features ) ) :
					?>
					<div class="js-dwp-features-container" style="margin-left: 25px; border-left: 2px solid #2271b1; padding-left: 15px; margin-top: 10px;">
						<p style="font-weight: 600; margin-top: 0; margin-bottom: 10px;"><?php esc_html_e( 'Beschikbare onderdelen:', 'dwp-cf' ); ?></p>
						<?php
						foreach ( $features as $feature_class ) {
							if ( ! class_exists( $feature_class ) ) {
								continue;
							}

							$feature_id      = $feature_class::get_id();
							$feature_name    = $feature_class::get_name();
							$feature_enabled = $this->settings->is_feature_enabled( $module_id, $feature_id );
							?>
							<label style="display: block; margin-bottom: 8px;">
								<input type="checkbox" class="js-dwp-feature-toggle" name="dwp_modules[<?php echo esc_attr( $module_id ); ?>][features][<?php echo esc_attr( $feature_id ); ?>]" value="1" <?php checked( $feature_enabled, true ); ?> />
								<?php echo esc_html( $feature_name ); ?>
							</label>
							<?php
						}
						?>
					</div>
				<?php endif; ?>
			</div>
			<?php
		}
	}

	/**
	 * Pre-flight validation gate checking inputs before database insertion.
	 *
	 * @since 1.0.0
	 * @param array $posted_data The form data that was submitted.
	 */
	public function validate( array $posted_data ): bool|WP_Error {
		return true;
	}

	/**
	 * Sanitises, validates, and persists the high-level module hierarchy.
	 *
	 * @since 1.0.0
	 * @param array $posted_data The form data that was submitted.
	 */
	public function save( array $posted_data ): void {
		$registered_modules = $this->plugin->config->get( 'modules' );
		if ( ! is_array( $registered_modules ) ) {
			return;
		}

		$sanitised_settings = array();
		$modules_input      = isset( $posted_data['dwp_modules'] ) && is_array( $posted_data['dwp_modules'] ) ? $posted_data['dwp_modules'] : array();

		foreach ( $registered_modules as $module_class ) {
			if ( ! class_exists( $module_class ) ) {
				continue;
			}

			$module_id                        = $module_class::get_id();
			$sanitised_settings[ $module_id ] = array(
				'enabled'  => isset( $modules_input[ $module_id ]['enabled'] ),
				'features' => array(),
			);

			$features = $module_class::get_features();
			if ( is_array( $features ) ) {
				foreach ( $features as $feature_class ) {
					if ( ! class_exists( $feature_class ) ) {
						continue;
					}

					$feature_id = $feature_class::get_id();
					$sanitised_settings[ $module_id ]['features'][ $feature_id ] = isset( $modules_input[ $module_id ]['features'][ $feature_id ] );
				}
			}
		}

		$this->settings->save( $sanitised_settings );
	}
}
