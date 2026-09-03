<?php
/**
 * Software Baseline Configuration for Admin Toolbar Cleanups.
 *
 * @package DeWittePrins\CoreFunctionality
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	array(
		'node_id'          => 'wp-logo',
		'target_parent_id' => 'cf_more_admin_bar_menus',
		'scope'            => 'all',
	),
	array(
		'node_id'          => 'updates',
		'target_parent_id' => 'cf_more_admin_bar_menus',
		'scope'            => 'all',
	),
	array(
		'node_id'          => 'comments',
		'target_parent_id' => 'cf_more_admin_bar_menus',
		'scope'            => 'all',
	),
);
