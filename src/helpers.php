<?php
/**
 * Global Helpers & Facades for De Witte Prins Core.
 *
 * This file is loaded automatically by Composer before any classes are initialized.
 * Managed under the uniform Core namespace to avoid function prefixes.
 *
 * @package DeWittePrins\Core
 */

namespace DeWittePrins\Core;

if ( ! function_exists( __NAMESPACE__ . '\is_cf_ready' ) ) {
	/**
	 * Dynamic core status verification gateway.
	 *
	 * Verifies if the core kernel, a specific module, or an encapsulated
	 * micro-feature is fully validated and active in the runtime memory.
	 *
	 * @since 4.0.0
	 * @param string|null $target Optional abstraction identifier token (e.g. 'genesis:theme_options').
	 * @return bool True if fully operational and clear for execution, false otherwise.
	 */
	function is_cf_ready( ?string $target = null ): bool {
		if ( ! class_exists( '\DeWittePrins\Core' ) || ! \DeWittePrins\Core::is_alive() ) {
			return false;
		}

		if ( null === $target ) {
			return true;
		}

		if ( false !== strpos( $target, ':' ) ) {
			list( $module, $feature ) = explode( ':', $target, 2 );
			return \DeWittePrins\Core::is_feature_active( $module, $feature );
		}

		return \DeWittePrins\Core::is_active( $target );
	}
}

if ( ! function_exists( __NAMESPACE__ . '\call_when_ready' ) ) {
	/**
	 * Seamless runtime execution orchestrator for third-party extensions.
	 *
	 * Executes the provided launch payload immediately if the core plugin is already fully
	 * booted, or defers it safely to the reactive state queue to prevent missed events.
	 *
	 * @since 4.0.0
	 * @param callable $callback The structural code payload execution target block.
	 * @return void
	 */
	function call_when_ready( callable $callback ): void {
		if ( class_exists( '\DeWittePrins\Core' ) ) {
			if ( \DeWittePrins\Core::is_cf_ready() ) {
				call_user_func( $callback );
			} else {
				// if not yet ready, defer execution to the core's reactive state queue.
				add_action( 'dwp_core_functionality_ready', $callback );
			}
		}
	}
}




if ( ! function_exists( __NAMESPACE__ . '\log' ) ) {
	/**
	 * Central diagnostic logging dispatcher gateway.
	 *
	 * Silently bypasses execution if the core kernel is inactive to guarantee a
	 * completely crash-free debugging workflow anywhere on the website.
	 *
	 * @since 4.0.0
	 * @param array $data Compiled debugging parameters tracking filters, hooks, and variables.
	 * @return void
	 */
	function log( array $data ): void {
		if ( is_cf_ready() ) {
			$logger = new \DeWittePrins\Core\Services\Logger();
			$logger->write( $data );
		}
	}
}
