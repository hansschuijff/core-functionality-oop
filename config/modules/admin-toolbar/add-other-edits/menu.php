<?php
/**
 * Runtime configuration defaults for the quick link admin-toolbar menu.
 *
 * Returns a list (array) of link attributes and dependant plugins
 *
 * @package     DeWittePrins\CoreFunctionality
 * @since       1.0.0
 * @author      Hans Schuijff
 * @link        https://dewitteprins.nl
 * @license     GNU-2.0+
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	// Other Edits.
	array(
		'node_args'  =>
			array(
				'title'  => \__( 'Edit Other', 'core-functionality-dwp' ),
				'parent' => 'site-name',
				'id'     => 'cf-shortcuts-Edit-Other',
				'href'   => \admin_url( 'edit.php?post_type=page', 'admin' ),
				'meta'   => array(
					'title' => \__( 'Link to', 'core-functionality-dwp' )
							. ' '
							. \__( 'Pages', 'core-functionality-dwp' ),
				),
			),
		'visibility' => 'both',
		'dependency' => array(
			'plugin' => '',  // No plugin dependencies.
		),
	),
	// Other Edits -> Reusable Blocks.
	array(
		'node_args'  =>
			array(
				'title'  => \__( 'Reusable blocks', 'core-functionality-dwp' ),
				'parent' => 'cf-shortcuts-Edit-Other',
				'id'     => 'cf-shortcuts-reusable-blocks',
				'href'   => \admin_url( 'edit.php?post_type=wp_block', 'admin' ),
				'meta'   => array(
					'title' => \__( 'Link to', 'core-functionality-dwp' )
							. ' '
							. \__( 'Reusable blocks', 'core-functionality-dwp' ),
				),
			),
		'visibility' => 'both',
		'dependency' => array(
			'plugin' => '',  // No plugin dependencies.
		),
	),
	// Other Edits -> Pages.
	array(
		'node_args'  =>
			array(
				'title'  => \__( 'Pages', 'core-functionality-dwp' ),
				'parent' => 'cf-shortcuts-Edit-Other',
				'id'     => 'cf-shortcuts-pages',
				'href'   => \admin_url( 'edit.php?post_type=page', 'admin' ),
				'meta'   => array(
					'title' => \__( 'Link to', 'core-functionality-dwp' )
							. ' '
							. \__( 'Pages', 'core-functionality-dwp' ),
				),
			),
		'visibility' => 'both',
		'dependency' => array(
			'plugin' => '',  // No plugin dependencies.
		),
	),
	// Other Edits -> Comments.
	array(
		'node_args'  =>
			array(
				'title'  => \__( 'Comments', 'core-functionality-dwp' ),
				'parent' => 'cf-shortcuts-Edit-Other',
				'id'     => 'cf-shortcuts-comments',
				'href'   => \admin_url( 'edit-comments.php', 'admin' ),
				'meta'   => array(
					'title' => \__( 'Link to', 'core-functionality-dwp' )
							. ' '
							. \__( 'Comments', 'core-functionality-dwp' ),
				),
			),
		'visibility' => 'both',
		'dependency' => array(
			'plugin' => '',  // No plugin dependencies.
		),
	),
	// Other Edits -> Forms.
	array(
		'node_args'  =>
			array(
				'title'  => \__( 'Forms', 'core-functionality-dwp' ),
				'parent' => 'cf-shortcuts-Edit-Other',
				'id'     => 'cf-shortcuts-gravityforms',
				'href'   => \admin_url( 'admin.php?page=gf_edit_forms', 'admin' ),
				'meta'   => array(
					'title' => \__( 'Link to', 'core-functionality-dwp' )
							. ' '
							. \__( 'Gravity Forms', 'core-functionality-dwp' ),
				),
			),
		'visibility' => 'both',
		'dependency' => array(
			'plugin' => 'Gravity Forms',
		),
	),
	// Other Edits -> Forms - wpforms.
	array(
		'node_args'  =>
			array(
				'title'  => \__( 'Forms', 'core-functionality-dwp' ),
				'parent' => 'cf-shortcuts-Edit-Other',
				'id'     => 'cf-shortcuts-wpforms',
				'href'   => \admin_url( 'admin.php?page=wpforms-overview', 'admin' ),
				'meta'   => array(
					'title' => \__( 'Link to', 'core-functionality-dwp' )
							. ' '
							. \__( 'WPForms', 'core-functionality-dwp' ),
				),
			),
		'visibility' => 'both',
		'dependency' => array(
			'plugin' => 'WPForms',
		),
	),
	// Other Edits -> Users.
	array(
		'node_args'  =>
			array(
				'title'  => \__( 'Users', 'core-functionality-dwp' ),
				'parent' => 'cf-shortcuts-Edit-Other',
				'id'     => 'cf-shortcuts-users',
				'href'   => \admin_url( 'users.php', 'admin' ),
				'meta'   => array(
					'title' => \__( 'Link to', 'core-functionality-dwp' )
							. ' '
							. \__( 'Users', 'core-functionality-dwp' ),
				),
			),
		'visibility' => 'both',
		'dependency' => array(
			'plugin' => '',  // No plugin dependencies.
		),
	),
);
