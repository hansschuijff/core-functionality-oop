<?php
/**
 * Messaging and Push Notice Dynamic Settings Section Component.
 *
 * @package    DeWittePrins\CoreFunctionality
 * @subpackage Admin\Services\Settings\Sections
 * @author     Hans Schuijff <@hansschuijff>
 * @license    GPL-2.0
 * @since      1.0.0
 */

declare( strict_types=1 );

namespace DeWittePrins\CoreFunctionality\Admin\Services\Settings\Sections;

use DeWittePrins\CoreFunctionality\Plugin;
use DeWittePrins\CoreFunctionality\Admin\Services\Settings\Interfaces\SettingsSectionInterface;
use DeWittePrins\CoreFunctionality\Notifiers\Interfaces\PushNoticeInterface;
use DeWittePrins\CoreFunctionality\Services\ClassParser;
use WP_Error;
use Override;

// Import global PHP and WordPress functions.
use function __;
use function class_exists;
use function esc_attr;
use function esc_html;
use function esc_html_e;
use function is_array;
use function is_string;
use function sanitize_text_field;
use function selected;
use function strtolower;
use function wp_unslash;
use function wp_nonce_field;
use function wp_verify_nonce;
use function current_user_can;
use function array_map;
use function sprintf;
use function defined;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class PushNoticeSettings
 *
 * Implements a dynamic, decoupled settings tab page managing available messaging drivers.
 *
 * @since 1.0.0
 */
class PushNoticeSettings implements SettingsSectionInterface {

	/**
	 * Configuration settings keys and prefixes.
	 *
	 * @var string
	 */
	private string $available_drivers_settings_key = 'push-notice-available-drivers';
	private string $active_driver_settings_key     = 'push-notice-driver';
	private string $active_driver_settings_prefix  = 'push-notice-driver-settings-';

	/**
	 * Constructor.
	 *
	 * Fully satisfies the signature demanded by SettingsSectionInterface.
	 *
	 * @since 1.0.0
	 * @param Plugin $plugin The central plugin class instance acting as a provider.
	 */
	public function __construct(
		private readonly Plugin $plugin,
	) {}

	/**
	 * URL slug identifier for this tab.
	 *
	 * @since  1.0.0
	 * @return string
	 */
	#[Override]
	public static function get_id(): string {
		return 'cf-push-notice-settings';
	}

	/**
	 * Returns an icon for the admin settings menu tabs layout presentation.
	 *
	 * @since  1.0.0
	 * @return string
	 */
	#[Override]
	public static function get_icon(): string {
		return 'dashicons-buddicons-replies';
	}

	/**
	 * Navigation text title for the settings tab section menu header.
	 *
	 * @since  1.0.0
	 * @return string
	 */
	#[Override]
	public static function get_name(): string {
		return __( 'Push Notice Services', 'dwp-cf' );
	}

	/**
	 * Short narrative description summary explaining user dashboard parameters.
	 *
	 * @since  1.0.0
	 * @return string The description of this Settings Section.
	 */
	#[Override]
	public static function get_description(): string {
		return __( 'Select a service you want to send the automatic push notices to, that are sent to inform you of problems with the website, and set the authentication data to connect the driver to the service.', 'dwp-cf' );
	}

	/**
	 * Stylesheets required for this tab execution rendering canvas.
	 *
	 * @since  1.0.0
	 * @return array<string, string>
	 */
	#[Override]
	public static function get_stylesheets(): array {
		return array();
	}

	/**
	 * JavaScript asset files dependencies maps registrations hooks.
	 *
	 * @since  1.0.0
	 * @return array<string, string>
	 */
	#[Override]
	public static function get_scripts(): array {
		return array();
	}

	/**
	 * Outputs the wrapping HTML form layout shell with instant zero-refresh toggling.
	 *
	 * @since  1.0.0
	 * @param  bool $defaults_only Optional. Force bypass of database overrides. Default false.
	 * @return void
	 */
	#[Override]
	public function render( bool $defaults_only = false ): void {
		// Strictly matching the 4th parameter constraint criteria for explicit default arrays initialization
		$driver_classes = $this->plugin->settings->get( $this->available_drivers_settings_key, '', false, array() );
		$active_driver  = $this->plugin->settings->get( $this->active_driver_settings_key, '', false, '' );

		if ( is_string( $active_driver ) ) {
			$active_driver = ClassParser::to_single_backslashes( $active_driver );
		} else {
			$active_driver = '';
		}

		echo '<div class="dwp-settings-section-wrapper" style="max-width: 800px; background: #fff; border: 1px solid #ccd0d4; padding: 20px; margin-top: 20px;">';
		echo '<table class="form-table">';

		// Render Main Active Driver Selector Dropdown Row Component Modules
		$this->render_driver_selector_row( $driver_classes, $active_driver );

		// Render Dynamic Schema Configuration Fields Blocks for each verified registered driver
		foreach ( $driver_classes as $fqcn ) {
			if ( is_string( $fqcn ) && ClassParser::is_implementation_of( $fqcn, PushNoticeInterface::class ) ) {
				$this->render_driver_fields_block( $fqcn, $active_driver );
			}
		}

		echo '</table>';
		echo '</div>';
	}

	/**
	 * Pre-flight validation gate checking inputs before database persistence routines.
	 *
	 * @since  1.0.0
	 * @param  array $posted_data The form data that was submitted context maps.
	 * @return bool|WP_Error      True if valid, false or WP_Error on structural field failures.
	 */
	#[Override]
	public function validate( array $posted_data ): bool|WP_Error {
		if ( isset( $posted_data['dwp_active_driver'] ) && ! empty( $posted_data['dwp_active_driver'] ) ) {
			$driver = ClassParser::to_single_backslashes( sanitize_text_field( $posted_data['dwp_active_driver'] ) );
			if ( ! class_exists( $driver ) ) {
				return new WP_Error( 'invalid_driver', __( 'The selected push notice service driver class does not exist.', 'dwp-cf' ) );
			}
		}
		return true;
	}

	/**
	 * Save this sections settings in a single unified execution block sequence.
	 * Acts as the absolute autonomous security gate filtering execution permissions inputs.
	 *
	 * @since  1.0.0
	 * @param  array $posted_data Raw $_POST context datasets maps records.
	 * @return string|bool        An optional success message string upon success, or false on failure.
	 */
	#[Override]
	public function save( array $posted_data ): string|bool {
		// Absolute Security Guard Permissions Checks Lookups Gates
		if ( ! current_user_can( 'manage_options' ) ) {
			$this->plugin->notices->add( 'error', __( 'Critical Error: You do not have sufficient permissions to modify push notice settings.', 'dwp-cf' ) );
			return false;
		}

		if ( ! isset( $posted_data['dwp_active_driver'] ) || ! is_string( $posted_data['dwp_active_driver'] ) ) {
			$this->plugin->notices->add( 'error', __( 'Save Failed: No valid active messaging driver payload was submitted.', 'dwp-cf' ) );
			return false;
		}

		$submitted_driver = ClassParser::to_single_backslashes( sanitize_text_field( $posted_data['dwp_active_driver'] ) );

		// 1. Persist the core active platform driver choice selection row parameters allocation
		$this->plugin->settings->save( $this->active_driver_settings_key, $submitted_driver );

		// 2. PROCESS ALL DRIVER FIELDS IN THE SAME REQUEST TRANSACTION (Fixes the legacy multi-step loop early return bug)
		if ( ! empty( $submitted_driver ) && class_exists( $submitted_driver ) ) {
			if ( ClassParser::is_implementation_of( $submitted_driver, PushNoticeInterface::class ) ) {
				$slug = strtolower( ClassParser::fqcn_remove_namespace( $submitted_driver ) );
				$slug = ClassParser::camel_to_kebab( $slug );

				if ( isset( $posted_data['dwp_driver_bundle'][ $slug ] ) && is_array( $posted_data['dwp_driver_bundle'][ $slug ] ) ) {
					$sanitized_data = array_map( 'sanitize_text_field', wp_unslash( $posted_data['dwp_driver_bundle'][ $slug ] ) );

					// Persist settings arrays records using standard settings pipeline framework tokens routing paths
					$this->plugin->settings->save( $this->active_driver_settings_prefix . $slug, $sanitized_data );
				}
			}
		}

		return __( 'Push notice configuration successfully updated.', 'dwp-cf' );
	}

	/*-------------------------------------------------------------------------
	 * PRIVATE RENDER BLOCK METHODS (CLEAN CODE ISOLATION)
	 *------------------------------------------------------------------------*/

	/**
	 * Renders the main form table header dropdown containing driver choice options.
	 *
	 * @since  1.0.0
	 * @param  array  $driver_classes Available registered FQCN strings array.
	 * @param  string $active_driver  The currently active driver class string identifier.
	 * @return void
	 */
	private function render_driver_selector_row( array $driver_classes, string $active_driver ): void {
		$label_driver = esc_html__( 'Active Messaging Service', 'dwp-cf' );
		$opt_none     = esc_html__( '-- None (Deactivated) --', 'dwp-cf' );
		$desc_driver  = esc_html__( 'Select the platform where automated push notice notifications should be transmitted to.', 'dwp-cf' );

		echo <<<HTML
		<thead>
			<tr>
				<th><label for="push-notice-driver">{$label_driver}</label></th>
				<td>
					<select name="dwp_active_driver" id="push-notice-driver" onchange="
						var selectedDriver = this.value;
						var sections = document.querySelectorAll('.js-dwp-driver-fields');
						sections.forEach(function(section) {
							section.style.display = (section.getAttribute('data-driver-fqcn') === selectedDriver) ? 'table-row-group' : 'none';
						});
					" style="min-width: 250px; vertical-align: middle;">
						<option value="">{$opt_none}</option>
		HTML;

		foreach ( $driver_classes as $fqcn ) {
			if ( is_string( $fqcn ) && ClassParser::is_implementation_of( $fqcn, PushNoticeInterface::class ) ) {
				$fqcn_attr     = esc_attr( $fqcn );
				$friendly_name = esc_html( $fqcn::get_name() );
				$is_selected   = selected( $active_driver, $fqcn, false );
				echo "<option value='{$fqcn_attr}' {$is_selected}>{$friendly_name}</option>";
			}
		}

		echo <<<HTML
					</select>
					<p class="description" style="margin-top: 8px;">{$desc_driver}</p>
				</td>
			</tr>
		</thead>
		HTML;
	}

	/**
	 * Renders a full dynamic field block layout module for a specific driver schema criteria mappings.
	 *
	 * Uses official framework routing paths passing default array values to satisfy data casting constraints rules.
	 *
	 * @since  1.0.0
	 * @param  string $fqcn          The fully qualified class name of the target service driver component.
	 * @param  string $active_driver The currently selected active driver class name string indicators pointer.
	 * @return void
	 */
	private function render_driver_fields_block( string $fqcn, string $active_driver ): void {
		$schema        = $fqcn::get_configuration_schema();
		$slug          = strtolower( ClassParser::fqcn_remove_namespace( $fqcn ) );
		$slug          = ClassParser::camel_to_kebab( $slug );
		$driver_name   = esc_html( $fqcn::get_name() );
		$config_txt    = esc_html__( 'Configuration', 'dwp-cf' );
		$fqcn_clean    = esc_attr( ClassParser::to_single_backslashes( $fqcn ) );

		// Secure lookup mapping passing an empty initialization array to satisfy the 4th parameters signature constraints
		$option_key    = $this->active_driver_settings_prefix . $slug;
		$options       = $this->plugin->settings->get( $option_key, '', false, array() );
		if ( ! is_array( $options ) ) {
			$options = array();
		}

		$is_visible    = ( $active_driver === ClassParser::to_single_backslashes( $fqcn ) );
		$display_style = $is_visible ? 'table-row-group' : 'none';

		echo <<<HTML
		<tbody class="js-dwp-driver-fields" data-driver-fqcn="{$fqcn_clean}" style="display: {$display_style};">
			<tr class="dwp-driver-separator-row" style="background: #f6f7f7;">
				<td colspan="2" style="padding: 4px 10px; font-weight: 600; color: #1d2327; font-size: 12px;">{$driver_name} {$config_txt}</td>
			</tr>
		HTML;

		foreach ( $schema as $field_key => $field_meta ) {
			$current_value = $options[ $field_key ] ?? '';
			$input_name    = esc_attr( sprintf( 'dwp_driver_bundle[%s][%s]', $slug, $field_key ) );
			$label_txt     = esc_html( $field_meta['label'] );

			echo <<<HTML
			<tr>
				<th><label>{$label_txt}</label></th>
				<td>
			HTML;

			if ( 'text' === $field_meta['type'] ) {
				$val_attr = esc_attr( (string) $current_value );
				echo "<input type='text' name='{$input_name}' value='{$val_attr}' class='regular-text' />";
			}

			if ( ! empty( $field_meta['desc'] ) ) {
				$desc_txt = esc_html( $field_meta['desc'] );
				echo "<p class='description'>{$desc_txt}</p>";
			}

			echo '</td></tr>';
		}

		echo '</tbody>';
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
