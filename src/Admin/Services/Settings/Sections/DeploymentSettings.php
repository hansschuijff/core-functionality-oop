<?php
/**
 * Release and Software Status Dashboard Settings Section.
 *
 * @package    DeWittePrins\CoreFunctionality
 * @subpackage Admin\Services\Settings\Sections
 * @author     Hans Schuijff <@hansschuijff>
 * @license    GPL-2.0
 * @since      1.0.0
 */

declare( strict_types=1 );

namespace DeWittePrins\CoreFunctionality\Admin\Services\Settings\Sections;

use Override;
use DeWittePrins\CoreFunctionality\Plugin;
use DeWittePrins\CoreFunctionality\Enums\Deployment;
use DeWittePrins\CoreFunctionality\Enums\Register;
use DeWittePrins\CoreFunctionality\Services\ClassParser;
use DeWittePrins\CoreFunctionality\Interfaces\FeatureInterface;
use DeWittePrins\CoreFunctionality\Interfaces\ModuleInterface;
use DeWittePrins\CoreFunctionality\Admin\Services\Settings\Interfaces\SettingsSectionInterface;

use function __;
use function file_exists;
use function file_get_contents;
use function file_put_contents;
use function json_decode;
use function json_encode;
use function class_exists;
use function in_array;
use function esc_html;
use function esc_attr;
use function selected;
use function is_array;

/**
 * Class DeploymentSettings
 *
 * Manages software release state transitions by overriding production flags inside a physical JSON database.
 *
 * @package DeWittePrins\CoreFunctionality\Admin\Services\Settings\Sections
 * @since   1.0.0
 */
class DeploymentSettings implements SettingsSectionInterface {

	/**
	 * Default deployment Enum.
	 *
	 * @var Deployment
	 */
	private Deployment $default_deployment = Deployment::DEVELOPMENT;

	/**
	 * DeploymentSettings Constructor.
	 *
	 * @since 1.0.0
	 * @param Plugin $plugin The central plugin class instance acting as a provider.
	 */
	public function __construct(
		private readonly Plugin $plugin,
	) {}

	/**
	 * Returns the unique section identifier string used for array mapping and URL routing.
	 *
	 * @since  1.0.0
	 * @return string The unique settings section ID slug.
	 */
	#[Override]
	public static function get_id(): string {
		return 'cf-release-management';
	}

	/**
	 * Returns an icon for the admin settings menu.
	 *
	 * @return string.
	 */
	#[Override]
	public static function get_icon(): string {
		return 'dashicons-buddicons-replies';
	}

	/**
	 * Returns the translatable tab name displayed inside the admin navigation bar.
	 *
	 * @since  1.0.0
	 * @return string The navigation tab display text label.
	 */
	#[Override]
	public static function get_name(): string {
		return __( 'Deployment Management', 'dwp-cf' );
	}

	/**
	 * Short description to explain to users what this section is for.
	 *
	 * @return string The description of this Settings Section.
	 */
	#[Override]
	public static function get_description(): string {
		return __( 'Here you can manage the deployment scope of modules and features. It gives allowance to be launched in the corresponding or lower environments (development, staging, production). Although you can set a feature to a higher deployment than the module it is part of, the feature will not be launched beyond the deployment scope of the parent module. Only if both module and feature are set to production deployment, the feature will be launched in a production environment.', 'dwp-cf' );
	}

	/**
	 * Stylesheets required for this tab.
	 */
	#[Override]
	public static function get_stylesheets(): array {
		return array();
	}

	/**
	 * JavaScript files required for this tab.
	 */
	#[Override]
	public static function get_scripts(): array {
		return array();
	}

	/**
	 * Renders the HTML table workspace inside the central application admin tab framework layout container.
	 *
	 * @since  1.0.0
	 * @param  bool $reset_to_defaults Optional. Bypasses live configs if rendering factory states is active.
	 * @return void
	 */
	#[Override]
	public function render( bool $reset_to_defaults = false ): void {

		// Get the current deployment settings of modules and features.
		$deployments = $this->get_deployments( $reset_to_defaults );

		// Get the complete list (array) of modules and features.
		$components = $this->plugin->components_register->get_register( Register::COMPONENTS );

		?>
		<div class="dwp-release-dashboard-wrapper">
			<?php
			// Render a list of modules and features with a select in which the current deployment can be set.
			foreach ( $components as $module_fqcn => $feature_classes ) {
				echo $this->get_render_module( $module_fqcn, $feature_classes, $deployments );
			}
			?>
		</div>
		<?php
	}

	/**
	 * Renders and returns the html for a single module with it's features.
	 *
	 * @param string $module_fqcn     Fully Qualified Class Name of a module.
	 * @param array  $feature_classes An array of Fully Qualified Class Name of the features of a module.
	 * @param array  $deployments     An array filled with the current deployment of features and modules.
	 * @return string The rendered html or a module row.
	 */
	private function get_render_module( string $module_fqcn, array $feature_classes, array $deployments ): string {

		$module_deployment = $deployments[ $module_fqcn ] ?? $this->default_deployment->value;

		$html = $this->get_render_module_open( $module_fqcn, $module_deployment );

		foreach ( $feature_classes as $feature_class ) {

			$feature_deployment = $deployments[ $feature_class ] ?? $this->default_deployment->value;

			$html .= $this->get_render_feature_row( $feature_class, $feature_deployment );
		}

		$html .= $this->get_render_module_close();

		return $html;
	}

	/**
	 * Renders and returns the top html for a single module.
	 *
	 * @param string $module_fqcn       Fully Qualified Class Name of the target module.
	 * @param array  $module_deployment The current deployment of the target module.
	 * @return string The rendered html.
	 */
	private function get_render_module_open( string $module_fqcn, string $module_deployment ): string {
		if ( ! ClassParser::is_module_fqcn( $module_fqcn ) ) {
			return '';
		}

		// $module_slug          = ClassParser::fqcn_remove_namespace( $module_fqcn );
		$module_name          = esc_html( $module_fqcn::get_name() );
		$module_description   = esc_html( $module_fqcn::get_description() ?? '' );

		return <<<HTML
			<table class="wp-list-table widefat fixed striped" style="margin-bottom: 35px; border: 1px solid #ccd0d4; box-shadow: 0 1px 1px rgba(0,0,0,.04);">
				<thead>
					<tr style="background-color: #f0f0f0;">
						<th style="font-weight: 700; font-size: 20px; padding: 15px;">
							<span style="color: #23282d;">{$module_name}</span>
							<br>
							<div style="font-weight: 400; color: #666; font-size: 15px; margin-top: 4px;">{$module_description}</div>
						</th>
						<th style="width: 250px; padding: 15px; text-align: right; vertical-align: middle;">
							{$this->get_render_deployment_select( $module_fqcn, $module_deployment )}
						</th>
					</tr>
				</thead>
				<tbody>
		HTML;
	}

	/**
	 * Renders and returns a row for a feature.
	 *
	 * @param string $feature_fqcn       Fully Qualified Class Name of a feature.
	 * @param string $feature_deployment Current deployment string a feature.
	 * @return string The rendered html or a feature row.
	 */
	private function get_render_feature_row( string $feature_fqcn, string $feature_deployment ): string {

		if ( ! ClassParser::is_feature_fqcn( $feature_fqcn ) ) {
			return '';
		}

		// $feature_slug         = ClassParser::fqcn_remove_namespace( $feature_fqcn );
		$feature_name         = esc_html( $feature_fqcn::get_name() );
		// $feature_fqcn_trunc  = substr( $feature_fqcn, strlen( $this->plugin->get_data( 'plugin-namespace' ) ) + 1 );

		$html = '';
		$html .= <<<HTML
			<tr>
				<td style="padding: 15px 15px 15px 25px; vertical-align: top;">
					<strong style="font-size: 16px; color: #1d2327; display: block; margin-bottom: 4px;">
						{$feature_name}
					</strong>
		HTML;

		$html .= $this->get_render_feature_description( $feature_fqcn );

		$html .=  <<<HTML
				</td>
				<td style="padding: 15px; width: 250px; vertical-align: middle; text-align: right;">
					{$this->get_render_deployment_select( $feature_fqcn, $feature_deployment )}
				</td>
			</tr>
		HTML;

		return $html;
	}

	/**
	 * Render and return a feature's description html.
	 *
	 * @param string $feature_fqcn Fully Qualified Class Name of a feature.
	 * @return string The rendered html or a feature row.
	 */
	private function get_render_feature_description( string $feature_fqcn ): string {
		$feature_description  = esc_html( $feature_fqcn::get_description() ?? '' );
		if ( empty( $feature_description ) ) {
			return '';
		}

		return <<<HTML
			<p class="description" style="margin: 6px 0 0 0; font-size: 14px; color: #4f5d66; line-height: 1.5;">
				{$feature_description}
			</p>
		HTML;
	}

	/**
	 * Renders the html for a deployment scope select control (development, staging, production)
	 *
	 * @param string $fqcn Fully Qualified Class Name of a module or feature.
	 * @param string $current_deployment Current deployment string of the mmodule or class.
	 * @return string Select controll html.
	 */
	private function get_render_deployment_select( string $fqcn, string $current_deployment): string {

		$fqcn             = esc_attr( $fqcn );

		$development_value = Deployment::DEVELOPMENT->value;
		$is_development    = selected( $current_deployment, Deployment::DEVELOPMENT->value, false );
		$development_label = esc_html( Deployment::DEVELOPMENT->get_label() );

		$staging_value     = Deployment::STAGING->value;
		$is_staging        = selected( $current_deployment, Deployment::STAGING->value, false );
		$staging_label     = esc_html( Deployment::STAGING->get_label() );

		$production_value  = Deployment::PRODUCTION->value;
		$is_production     = selected( $current_deployment, Deployment::PRODUCTION->value, false );
		$production_label  = esc_html( Deployment::PRODUCTION->get_label() );

		return <<<HTML
			<select name="feature_states[{$fqcn}]" style="max-width: 100%; font-size: 14px; height: 35px; padding: 0 30px 0 10px; border-color: #8c8f94;">
				<option value="{$development_value}" {$is_development}>{$development_label}</option>
				<option value="{$staging_value}" {$is_staging}>{$staging_label}</option>
				<option value="{$production_value}" {$is_production}>{$production_label}</option>
			</select>
		HTML;
	}

	/**
	 * Renders and returns the closing html for a single module.
	 *
	 * @return string The rendered html.
	 */
	private function get_render_module_close(): string {
		return <<<HTML
				</tbody>
			</table>
		HTML;
	}

	/**
	 * Validates the raw post payload data structure prior to execution commitment.
	 *
	 * @since  1.0.0
	 * @param  array $post_data The global $_POST collection block structure.
	 * @return bool             True if inputs pass verification checks, false otherwise.
	 */
	#[Override]
	public function validate( array $post_data ): bool {
		if ( ! isset( $post_data['feature_states'] ) || ! is_array( $post_data['feature_states'] ) ) {
			return false;
		}

		$valid_statuses = array( Deployment::DEVELOPMENT->value, Deployment::STAGING->value, Deployment::PRODUCTION->value );

		foreach ( $post_data['feature_states'] as $class_name => $status_value ) {
			if ( ! class_exists( $class_name ) || ! in_array( $status_value, $valid_statuses, true ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Commits validated software-release states directly into the immutable JSON configuration document.
	 *
	 * @since  1.0.0
	 * @param  array $post_data The validated global $_POST payload data structure.
	 * @return string|bool     An optional success message string upon success, or bool to indicate success or failure.
	 */
	#[Override]
	public function save( array $post_data ): string|bool {
		$submitted_states = $post_data['feature_states'] ?? array();
		$cleaned_states   = array();
		$valid_statuses   = array( Deployment::DEVELOPMENT->value, Deployment::STAGING->value, Deployment::PRODUCTION->value );

		foreach ( $submitted_states as $class_name => $status_value ) {
			if ( class_exists( $class_name ) && in_array( $status_value, $valid_statuses, true ) ) {
				$cleaned_states[ $class_name ] = $status_value;
			}
		}

		$written === $this->plugin->components_register->save( Register::DEPLOYMENT, $cleaned_states );

		if ( false === $written ) {
			$this->plugin->notices->add( 'error', __( 'Critical Error: Failed to write updates toward the release-states JSON file.', 'dwp-cf' ) );
			return false;
		}

		return __( 'Release status settings saved successfully. Remember to commit changes in Git! 🚀', 'dwp-cf' );
	}

	/**
	 * Gets the deployment ('in development', 'pre-production', 'production')
	 * of all modules and features from a json config file.
	 *
	 * @param bool $reset True when reset to factory defaults is requested.
	 * @return array
	 */
	private function get_deployments( bool $reset = false ): array {
		// If reset is requested, then skip the json file.
		if ( $reset ) {
			return array();
		}
		return $this->plugin->components_register->get_register( Register::DEPLOYMENT );
	}

	/**
	 * Delivers custom context-aware action buttons to the host administration factory footer container.
	 *
	 * @since  1.0.0
	 * @return array<string, string> Dictionary indexing raw HTML button element blocks strings.
	 */
	#[Override]
	public function get_action_buttons(): array {
		return array();
	}
}
