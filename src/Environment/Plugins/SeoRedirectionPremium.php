<?php
namespace DeWittePrins\CoreFunctionality\Environment\Plugins;

use DeWittePrins\CoreFunctionality\Enums\Orientation;
use DeWittePrins\CoreFunctionality\DTO\ToolbarShortcut;
use DeWittePrins\CoreFunctionality\Environment\DTO\ProofRequirement;
use Override;

use function __;
use function admin_url;

class SeoRedirectionPremium extends AbstractPlugin {

	#[Override]
	public function get_id(): string {
		return 'seo-redirection-premium';
	}

	#[Override]
	protected function get_proof_requirements(): ProofRequirement {
		return new ProofRequirement( constants: 'SR_PLUGIN_NAME' );
	}

	#[Override]
	protected function get_toolbar_nodes(): array {
		return array(
			new ToolbarShortcut(
				id:      'cf-seo-redirects-all',
				title:   __( 'Redirects', 'core-functionality' ),
				href:    admin_url( 'options-general.php?page=seo-redirection-premium.php' ),
				label:   __( 'Link to', 'core-functionality' ) . ' ' . __( 'SEO Redirects', 'core-functionality' ),
				orientation: Orientation::ADMIN,
			),
			new ToolbarShortcut(
				id:      'cf-seo-redirects-404',
				title:   __( 'Redirect 404s', 'core-functionality' ),
				href:    admin_url( 'options-general.php?page=seo-redirection-premium.php&SR_tab=404_manager' ),
				label:   __( 'Link to', 'core-functionality' ) . ' ' . __( 'Redirect 404s', 'core-functionality' ),
				orientation: Orientation::ADMIN,
			),
		);
	}

	#[Override]
	protected function get_menu_nodes(): array {
		return array(); }
}
