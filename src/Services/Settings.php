<?php
/**
 * Database Settings Storage Layer.
 *
 * @package DeWittePrins\CoreFunctionality\Services
 * @since   4.0.0
 */

namespace DeWittePrins\CoreFunctionality\Services;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class Settings
 *
 * Specialised storage repository interacting directly with the WordPress options pool.
 * Enforces a strict nested module->features structural layout [INDEX].
 *
 * @since 4.0.0
 */
class Settings {

	/**
	 * Local cache memory for reducing repetitive database queries.
	 *
	 * @var array|null
	 */
	private ?array $db_cache = null;

	/**
	 * Central WordPress database option key.
	 *
	 * @var string
	 */
	private string $option_key = 'dwp_cf_settings';

	/**
	 * Retrieves the entire, raw configuration matrix from the database.
	 * Optimized with runtime memory caching.
	 *
	 * @since  4.0.0
	 * @return array The compiled options matrix.
	 */
	public function get_all(): array {
		if ( null === $this->db_cache ) {
			$this->db_cache = get_option( $this->option_key, array() );
		}

		return is_array( $this->db_cache ) ? $this->db_cache : array();
	}

	/**
	 * Resolves whether a database override exists for a given key or module setup.
	 * Bridges the gap directly back to the central Config gateway [INDEX].
	 *
	 * @since  4.0.0
	 * @param  string      $config_key The abstract configuration identifier.
	 * @param  string|null $module_id  Optional parent module context.
	 * @return mixed Null if no override exists, otherwise the filtered database value.
	 */
	public function get_override( string $config_key, ?string $module_id = null ): mixed {
		// De module-lijst zelf ('modules') mag nooit uit de DB overschreven worden.
		if ( 'modules' === $config_key ) {
			return null;
		}

		$settings = $this->get_all();

		// Als we een module-context hebben, zoeken we binnen de sub-array van die module.
		if ( ! empty( $module_id ) ) {
			return $settings[ $module_id ][ $config_key ] ?? null;
		}

		// Plugin-brede globale instellingen (zonder module_id).
		return $settings[ $config_key ] ?? null;
	}

	/**
	 * Evaluates if a specific module is enabled within the database matrix [INDEX].
	 * Defaults to TRUE (active) if no explicit user configuration exists yet [INDEX].
	 *
	 * @since  4.0.0
	 * @param  string $module_id Target parent module identifier.
	 * @return bool True if active or unconfigured, false if explicitly disabled.
	 */
	public function is_module_enabled( string $module_id ): bool {
		$settings = $this->get_all();

		// Als er nog geen keuze in de DB staat, staat de module standaard AAN (Factory Default) [INDEX].
		if ( ! isset( $settings[ $module_id ]['enabled'] ) ) {
			return true;
		}

		return (bool) $settings[ $module_id ]['enabled'];
	}

	/**
	 * Evaluates if a child feature is allowed to run hooks [INDEX].
	 * Enforces parental inheritance: if the module is OFF, the feature is ALWAYS OFF [INDEX].
	 *
	 * @since  4.0.0
	 * @param  string $module_id  Target parent module identifier.
	 * @param  string $feature_id Target child feature identifier.
	 * @return bool True if cleared for execution.
	 */
	public function is_feature_enabled( string $module_id, string $feature_id ): bool {
		// HERSTELD: Hier gebruiken we de herstelde is_module_enabled methode!
		if ( ! $this->is_module_enabled( $module_id ) ) {
			return false;
		}

		$settings = $this->get_all();

		// Fallback: Als er geen specifieke keuze is opgeslagen voor het vinkje, volgt hij de module [INDEX].
		if ( ! isset( $settings[ $module_id ]['features'][ $feature_id ] ) ) {
			return true;
		}

		return (bool) $settings[ $module_id ]['features'][ $feature_id ];
	}

	/**
	 * Persists the entire sanitised options table back to the database.
	 *
	 * @since 4.0.0
	 * @param array $new_settings Structural multi-dimensional settings dataset.
	 */
	public function save( array $new_settings ): void {
		update_option( $this->option_key, $new_settings );
		$this->db_cache = $new_settings;
	}
}
