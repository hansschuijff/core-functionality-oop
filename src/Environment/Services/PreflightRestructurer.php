<?php
/**
 * Preflight Matrix Restructurer.
 *
 * @package DeWittePrins\CoreFunctionality\Environment\Services
 */

declare(strict_types=1);

namespace DeWittePrins\CoreFunctionality\Environment\Services;

use InvalidArgumentException;

use function array_filter;
use function array_is_list;
use function count;
use function current;
use function is_array;
use function is_string;
use function trim;

/**
 * Class PreflightRestructurer
 *
 * A recursive top-down parser that normalizes preflight configurations
 * and strictly prunes empty inputs down to an empty array context.
 *
 * @since 1.0.0
 */
class PreflightRestructurer {

	/**
	 * Restructures raw developer input into a normalized multi-dimensional array matrix.
	 *
	 * @since  1.0.0
	 * @param  mixed $input The raw value from get_dependencies().
	 * @return array The strict structural matrix format, or an empty array if no criteria exist.
	 *
	 * @throws InvalidArgumentException If the provided structure is invalid or unrecognizable.
	 */
	public static function restructure( mixed $input ): array {
		if ( empty( $input ) ) {
			return array();
		}

		// Look for the outermost explicit OR-shell
		if ( is_array( $input ) && isset( $input['or'] ) ) {
			$input = $input['or'];
		}

		$compiled_or_matrix = array();

		// Execute the recursive reduction over the inner slice
		self::reduce_to_and_branches( $input, $compiled_or_matrix );

		// Filter out any empty 'and' hulls that might have survived the reduction loop
		$compiled_or_matrix = array_filter(
			$compiled_or_matrix,
			fn( array $branch ) => ! empty( $branch['and'] )
		);

		// If no valid dependencies remained, return a completely clean array
		if ( empty( $compiled_or_matrix ) ) {
			return array();
		}

		return array( 'or' => $compiled_or_matrix );
	}

	/**
	 * Recursively breaks down and normalizes any preflight format into canonical 'and' branches.
	 *
	 * Strictly skips empty strings and prunes empty arrays out of the matrix accumulation.
	 *
	 * @param mixed $slice           The current slice of the preflight configuration.
	 * @param array $compiled_matrix Reference to the accumulator array holding completed 'and' branches.
	 * @return void
	 *
	 * @throws InvalidArgumentException If a structural element cannot be resolved.
	 */
	private static function reduce_to_and_branches( mixed $slice, array &$compiled_matrix ): void {
		if ( empty( $slice ) ) {
			return;
		}

		// Strip single item sequential wrappers -> array('') becomes ''
		while ( is_array( $slice ) && array_is_list( $slice ) && 1 === count( $slice ) ) {
			$slice = current( $slice );
		}

		// Scenario A: We hit a raw string token
		if ( is_string( $slice ) ) {
			$slice = trim( $slice );
			if ( ! empty( $slice ) ) {
				$compiled_matrix[] = array( 'and' => array( $slice ) );
			}
			return;
		}

		if ( is_array( $slice ) ) {
			// Scenario B: Explicit 'and' wrapper -> strip and re-evaluate
			if ( isset( $slice['and'] ) ) {
				self::reduce_to_and_branches( $slice['and'], $compiled_matrix );
				return;
			}

			// Scenario C: A sequential list of alternative or implicit clauses
			if ( array_is_list( $slice ) ) {

				// Inspect if this is a simple flat list of strings
				$is_flat_string_list = true;
				foreach ( $slice as $item ) {
					if ( ! is_string( $item ) ) {
						$is_flat_string_list = false;
						break;
					}
				}

				if ( $is_flat_string_list ) {
					$clean_tokens = array();
					foreach ( $slice as $token ) {
						$token = trim( $token );
						if ( ! empty( $token ) ) {
							$clean_tokens[] = $token;
						}
					}

					// Only append if the AND-cluster actually contains active tokens
					if ( ! empty( $clean_tokens ) ) {
						$compiled_matrix[] = array( 'and' => $clean_tokens );
					}
					return;
				}

				// If it's a mixed list, dive deeper recursively into each item
				foreach ( $slice as $sub_slice ) {
					self::reduce_to_and_branches( $sub_slice, $compiled_matrix );
				}
				return;
			}
		}

		throw new InvalidArgumentException( 'Preflight syntax slice could not be structurally resolved.' );
	}
}
