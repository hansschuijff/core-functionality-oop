<?php
/**
 * Plugin Name:       Core Functionality OOP
 * Description:       Central Microkernel framework managing standalone modules and contextual guards.
 * Version:           1.0.0
 * Author:            Hans Schuijff
 * Text Domain:       dwp-cf
 * Domain Path:       /languages
 *
 * @package DeWittePrins\CoreFunctionality
 */

namespace DeWittePrins\CoreFunctionality;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Onwrikbare poortwachter.
}

if ( ! defined( 'DWP_CF_PLUGIN_DIR' ) ) {
	define( 'DWP_CF_PLUGIN_DIR', __DIR__ );
}


// 1. Laat Composer de complete klasse-distributie (PSR-4) beheren
if ( is_readable( __DIR__ . '/vendor/autoload.php' ) ) {
	require_once __DIR__ . '/vendor/autoload.php';
}

// 2. Start de verkeersleider.
new \DeWittePrins\CoreFunctionality\Plugin( __FILE__ );
