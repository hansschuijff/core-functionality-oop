<?php
/**
 * Administrative Settings Page Host Controller (OOP Settings Section Factory).
 *
 * @package DeWittePrins\CoreFunctionality\Services
 * @since   4.0.0
 */

namespace DeWittePrins\CoreFunctionality\Services;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use DeWittePrins\CoreFunctionality\Plugin;
use DeWittePrins\CoreFunctionality\Contracts\SettingsSectionInterface;

/**
 * Class AdminSettingsPage
 *
 * Orchestrates pluggable SettingsSectionInterface instances, manages unified security gates,
 * and centralises administrative asset routing through an intelligent delivery pipeline.
 *
 * @since 4.0.0
 */
class AdminSettingsPage {

	/**
	 * Central plugin controller reference.
	 *
	 * @var Plugin
	 */
	private Plugin $plugin;

	/**
	 * Local registry repository storing the operational settings section objects.
	 *
	 * @var array<string, SettingsSectionInterface>
	 */
	private array $sections = array();

	/**
	 * Central asset delivery toggle.
	 * 'inline'  = Reads code from files and injects directly into admin header/footer wrappers.
	 * 'enqueue' = Formally registers scripts via core WordPress enqueue handlers.
	 *
	 * @var string
	 */
	private string $delivery_method = 'inline';

	/**
	 * AdminSettingsPage Constructor.
	 *
	 * Automatically attaches core hooks and anchors default administrative layout contexts.
	 *
	 * @since 4.0.0
	 * @param Plugin   $plugin   The central orchestrator instance.
	 * @param Settings $settings The isolated database storage service layer.
	 */
	public function __construct( Plugin $plugin, Settings $settings ) {
		$this->plugin   = $plugin;
		$this->settings = $settings;

		add_action( 'admin_menu', array( $this, 'register_admin_menu_page' ) );
		add_action( 'admin_init', array( $this, 'save_settings' ) );

		// De centrale bron regelt de logistiek en timing van alle sectie-assets [INDEX].
		add_action( 'admin_enqueue_scripts', array( $this, 'process_factory_assets' ) );

		/**
		 * Fires when the administration page initialises, enabling sections to register themselves [INDEX].
		 *
		 * @since 4.0.0
		 * @param AdminSettingsPage $this The central section factory host instance.
		 */
		do_action( 'dwp_cf_register_settings_sections', $this );
	}

	/**
	 * Public API method allowing internal modules to feed configuration sections into the factory [INDEX].
	 *
	 * @since  4.0.0
	 * @param  SettingsSectionInterface $section The instantiated settings section object.
	 * @return void
	 */
	public function register_section( SettingsSectionInterface $section ): void {
		$this->sections[ $section::get_id() ] = $section;
	}

	/**
	 * Anchors the settings page into the native WordPress administrative sidebar menu.
	 *
	 * @since  4.0.0
	 * @return void
	 */
	public function register_admin_menu_page(): void {
		add_options_page(
			__( 'Core Functionality Settings', 'dwp-cf' ),
			__( 'Core Functionality', 'dwp-cf' ),
			'manage_options',
			'dwp-cf-settings',
			array( $this, 'render_settings_page' )
		);
	}

	/**
	 * Renders the main wrapping options shell, notices, and section navigation [INDEX].
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public function render_settings_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		if ( empty( $this->sections ) ) {
			return;
		}

		// Bepaal de actieve sectie via de URL (met fallback naar de allereerste sectie) [INDEX].
		$default_section = array_key_first( $this->sections );
		$current_section = isset( $_GET['section'] ) ? sanitize_key( $_GET['section'] ) : $default_section;

		if ( ! array_key_exists( $current_section, $this->sections ) ) {
			$current_section = $default_section;
		}

		$active_section = $this->sections[ $current_section ];
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

			<!-- Rendert de schone navigatiebalk direct vanuit de objecten matrix [INDEX] -->
			<nav class="nav-tab-wrapper wp-clearfix" style="margin-bottom: 20px;">
				<?php foreach ( $this->sections as $id => $section_obj ) : ?>
					<?php
					$section_url  = add_query_arg( array( 'section' => $id ), menu_page_url( 'dwp-cf-settings', false ) );
					$active_class = ( $current_section === $id ) ? 'nav-tab-active' : '';
					?>
					<a href="<?php echo esc_url( $section_url ); ?>" class="nav-tab <?php echo esc_attr( $active_class ); ?>">
						<?php echo esc_html( $section_obj::get_name() ); ?>
					</a>
				<?php endforeach; ?>
			</nav>

			<?php $this->plugin->notice->render_queued_notices(); ?>

			<form method="post" action="">
				<?php wp_nonce_field( 'dwp_cf_save_settings', 'dwp_cf_nonce' ); ?>
				<input type="hidden" name="action" value="dwp_cf_save_options" />
				<input type="hidden" name="current_section" value="<?php echo esc_attr( $current_section ); ?>" />

				<div class="dwp-section-content-container" style="margin-top: 15px;">
					<?php
					// Volledig gedelegeerd object-renderen!
					$active_section->render();
					?>
				</div>

				<?php submit_button( __( 'Instellingen opslaan', 'dwp-cf' ) ); ?>
			</form>
		</div>
		<?php
	}

	/**
	 * Orchestrates the secure form processing lifecycle and directs the pipeline states.
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public function save_settings(): void {
		if ( ! isset( $_POST['dwp_cf_nonce'] ) || ! isset( $_POST['action'] ) || 'dwp_cf_save_options' !== $_POST['action'] ) {
			return;
		}

		if ( ! wp_verify_nonce( sanitize_key( $_POST['dwp_cf_nonce'] ), 'dwp_cf_save_settings' ) ) {
			wp_die( esc_html__( 'Beveiligingscontrole mislukt. Probeer het opnieuw.', 'dwp-cf' ) );
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'U heeft onvoldoende rechten om deze instellingen te wijzigen.', 'dwp-cf' ) );
		}

		$target_section = isset( $_POST['current_section'] ) ? sanitize_key( $_POST['current_section'] ) : array_key_first( $this->sections );
		if ( ! isset( $this->sections[ $target_section ] ) ) {
			return;
		}

		$section_object = $this->sections[ $target_section ];

		// PIPELINE VALIDATIE EN DIRECTE ROUTING.
		$validation_result = $section_object->validate( $_POST );

		if ( is_wp_error( $validation_result ) ) {
			if ( array( 'warning' ) === $validation_result->get_error_codes() ) {
				$section_object->save( $_POST );
				foreach ( $validation_result->get_error_messages() as $msg ) {
					$this->plugin->notice->add( 'warning', $msg );
				}
				wp_safe_redirect( add_query_arg( array( 'settings-updated' => 'true' ), wp_get_referer() ) );
				exit;
			}

			foreach ( $validation_result->get_error_messages() as $msg ) {
				$this->plugin->notice->add( 'error', $msg );
			}
			wp_safe_redirect( wp_get_referer() );
			exit;
		}

		if ( true === $validation_result ) {
			$section_object->save( $_POST );
		}

		wp_safe_redirect( add_query_arg( array( 'settings-updated' => 'true' ), wp_get_referer() ) );
		exit;
	}

	/**
	 * Orchestrates asset distribution for the active section based on the delivery switch [INDEX].
	 *
	 * @since  4.0.0
	 * @param  string $hook_suffix The active admin screen context string.
	 * @return void
	 */
	public function process_factory_assets( string $hook_suffix ): void {
		if ( 'settings_page_dwp-cf-settings' !== $hook_suffix || empty( $this->sections ) ) {
			return;
		}

		$current_section = isset( $_GET['section'] ) ? sanitize_key( $_GET['section'] ) : array_key_first( $this->sections );
		$active_section  = $this->sections[ $current_section ] ?? null;

		if ( ! $active_section ) {
			return;
		}

		if ( 'enqueue' === $this->delivery_method ) {
			$this->enqueue_stylesheets( $active_section->get_stylesheets() );
			$this->enqueue_scripts( $active_section->get_scripts() );
		} else {
			add_action(
				'admin_head',
				function () use ( $active_section ) {
					$this->inject_inline_stylesheets( $active_section->get_stylesheets() );
				}
			);

			add_action(
				'admin_footer',
				function () use ( $active_section ) {
					$this->inject_inline_scripts( $active_section->get_scripts() );
				}
			);
		}
	}

	/**
	 * Registers and enqueues stylesheets formally.
	 *
	 * @since 1.0.0
	 * @param array $stylesheets Array containing full paths of stylesheet file(s).
	 */
	private function enqueue_stylesheets( array $stylesheets ): void {
		foreach ( $stylesheets as $handle => $path ) {
			if ( file_exists( $path ) ) {
				$url = str_replace( $this->plugin->get_data( 'plugin-dir' ), $this->plugin->get_data( 'plugin-url' ), $path );
				wp_enqueue_style( $handle, $url, array(), $this->plugin->get_data( 'version' ) );
			}
		}
	}

	/**
	 * Registers and enqueues scripts formally.
	 *
	 * @since 1.0.0
	 * @param array $scripts Array of absolute paths to javascript files.
	 */
	private function enqueue_scripts( array $scripts ): void {
		foreach ( $scripts as $handle => $path ) {
			if ( file_exists( $path ) ) {
				$url = str_replace( $this->plugin->get_data( 'plugin-dir' ), $this->plugin->get_data( 'plugin-url' ), $path );
				wp_enqueue_script( $handle, $url, array( 'jquery' ), $this->plugin->get_data( 'version' ), true );
			}
		}
	}

	/**
	 * Reads styles from disk and dumps them inline.
	 *
	 * @since 1.0.0
	 * @param array $stylesheets Array containing full paths of (a) stylesheet file(s).
	 */
	private function inject_inline_stylesheets( array $stylesheets ): void {
		$buffer = '';
		foreach ( $stylesheets as $path ) {
			if ( file_exists( $path ) ) {
				$buffer .= file_get_contents( $path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
			}
		}
		if ( ! empty( $buffer ) ) {
			echo '<style id="dwp-cf-factory-inline-css">' . $buffer . '</style>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
	}

	/**
	 * Reads scripting from disk and dumps them inline.
	 *
	 * @since  4.0.0
	 * @param  array $scripts Array of absolute paths to javascript files.
	 * @return void
	 */
	private function inject_inline_scripts( array $scripts ): void {
		$buffer = '';
		foreach ( $scripts as $path ) {
			if ( file_exists( $path ) ) {
				$buffer .= file_get_contents( $path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
			}
		}
		if ( ! empty( $buffer ) ) {
			echo '<script id="dwp-cf-factory-inline-js">' . $buffer . '</script>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
	}
}
