<?php
/**
 * Module Configuration Schema DTO / Blueprint.
 *
 * @package DeWittePrins\CoreFunctionality\Services\Data
 * @since   4.0.0
 */

namespace DeWittePrins\CoreFunctionality\Services\Data;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class ModuleConfigSchema
 *
 * Value object acting as a declarative blueprint for module settings schema definitions.
 *
 * @since 4.0.0
 */
class ModuleConfigSchema {

	/**
	 * Unique identifier string for the module context.
	 *
	 * @since 4.0.0
	 * @var string
	 */
	private string $module_id;

	/**
	 * Human-readable name of the module.
	 *
	 * @since 4.0.0
	 * @var string
	 */
	private string $name;

	/**
	 * Descriptive paragraph text explaining the module's functional capabilities.
	 *
	 * @since 4.0.0
	 * @var string
	 */
	private string $description;

	/**
	 * Registry mapping individual input configuration fields.
	 *
	 * @since 4.0.0
	 * @var array<string, array{type: string, label: string, default: mixed}>
	 */
	private array $fields = array();

	/**
	 * ModuleConfigSchema Constructor.
	 *
	 * @since 4.0.0
	 * @param string $module_id   Unique identifier string for the module.
	 * @param string $name        Human-readable name of the module.
	 * @param string $description Descriptive text explaining the module purpose.
	 */
	public function __construct( string $module_id, string $name, string $description ) {
		$this->module_id   = $module_id;
		$this->name        = $name;
		$this->description = $description;
	}

	/**
	 * Registers a setting field to this module context.
	 *
	 * Supports method chaining for streamlined fluid schema declarations.
	 *
	 * @since  4.0.0
	 * @param  string $field_id       Unique identifier token for the field entity.
	 * @param  string $type           The visual input type element specification.
	 * @param  string $label          The human-readable label shown next to the input.
	 * @param  mixed  $default_value Optional. Baseline fallback value if unconfigured. Default false.
	 * @return self  Returns the instance object to support method chaining.
	 */
	public function add_field( string $field_id, string $type, string $label, $default_value = false ): self {
		$this->fields[ $field_id ] = array(
			'type'    => $type,
			'label'   => $label,
			'default' => $default_value,
		);
		return $this;
	}

	/**
	 * Retrieves the local module identifier token.
	 *
	 * @since  4.0.0
	 * @return string The raw unique module id.
	 */
	public function get_id(): string {
		return $this->module_id;
	}

	/**
	 * Retrieves the human-readable module title.
	 *
	 * @since  4.0.0
	 * @return string The configuration schema title.
	 */
	public function get_name(): string {
		return $this->name;
	}

	/**
	 * Retrieves the descriptive contextual paragraph.
	 *
	 * @since  4.0.0
	 * @return string Detailed module description text.
	 */
	public function get_description(): string {
		return $this->description;
	}

	/**
	 * Retrieves the registered setting fields matrix array.
	 *
	 * @since  4.0.0
	 * @return array<string, array{type: string, label: string, default: mixed}> Registered field definitions.
	 */
	public function get_fields(): array {
		return $this->fields;
	}
}
