<?php
/**
 * Abstract Plugin Environment Guard Component.
 *
 * @package    DeWittePrins\CoreFunctionality
 * @subpackage Environment\Plugins
 * @author     Hans Schuijff <@hansschuijff>
 * @license    GPL-2.0
 * @since      1.0.0
 */

declare(strict_types=1);

namespace DeWittePrins\CoreFunctionality\Environment\Plugins;

use DeWittePrins\CoreFunctionality\Environment\Plugins\Interfaces\PluginInterface;
use DeWittePrins\CoreFunctionality\Environment\Plugins\Services\PluginInspector;
use DeWittePrins\CoreFunctionality\Environment\EnvironmentLookup;
use DeWittePrins\CoreFunctionality\Environment\Registers\AdminToolbarRegister;
use DeWittePrins\CoreFunctionality\Environment\Registers\AdminMenuRegister;
use DeWittePrins\CoreFunctionality\Environment\DTO\ProofRequirement;
use DeWittePrins\CoreFunctionality\Services\ClassParser;
use Override;

use function current;
use function is_string;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class AbstractPlugin
 *
 * Provides base implementation for tracking and verifying active plugin dependencies.
 *
 * @since 1.0.0
 */
abstract class AbstractPlugin implements PluginInterface {

	/**
	 * The configuration data (basename path) of the plugin.
	 *
	 * @var string
	 */
	protected string $plugin_basename = '';

	/**
	 * Class Constructor.
	 *
	 * @since 1.0.0
	 * @param EnvironmentLookup $lookup The environment registry lookup service.
	 */
	public function __construct( private readonly EnvironmentLookup $lookup ) {
		$clean_id = ClassParser::get_clean_id( $this->get_id() );
		$basename = $this->lookup->get_plugin_basename( $clean_id );

		if ( is_string( $basename ) ) {
			$this->plugin_basename = $basename;
		}
	}

	/**
	 * Inspects plugin parameters using the centralized service.
	 *
	 * Feeds the shared registries dynamically once readiness is affirmatively verified.
	 *
	 * @since 1.0.0
	 * @return bool True if the target plugin is active in the environment, false otherwise.
	 */
	public function is_ready(): bool {
		if ( empty( $this->plugin_basename ) ) {

			return false;
		}

		// Guard: Lightweight database check. If it's not activated, abort immediately.
		if ( ! PluginInspector::is_plugin_active( $this->plugin_basename ) ) {
			return false;
		}

		// Deep proof checks are only executed if the activation guard has passed.
		if ( ! $this->has_proof_of_life() ) {
			return false;
		}

		// Feed the static master registries immediately upon successful validation.
		AdminToolbarRegister::register( $this->get_id(), $this->get_toolbar_nodes() );
		AdminMenuRegister::register( $this->get_id(), $this->get_menu_nodes() );

		return true;
	}

	/**
	 * Default fallback proof of life check for plugins using the structured DTO.
	 *
	 * Child classes should override get_proof_requirements() to provide defensive criteria.
	 *
	 * @since 1.0.0
	 * @return bool True if all criteria pass, false otherwise.
	 */
	public function has_proof_of_life(): bool {
		return PluginInspector::must_exist( $this->get_proof_requirements() );
	}

	/**
	 * Defines the technical proof criteria required for verification.
	 *
	 * Replicated as a safe default fallback. Overriding this prevents boilerplate in child components.
	 *
	 * @return ProofRequirement The structured proof criteria object.
	 */
	protected function get_proof_requirements(): ProofRequirement {
		return new ProofRequirement();
	}

	/**
	 * Compiles and returns the available pool of toolbar shortcuts this plugin can offer.
	 *
	 * Must be implemented by child classes to supply their links to the register feed.
	 *
	 * @return \DeWittePrins\CoreFunctionality\DTO\ToolbarShortcut[]
	 */
	abstract protected function get_toolbar_nodes(): array;

	/**
	 * Compiles and returns the available pool of admin menu blocks this plugin can offer.
	 *
	 * Must be implemented by child classes to supply their links to the register feed.
	 *
	 * @return \DeWittePrins\CoreFunctionality\DTO\AdminMenuBlock[]
	 */
	abstract protected function get_menu_nodes(): array;
}
