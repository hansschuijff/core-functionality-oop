<?php
/**
 * Options Vault Service.
 *
 * @package DeWittePrins\CoreFunctionality\Services
 * @author  Hans Schuijff <@hansschuijff>
 * @license GPL-2.0
 * @since   1.0.0
 */

declare( strict_types=1 );

namespace DeWittePrins\CoreFunctionality\Services;

// Import global WordPress core database abstraction functions.
use function get_option;
use function update_option;
use function delete_option;
use function in_array;
use function is_array;
use function is_string;
use function array_count_values;
use function array_keys;
use function defined;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Options
 *
 * Acts as an options vault, a secure data-mapping shield regulating physical database option allocations.
 * Dynamically routes abstract tokens to standalone keys or shared grouped options via a flat mapping matrix.
 *
 * @since 1.0.0
 */
class Options {

	/**
	 * Flat token-to-key translation map registers repository.
	 *
	 * @var array<string, string> Format: [ 'token' => 'key' ]
	 */
	private array $keys = array();

	/**
	 * The option key used to store custom token-to-key routing configurations.
	 *
	 * @var string
	 */
	private string $token_translations_option_key = 'wp_cf_options_token_translations';

	/**
	 * The option key used to store the unregistered fallback quarantine data pool.
	 *
	 * @var string
	 */
	private string $quarantine_pool_option_key = 'dwp_cf_options_quarantine_pool';

	/**
	 * Options Constructor.
	 *
	 * @since 1.0.0
	 * @param Settings $settings The central framework settings engine instance.
	 */
	public function __construct( private readonly Settings $settings ) {}

	/**
	 * Pulls the raw active flat routing configurations matrix safely out of the database layer.
	 *
	 * @since  1.0.0
	 * @return array<string, string> Flat array schema: [ 'token' => 'key' ]
	 */
	public function get_keys(): array {
		if ( empty( $this->keys ) ) {
			$keys = $this->get_unmanaged( $this->token_translations_option_key, array() );
			if ( is_array( $keys ) ) {
				$this->keys = $keys;
			}
		}
		return $this->keys;
	}

	/**
	 * Extracts and filters all direct standalone rows options parameters maps from the flat matrix.
	 *
	 * @since  1.0.0
	 * @return array<string, string> Filtered single entries format: [ 'token' => 'key' ]
	 */
	public function get_standalone_keys(): array {
		$keys       = $this->get_keys();
		$key_counts = array_count_values( $keys );
		$standalone = array();

		foreach ( $keys as $token => $key ) {
			if ( ! empty( $key ) && is_string( $key ) && 1 === $key_counts[ $key ] ) {
				$standalone[ $token ] = $key;
			}
		}
		return $standalone;
	}

	/**
	 * Extracts, groups, and clusters all shared collection option keys automatically.
	 *
	 * @since  1.0.0
	 * @return array<string, array<string>> Grouped features parameters schemas: [ 'key' => [ 'token1', 'token2' ] ]
	 */
	public function get_grouped_keys(): array {
		$keys       = $this->get_keys();
		$key_counts = array_count_values( $keys );
		$grouped    = array();

		foreach ( $keys as $token => $key ) {
			if ( ! empty( $key ) && is_string( $key ) && $key_counts[ $key ] > 1 ) {
				$grouped[ $key ][] = $token;
			}
		}
		return $grouped;
	}

	/**
	 * Deliver the option key routing target belonging to a token, out of cache if possible.
	 *
	 * @since  1.0.0
	 * @param  string $token The abstract security intent token.
	 * @return string        The physical database option key name string.
	 */
	public function get_key( string $token ): string {
		$system_protected_tokens = array(
			$this->token_translations_option_key,
			$this->quarantine_pool_option_key,
		);

		if ( in_array( $token, $system_protected_tokens, true ) ) {
			return $token;
		}

		$keys = $this->get_keys();
		return $keys[ $token ] ?? '';
	}

	/**
	 * Forcefully reloads the flat translation matrix from the settings provider layer.
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public function refresh_keys(): void {
		$this->keys = array();
		$keys       = $this->settings->get( $this->token_translations_option_key, '', false, array() );

		if ( is_array( $keys ) ) {
			$this->keys = $keys;
		}
	}

	/**
	 * ROUTE 1: Token-based retrieval (The Validation Layer)
	 *
	 * Retrieves data safely using intent tokens, dynamically dissolving multi-tenant sharing rules.
	 *
	 * @since  1.0.0
	 * @param  string $token   The abstract intent token.
	 * @param  mixed  $default Optional fallback value if the data payload evaluates as absent.
	 * @return mixed           The raw storage payload extracted from the database or default fallback.
	 */
	public function get( string $token, mixed $default = false ): mixed {
		$key = $this->get_key( $token );
		if ( empty( $key ) ) {
			return $this->get_quarantined( $token, $default );
		}

		$grouped_keys = $this->get_grouped_keys();

		// If the targeted key is shared by multiple tokens, dissolve via array structures
		if ( isset( $grouped_keys[ $key ] ) ) {
			$master_array = get_option( $key, array() );
			if ( is_array( $master_array ) && isset( $master_array[ $token ] ) ) {
				return $master_array[ $token ];
			}
			return $default;
		}

		// Otherwise, retrieve directly as a clean standalone database row option
		return $this->get_unmanaged( $key, $default );
	}

	/**
	 * ROUTE 2: Central Database Route (The Direct Executor)
	 *
	 * Bypasses the token translation layer entirely. Called directly from
	 * external modules for unmanaged keys, or internally via get().
	 *
	 * @since  1.0.0
	 * @param  string $key     The exact WordPress database option key (e.g., 'wp_user_roles').
	 * @param  mixed  $default Fallback value if the option does not exist in the database.
	 * @return mixed           The retrieved value from the WordPress database, or the default value.
	 */
	public function get_unmanaged( string $key, mixed $default = false ): mixed {
		return get_option( $key, $default );
	}

	/**
	 * Saves data safely, dynamically distributing parameters to standalone rows or shared grouped collections.
	 *
	 * @since  1.0.0
	 * @param  string $token The abstract intent token destination.
	 * @param  mixed  $value The raw payload data structure to commit to memory.
	 * @return bool          True on database adjustment success, false otherwise.
	 */
	public function update( string $token, mixed $value ): bool {
		$key = $this->get_key( $token );
		if ( empty( $key ) ) {
			return $this->update_quarantined( $token, $value );
		}

		$grouped_keys = $this->get_grouped_keys();

		// Strategy A: If the physical key is shared, save inside a multi-tenant array block
		if ( isset( $grouped_keys[ $key ] ) ) {
			$master_array = get_option( $key, array() );
			if ( ! is_array( $master_array ) ) {
				$master_array = array();
			}

			$master_array[ $token ] = $value;

			return update_option( $key, $master_array, true ); // Forced autoload for maximum sub-millisecond RAM speeds
		}

		// Strategy B: Save as a standard standalone database option row
		return update_option( $key, $value );
	}

	/**
	 * Permanently deletes a token and its data from the database, regardless of where it resides.
	 *
	 * @since  1.0.0
	 * @param  string $token The unique feature token to delete.
	 * @return bool          True on successful deletion, false otherwise.
	 */
	public function delete( string $token ): bool {
		$key = $this->get_key( $token );

		if ( ! empty( $key ) ) {
			$grouped_keys = $this->get_grouped_keys();

			if ( isset( $grouped_keys[ $key ] ) ) {
				$this->remove_from_grouped_options( $key, $token );
			} else {
				$this->remove_standalone_options( $key );
			}

			// Clean up the flat routing linkage register
			$translations = get_option( $this->token_translations_option_key, array() );
			if ( is_array( $translations ) && isset( $translations[ $token ] ) ) {
				unset( $translations[ $token ] );
				update_option( $this->token_translations_option_key, $translations );
			}
		}

		// Always scrub from quarantine pool as fallback flavor
		$this->remove_from_quarantine( $token );

		// Refresh internal cache registers to keep runtime in sync
		$this->refresh_keys();

		return true;
	}

	/**
	 * Deletes a standalone row option directly from WordPress.
	 *
	 * @since 1.0.0
	 * @param string $key The exact option key to delete.
	 * @return void
	 */
	private function remove_standalone_options( string $key ): void {
		delete_option( $key );
	}

	/**
	 * Removes an individual sub-key entry from a shared grouped option array.
	 *
	 * @since 1.0.0
	 * @param string $master_option De groepsoptie key in de database.
	 * @param string $sub_key       De token key binnen de array.
	 * @return void
	 */
	private function remove_from_grouped_options( string $master_option, string $sub_key ): void {
		$master_array = get_option( $master_option, array() );
		if ( is_array( $master_array ) && isset( $master_array[ $sub_key ] ) ) {
			unset( $master_array[ $sub_key ] );

			if ( empty( $master_array ) ) {
				delete_option( $master_option );
			} else {
				update_option( $master_option, $master_array, true );
			}
		}
	}

	/**
	 * Removes an unmapped token from the pre-programmed quarantine pool option.
	 *
	 * @since 1.0.0
	 * @param string $token De feature token die uit quarantaine moet.
	 * @return void
	 */
	private function remove_from_quarantine( string $token ): void {
		$quarantine_pool = $this->get_quarantine_pool();
		if ( isset( $quarantine_pool[ $token ] ) ) {
			unset( $quarantine_pool[ $token ] );
			$this->save_quarantine_pool( $quarantine_pool );
		}
	}

	/**
	 * De-registers an active token and safely moves its data back to the quarantine pool.
	 *
	 * Handles full backend-decoupling, physical record removal, and internal registry synchronization.
	 *
	 * @since  1.0.0
	 * @param  string $token The active intent token to move back to quarantine.
	 * @return bool          True on successful quarantine movement, false otherwise.
	 */
	public function quarantine_token( string $token ): bool {
		$key = $this->get_key( $token );
		if ( empty( $key ) ) {
			return false;
		}

		// 1. Extract the active live payload safely before mutating any structures
		$live_value = $this->get( $token );

		// 2. Hydrate and update the isolated quarantine pool array in memory
		$quarantine_pool = $this->get_quarantine_pool();
		$quarantine_pool[ $token ] = $live_value;
		$this->save_quarantine_pool( $quarantine_pool );

		// 3. Clean up the physical database storage layer based on flat mapping counts
		$grouped_keys = $this->get_grouped_keys();
		if ( isset( $grouped_keys[ $key ] ) ) {
			$this->remove_from_grouped_options( $key, $token );
		} else {
			$this->remove_standalone_options( $key );
		}

		// 4. Sever the linkage inside the flat routing translations register option
		$translations = get_option( $this->token_translations_option_key, array() );
		if ( is_array( $translations ) && isset( $translations[ $token ] ) ) {
			unset( $translations[ $token ] );
			update_option( $this->token_translations_option_key, $translations );
		}

		// 5. Hard reload internal cache registers to reflect current matrix status during runtime
		$this->refresh_keys();

		return true;
	}

	/**
	 * Assigns a feature token to a target database key, absorbing all transport complexitiy.
	 *
	 * Seamlessly migrates data from quarantine, and automatically normalizes existing standalone
	 * options into grouped multi-tenant arrays when keys merge, preventing data loss.
	 *
	 * @since  1.0.0
	 * @param  string $token      The abstract feature settings token to transport.
	 * @param  string $target_key The targeted physical database option key destination string.
	 * @return bool               True on successful assignment and data migration, false otherwise.
	 */
	public function assign_token_to_key( string $token, string $target_key ): bool {
		if ( empty( $token ) || empty( $target_key ) ) {
			return false;
		}

		// 1. Preserve and extract the live value (out of quarantine pool or current database location)
		$live_value = $this->get( $token );

		// 2. Check for Standalone-to-Group convergence overlap before updating the matrix.
		// If the target key currently exists as a single standalone option for ANOTHER token,
		// we must convert that sibling token's data into a grouped array format first.
		$standalone_keys = $this->get_standalone_keys();
		$sibling_token   = array_search( $target_key, $standalone_options ?? $standalone_keys, true );

		if ( is_string( $sibling_token ) && $sibling_token !== $token ) {
			$sibling_value = $this->get( $sibling_token );

			// Temporary manually build a minimal shared array register for the sibling
			$sibling_array = array( $sibling_token => $sibling_value );
			update_option( $target_key, $sibling_array, true );
		}

		// 3. Update the flat routing matrix configurations registry option
		$translations = get_option( $this->token_translations_option_key, array() );
		if ( ! is_array( $translations ) ) {
			$translations = array();
		}
		$translations[ $token ] = $target_key;
		update_option( $this->token_translations_option_key, $translations );

		// 4. Force synchronization of internal registers before executing data migration write
		$this->refresh_keys();

		// 5. Purge from quarantine array structure if it resided there
		$this->remove_from_quarantine( $token );

		// 6. Write the preserved live value safely to its new Active destination path
		return $this->update( $token, $live_value );
	}

	/**
	 * Checks whether a specific tracking entity token resides within the quarantine pool cache array.
	 *
	 * @since  1.0.0
	 * @param  string $token The target investigation tracker.
	 * @return bool          True if unrouted storage payload tracking data exists, false otherwise.
	 */
	private function is_quarantined( string $token ): bool {
		$quarantine_pool = $this->get_quarantine_pool();
		return isset( $quarantine_pool[ $token ] );
	}

	/**
	 * Gets an option value from the quarantine collection option cache structure.
	 *
	 * @since  1.0.0
	 * @param  string $token   The requested trace identifier.
	 * @param  mixed  $default Fallback value if storage allocations evaluate as missing.
	 * @return mixed           The unrouted metadata block structure payload data.
	 */
	private function get_quarantined( string $token, mixed $default = null ): mixed {
		$quarantine_pool = $this->get_quarantine_pool();
		return $quarantine_pool[ $token ] ?? $default;
	}

	/**
	 * Updates an individual key element inside the serialized quarantine collection pool storage wrapper.
	 *
	 * @since  1.0.0
	 * @param  string $token The destination trace pointer.
	 * @param  mixed  $value The raw payload data assignment block.
	 * @return bool          True if tracking array mutations are written successfully, false on database failure.
	 */
	private function update_quarantined( string $token, mixed $value ): bool {
		$quarantine_pool           = $this->get_quarantine_pool();
		$quarantine_pool[ $token ] = $value;
		return $this->save_quarantine_pool( $quarantine_pool );
	}

	/**
	 * Gets the raw quarantine collection array matrix option from the physical database layer.
	 *
	 * @since  1.0.0
	 * @return array<string, mixed> The unrouted fallback storage pool mapping configuration data structure.
	 */
	public function get_quarantine_pool(): array {
		$pool = get_option( $this->quarantine_pool_option_key, array() );
		return is_array( $pool ) ? $pool : array();
	}

	/**
	 * Saves the serialized quarantine dataset block registry back to the database.
	 *
	 * @since  1.0.0
	 * @param  array $quarantine_pool The complete mutated fallback option matrix tracker collection block.
	 * @return bool                  True if database commitment operations finish successfully, false on failures.
	 */
	private function save_quarantine_pool( array $quarantine_pool ): bool {
		return update_option( $this->quarantine_pool_option_key, $quarantine_pool );
	}
}
