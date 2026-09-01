<?php
namespace DeWittePrins\Modules\RemoteTickets;

use DeWittePrins\Core\Contracts\ModuleInterface;
use DeWittePrins\Core\Plugin;

class Module implements ModuleInterface {
	private Plugin $plugin;
	private array $module_data = array();

	// De module ontvangt de globale plugin-data via Dependency Injection!
	public function __construct( Plugin $plugin ) {
		$this->plugin = $plugin;

		// Bepaal de paden specifiek voor DEZE module-submap.
		$module_dir = plugin_dir_path( __FILE__ );
		$module_url = plugin_dir_url( __FILE__ );

		$this->module_data = array(
			'base-dir'     => $module_dir,
			'views-dir'    => $module_dir . 'views/',
			'language-dir' => $module_dir . 'languages/',
			'base-url'     => $module_url,
			'views-url'    => $module_url . 'views/',
			'assets-url'   => $module_url . 'assets/',
		);
	}

	/**
	 * Lokale module-paden opvragen.
	 */
	public function get_module_data( string $key ): string {
		return $this->module_data[ $key ] ?? '';
	}

	/**
	 * Globale plugin-data (zoals versie of plugin-url) opvragen via de hoofdhuls.
	 */
	public function get_global_plugin_data( string $key ): string {
		return $this->plugin->get_data( $key );
	}

	public function get_config( string $filename ): array {
		$file = $this->module_data['base-dir'] . 'config/' . $filename . '.php';
		return is_readable( $file ) ? include $file : array();
	}

	public function get_preflight_checks(): array {
		return array(
			'and' => array(
				'woocommerce',                   // Roept automatisch src/Platform/Woocommerce.php aan!
				'the_events_calendar',           // Roept src/Platform/TheEventsCalendar.php aan!
				'event_tickets_plus',            // Roept src/Platform/EventTicketsPlus.php aan!
				'mollie_payments_for_woocommerce', // De loader vertaalt dit naar \DeWittePrins\Environment\Themes\MolliePaymentsForWoocommerce.
				'any_genesis_theme',             // De loader vertaalt dit naar \DeWittePrins\Environment\Themes\AnyGenesis.
			),
		);
	}
	public function get_features(): array { return array(); }
	public function launch(): void {}
}
