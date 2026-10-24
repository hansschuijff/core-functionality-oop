<?php
/**
 * Sensei Pro Verification Guard.
 *
 * @package DeWittePrins\CoreFunctionality\Environment\Plugins
 */

namespace DeWittePrins\CoreFunctionality\Environment\Plugins;

use DeWittePrins\CoreFunctionality\Environment\DTO\ProofRequirement;
use Override;

/**
 * Class SenseiPro
 *
 * Sensei Pro extends Sensei LMS and has no admin screens of its own; the course links
 * come from the SenseiLms guard.
 */
class SenseiPro extends AbstractPlugin {

	#[Override]
	public function get_id(): string {
		return 'sensei-pro';
	}

	#[Override]
	protected function get_proof_requirements(): ProofRequirement {
		// Sensei Pro runs on top of Sensei LMS (confirm: constant name, plugin not installed locally).
		return new ProofRequirement( constants: array( 'SENSEI_LMS_VERSION', 'SENSEI_PRO_VERSION' ) );
	}

	#[Override]
	protected function get_toolbar_nodes(): array {
		return array();
	}

	#[Override]
	protected function get_menu_nodes(): array {
		return array();
	}
}
