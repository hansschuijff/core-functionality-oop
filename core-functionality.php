<?php
/**
 * Plugin Name:       Core Functionality OOP
 * Description:       Central Microkernel framework managing standalone modules and contextual guards.
 * Version:           4.0.0
 * Author:            Hans Schuijff
 * Text Domain:       core-functionality-dwp
 * Domain Path:       /languages
 *
 * @package DeWittePrins\CoreFunctionality
 */

namespace DeWittePrins\CoreFunctionality;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Onwrikbare poortwachter.
}

// 1. Laat Composer de complete klasse-distributie (PSR-4) beheren
if ( is_readable( __DIR__ . '/vendor/autoload.php' ) ) {
	require_once __DIR__ . '/vendor/autoload.php';
}

// 2. Start de gelaagde machinekamer loepzuiver op
$plugin = new Plugin( __FILE__ );
