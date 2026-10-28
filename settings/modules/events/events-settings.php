<?php
/**
 * Defaults of the Event templates settings tab (module Events, feature TemplateFallback).
 *
 * @package DeWittePrins\CoreFunctionality
 * @since   1.0.0
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

return array(
	// Whether the template check reports templates in the active theme that are also CF templates.
	'report_theme_copies' => true,
);
