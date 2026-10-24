<?php
/**
 * Sensei Pro (WC Paid Courses) Verification Guard.
 *
 * @package DeWittePrins\CoreFunctionality\Environment\Plugins
 */

namespace DeWittePrins\CoreFunctionality\Environment\Plugins;

use DeWittePrins\CoreFunctionality\Environment\DTO\ProofRequirement;
use Override;

/**
 * Class SenseiPro
 *
 * Sensei Pro (WC Paid Courses, plugin folder woothemes-sensei) bundles Sensei LMS and loads it
 * itself when the standalone Sensei LMS plugin is not active. It therefore supplies the same
 * course links as the SenseiLms guard, which it inherits.
 */
class SenseiPro extends SenseiLms {

	#[Override]
	public function get_id(): string {
		return 'sensei-pro';
	}

	#[Override]
	protected function get_proof_requirements(): ProofRequirement {
		// Both are defined by the bundled plugins once the bundle has loaded them (plugins_loaded 1).
		return new ProofRequirement( constants: array( 'SENSEI_LMS_VERSION', 'SENSEI_PRO_VERSION' ) );
	}
}
