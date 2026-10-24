<?php
/**
 * Schema Glue for Yoast & The Events Calendar Verification Guard.
 *
 * @package DeWittePrins\CoreFunctionality\Environment\Plugins
 * @author  Hans Schuijff <@hansschuijff>
 * @license GPL-2.0
 * @since   1.0.0
 */

declare( strict_types=1 );

namespace DeWittePrins\CoreFunctionality\Environment\Plugins;

use DeWittePrins\CoreFunctionality\Environment\DTO\ProofRequirement;
use Override;

/**
 * Class SchemaGlueYoastTec
 *
 * Reports whether Schema Glue for Yoast & The Events Calendar is active and loaded. Supplies no admin links yet.
 *
 * @since 1.0.0
 */
class SchemaGlueYoastTec extends AbstractPlugin {

	#[Override]
	public function get_id(): string {
		return 'schema-glue-yoast-tec';
	}

	#[Override]
	protected function get_proof_requirements(): ProofRequirement {
		// Not installed locally; add a proof once the plugin is available (confirm).
		// Until then only the activation check applies.
		return new ProofRequirement();
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
