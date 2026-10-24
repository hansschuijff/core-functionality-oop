<?php
/**
 * Abstract Feature.
 *
 * @package DeWittePrins\CoreFunctionality\Abstracts
 * @author  Hans Schuijff <@hansschuijff>
 * @license GPL-2.0
 * @since   1.0.0
 */

declare( strict_types=1 );

namespace DeWittePrins\CoreFunctionality\Abstracts;

use DeWittePrins\CoreFunctionality\Interfaces\FeatureInterface;
use DeWittePrins\CoreFunctionality\Interfaces\ModuleInterface;
use DeWittePrins\CoreFunctionality\Plugin;

/**
 * Class AbstractFeature
 *
 * Base for features. A feature describes itself in get_meta() and does its work in launch();
 * the getters, the constructor and get_module() come from here.
 *
 * @since 1.0.0
 */
abstract class AbstractFeature implements FeatureInterface {

	use DescribedByMeta;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 * @param ModuleInterface $module The parent module.
	 * @param Plugin          $plugin The central plugin instance.
	 */
	public function __construct(
		protected readonly ModuleInterface $module,
		protected readonly Plugin $plugin,
	) {}

	/**
	 * Returns the parent module.
	 *
	 * @since  1.0.0
	 * @return ModuleInterface The parent module.
	 */
	public function get_module(): ModuleInterface {
		return $this->module;
	}

	/**
	 * Adds the feature's hooks. Runs when the Kernel launches the components.
	 *
	 * @since  1.0.0
	 * @return void
	 */
	abstract public function launch(): void;
}
