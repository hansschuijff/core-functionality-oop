<?php
/**
 * Abstract Module.
 *
 * @package DeWittePrins\CoreFunctionality\Abstracts
 * @author  Hans Schuijff <@hansschuijff>
 * @license GPL-2.0
 * @since   1.0.0
 */

declare( strict_types=1 );

namespace DeWittePrins\CoreFunctionality\Abstracts;

use DeWittePrins\CoreFunctionality\Interfaces\ModuleInterface;
use DeWittePrins\CoreFunctionality\Plugin;

/**
 * Class AbstractModule
 *
 * Base for modules. A module is a container of features: it only describes itself in
 * get_meta(); the getters, the constructor and an empty launch() come from here.
 *
 * @since 1.0.0
 */
abstract class AbstractModule implements ModuleInterface {

	use DescribedByMeta;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 * @param Plugin $plugin The central plugin instance.
	 */
	public function __construct(
		protected readonly Plugin $plugin,
	) {}

	/**
	 * Runs when the Kernel launches the components. Modules normally have nothing to do here.
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public function launch(): void {}
}
