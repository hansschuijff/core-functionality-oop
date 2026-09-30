<?php
/**
 * Proof Requirement DTO.
 *
 * @package DeWittePrins\CoreFunctionality\Environment\DTO
 */

declare(strict_types=1);

namespace DeWittePrins\CoreFunctionality\Environment\DTO;

use function is_array;

/**
 * Class ProofRequirement
 *
 * Defines strict environmental footprints that must (or must not) be met.
 *
 * @since 1.0.0
 */
readonly class ProofRequirement {

	/**
	 * @var string[] Required classes.
	 */
	public array $classes;

	/**
	 * @var string[] Required functions.
	 */
	public array $functions;

	/**
	 * @var string[] Required class methods.
	 */
	public array $methods;

	/**
	 * @var string[] Required global constants.
	 */
	public array $constants;

	/**
	 * @var string[] Action hooks that MUST have fired or be running.
	 */
	public array $actions;

	/**
	 * @var string[] Action hooks that MUST NOT have fired or be running.
	 */
	public array $not_actions;

	/**
	 * Constructor.
	 *
	 * Automatically normalizes mixed string/array inputs to strict arrays.
	 *
	 * @param string|string[] $classes     Classes that must exist.
	 * @param string|string[] $functions   Global functions that must exist.
	 * @param string|string[] $methods     Class methods that must exist (format: 'Class::method').
	 * @param string|string[] $constants   Constants that must be defined.
	 * @param string|string[] $actions     Actions that must have fired.
	 * @param string|string[] $not_actions Actions that must NOT have fired.
	 */
	public function __construct(
		string|array $classes     = array(),
		string|array $functions   = array(),
		string|array $methods     = array(),
		string|array $constants   = array(),
		string|array $actions     = array(),
		string|array $not_actions = array()
	) {
		$this->classes     = is_array( $classes )     ? $classes     : array( $classes );
		$this->functions   = is_array( $functions )   ? $functions   : array( $functions );
		$this->methods     = is_array( $methods )     ? $methods     : array( $methods );
		$this->constants   = is_array( $constants )   ? $constants   : array( $constants );
		$this->actions     = is_array( $actions )     ? $actions     : array( $actions );
		$this->not_actions = is_array( $not_actions ) ? $not_actions : array( $not_actions );
	}
}
