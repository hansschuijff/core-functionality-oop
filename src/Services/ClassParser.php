<?php
/**
 * String Manipulation and Parsing Utilities.
 *
 * @package DeWittePrins\CoreFunctionality\Services
 * @author  Hans Schuijff <@hansschuijff>
 * @license GPL-2.0
 * @since   1.0.0
 */

declare(strict_types=1);

namespace DeWittePrins\CoreFunctionality\Services;

use DeWittePrins\CoreFunctionality\Enums\DependencyType;
use DeWittePrins\CoreFunctionality\Interfaces\ModuleInterface;
use DeWittePrins\CoreFunctionality\Interfaces\FeatureInterface;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class ClassParser
 *
 * Provides centralized text transform services and identifier classification helpers.
 *
 * @since 1.0.0
 */
class ClassParser {

	/**
	 * Disable instantiation.
	 *
	 * @since 1.0.0
	 */
	private function __construct() {}

	/**
	 * Returns the namespace of a given FQCN classname.
	 *
	 * @since  1.0.0
	 * @param  string $fqcn Fully Qualified Class Name.
	 * @return string       Namespace of the FQCN.
	 */
	public static function namespace_of_fqcn( string $fqcn ): string {
		$namespace = \str_contains( $fqcn, '\\' )
			? \substr( $fqcn, 0, \strrpos( $fqcn, '\\' ) )
			: '';

		return $namespace;
	}

	/**
	 * Returns the slug (FQCN minus the namespace) of a given FQCN classname.
	 *
	 * @since  1.0.0
	 * @param  string $fqcn Fully Qualified Class Name.
	 * @return string       Slug: FQCN minus the namespace.
	 */
	public static function fqcn_remove_namespace( string $fqcn ): string {
		$slug = \str_contains( $fqcn, '\\' )
			? \substr( $fqcn, \strrpos( $fqcn, '\\' ) + 1 )
			: $fqcn;

		return $slug;
	}

	/**
	 * Checks if a class implements a specific interface.
	 *
	 * @since  1.0.0
	 * @param  string $class     The Fully Qualified Class Name string to inspect.
	 * @param  string $interface The Fully Qualified Interface Name string to test against.
	 * @return bool              True if the class implements the interface, false otherwise.
	 */
	public static function is_implementation_of( string $class, string|array $interface ): bool {
		if ( ! class_exists( $class ) ) {
			return false;
		}

		$interfaces = (array) $interface;
		foreach ( $interfaces as $int => $interface_fqcn ) {
			if ( ! interface_exists( $interface_fqcn ) ) {
				continue;
			}
			if ( is_subclass_of( $class, $interface_fqcn ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Converts double escaped backslashes into canonical single backslashes within a class name string.
	 *
	 * Converts tokens like 'DeWittePrins\\CoreFunctionality' to 'DeWittePrins\CoreFunctionality'.
	 *
	 * @since  1.0.0
	 * @param  string $fqcn The raw class name string containing escaped backslashes.
	 * @return string       The cleaned class name string with single backslashes.
	 */
	public static function to_single_backslashes( string $fqcn ): string {
		return str_replace( '\\\\', '\\', $fqcn );
	}

	/**
	 * Checks if a given environment identifier represents a theme context.
	 *
	 * @since  1.0.0
	 * @param  string $dependency_id The raw environment identifier token.
	 * @return bool                  True if the identifier belongs to a theme, false otherwise.
	 */
	public static function is_theme_id( string $dependency_id ): bool {
		return str_ends_with( $dependency_id, '_theme' );
	}

	/**
	 * Checks if a given environment identifier represents a valid plugin guard class.
	 *
	 * @since  1.0.0
	 * @param  string $id The environment identifier token.
	 * @return bool       True if it is a verified plugin identifier, false otherwise.
	 */
	public static function is_plugin_id( string $id ): bool {
		$type = self::get_dependency_type( $id );

		if ( DependencyType::PLUGIN === $type && self::id_fqcn_exists( $id ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Checks if a given environment identifier represents a server/hosting environment context.
	 *
	 * @since  1.0.0
	 * @param  string $dependency_id The raw environment identifier token.
	 * @return bool                  True if the identifier belongs to a server environment, false otherwise.
	 */
	public static function is_environment_id( string $dependency_id ): bool {
		return str_ends_with( $dependency_id, '_environment' );
	}

	/**
	 * Removes descriptive type suffixes from an environment identifier token.
	 *
	 * Converts tokens like 'essence_pro_theme' to its clean slug 'essence_pro'.
	 *
	 * @since  1.0.0
	 * @param  string $dependency_id The raw environment identifier token.
	 * @return string                Cleaned structural token.
	 */
	public static function get_clean_id( string $dependency_id ): string {
		return str_replace( array( '_theme', '_environment' ), '', $dependency_id );
	}

	/**
	 * Transforms a snake_case identifier slug into a PascalCase class definition name.
	 *
	 * Converts tokens like 'any_genesis' into 'AnyGenesis'.
	 *
	 * @since  1.0.0
	 * @param  string $clean_id The cleaned token slug.
	 * @return string           CamelCased class identifier name.
	 */
	public static function to_camel_case( string $clean_id ): string {
		return str_replace( ' ', '', ucwords( str_replace( array( '-', '_' ), ' ', $clean_id ) ) );
	}

	/**
	 * Converts a camelCase or PascalCase string into a lowercase snake_case-equivalent with dashes (kebab-case).
	 *
	 * @since  1.0.0
	 * @param  string $string The camelCase or PascalCase identifier.
	 * @return string         The formatted lowercase kebab-case string.
	 */
	public static function camel_to_kebab( string $string ): string {
		if ( empty( $string ) ) {
			return '';
		}

		$kebab = preg_replace( '/([a-z0-9])([A-Z])/', '$1-$2', $string );
		$kebab = strtolower( (string) $kebab );
		$kebab = str_replace( '_', '-', $kebab );
		$kebab = preg_replace( '/-+/', '-', $kebab );

		return trim( $kebab, '-' );
	}

	/**
	 * Translates a dependency ID to a Fully Qualified Class Name of the Class it belongs to.
	 *
	 * @since  1.0.0
	 * @param  string $id The identifier of a theme, plugin or server environment.
	 * @return string     The FQCN of the dependency.
	 */
	public static function id_to_fqcn( string $id ): string {
		$clean_id        = self::get_clean_id( $id );
		$class_slug      = self::to_camel_case( $class_slug ?? $clean_id );
		$class_namespace = self::get_dependency_namespace( $id );

		return $class_namespace . '\\' . $class_slug;
	}

	/**
	 * Translates a dependency ID to a Fully Qualified Class Name and checks if it exists.
	 *
	 * @since  1.0.0
	 * @param  string $id The identifier of a theme, plugin or server environment.
	 * @return bool       True if the translated class of a dependency ID actually exists, otherwise false.
	 */
	public static function id_fqcn_exists( string $id ): bool {
		$plugin_class_name = self::id_to_fqcn( $id );

		return class_exists( $plugin_class_name );
	}

	/**
	 * Returns the type of dependency based on the suffix in a dependency ID.
	 *
	 * @since  1.0.0
	 * @param  string $id The identifier of a theme, plugin or server environment.
	 * @return DependencyType Enum for the type of dependency (plugin, theme, server environment).
	 */
	public static function get_dependency_type( string $id ): DependencyType {
		if ( self::is_theme_id( $id ) ) {
			return DependencyType::THEME;
		}
		if ( self::is_environment_id( $id ) ) {
			return DependencyType::SERVER;
		}

		return DependencyType::PLUGIN;
	}

	/**
	 * Determines the correct namespace for a given dependency identifier.
	 *
	 * @since  1.0.0
	 * @param  string $id The identifier of a theme, plugin or server environment.
	 * @return string     The fully qualified namespace for the dependency.
	 */
	public static function get_dependency_namespace( string $id ): string {
		$namespace_base = '\\DeWittePrins\\CoreFunctionality\\Environment\\';
		$type           = self::get_dependency_type( $id );

		$namespace_suffix = match ( $type ) {
			DependencyType::THEME  => 'Themes',
			DependencyType::SERVER => 'Servers',
			DependencyType::PLUGIN => 'Plugins',
		};

		return $namespace_base . $namespace_suffix;
	}

	/**
	 * Checks if a string represents a valid module class mapping.
	 *
	 * @since  1.0.0
	 * @param  string $module_fqcn Fully Qualified Class Name of a module.
	 * @return bool                 True if the class implements the ModuleInterface, false otherwise.
	 */
	public static function is_module_fqcn( string $module_fqcn ): bool {
		return self::is_implementation_of( $module_fqcn, ModuleInterface::class );
	}

	/**
	 * Checks if a string represents a valid feature class mapping.
	 *
	 * @since  1.0.0
	 * @param  string $feature_fqcn Fully Qualified Class Name of a feature.
	 * @return bool                  True if the class implements the FeatureInterface, false otherwise.
	 */
	public static function is_feature_fqcn( string $feature_fqcn ): bool {
		return self::is_implementation_of( $feature_fqcn, FeatureInterface::class );
	}

	/**
	 * Enforces that a PHP namespace string consistently terminates with a trailing backslash.
	 *
	 * @since  1.0.0
	 * @param  string $namespace A string representing a structural namespace.
	 * @return string            Normalized namespace string ending with a clean trailing backslash.
	 */
	public static function trailingslashit_namespace( string $namespace ): string {
		return self::untrailingslashit_namespace( $namespace ) . '\\';
	}

	/**
	 * Enforces that a PHP namespace string consistently terminates without a trailing backslash.
	 *
	 * @since  1.0.0
	 * @param  string $namespace A string representing a structural namespace.
	 * @return string            Normalized namespace string not ending with a clean trailing backslash.
	 */
	public static function untrailingslashit_namespace( string $namespace ): string {
		return rtrim( $namespace, '\\' );
	}

	/**
	 * Trims trailing forward- and backward slash from path.
	 *
	 * @since  1.0.0
	 * @param  string $path The raw file directory path string.
	 * @return string       Cleaned path string without trailing slash artifacts.
	 */
	public static function untrailingslashit( string $path ): string {
		return rtrim( $path, '/\\' );
	}
}
