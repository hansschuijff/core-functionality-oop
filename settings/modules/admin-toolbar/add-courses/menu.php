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

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	// Courses.
	array(
		'node_args'  =>
			array(
				'title'  => \__( 'Courses', 'core-functionality' ),
				'parent' => 'site-name',
				'id'     => 'cf-shortcuts-courses',
				'href'   => \admin_url( 'edit.php?post_type=course', 'admin' ),
				'meta'   => array(
					'title' => \__( 'Link to Sensei LMS', 'core-functionality' )
							. ' '
							. \__( 'Courses', 'core-functionality' ),
				),
			),
		'visibility' => 'both',
		'dependency' => array(
			'plugin' => 'Sensei with WooCommerce Paid Courses',
		),
	),
	// Courses -> View Courses.
	array(
		'node_args'  =>
			array(
				'title'  => \__( 'View', 'core-functionality' ),
				'parent' => 'cf-shortcuts-courses',
				'id'     => 'cf-shortcuts-courses-courses',
				'href'   => \get_site_url( null, '/cursussen/' ),
				'meta'   => array(
					'title' => \__( 'Link to Sensei LMS', 'core-functionality' )
							. ' '
							. \__( 'Courses', 'core-functionality' ),
				),
			),
		'visibility' => 'both',
		'dependency' => array(
			'plugin' => 'Sensei with WooCommerce Paid Courses',
		),
	),
	// Courses -> Grading.
	array(
		'node_args'  =>
			array(
				'title'  => \__( 'Grading', 'core-functionality' ),
				'parent' => 'cf-shortcuts-courses',
				'id'     => 'cf-shortcuts-courses-grading',
				'href'   => \admin_url( 'admin.php?page=sensei_grading', 'admin' ),
				'meta'   => array(
					'title' => \__( 'Link to Sensei LMS', 'core-functionality' )
							. ' '
							. \__( 'Grading', 'core-functionality' ),
				),
			),
		'visibility' => 'both',
		'dependency' => array(
			'plugin' => 'Sensei with WooCommerce Paid Courses',
		),
	),
	// Courses -> Student Management.
	array(
		'node_args'  =>
			array(
				'title'  => \__( 'Student Management', 'core-functionality' ),
				'parent' => 'cf-shortcuts-courses',
				'id'     => 'cf-shortcuts-courses-student-management',
				'href'   => \admin_url( 'admin.php?page=sensei_learners', 'admin' ),
				'meta'   => array(
					'title' => \__( 'Link to Sensei LMS', 'core-functionality' )
							. ' '
							. \__( 'Student Management', 'core-functionality' ),
				),
			),
		'visibility' => 'both',
		'dependency' => array(
			'plugin' => 'Sensei with WooCommerce Paid Courses',
		),
	),
	// Courses -> Edit Courses.
	array(
		'node_args'  =>
			array(
				'title'  => \__( 'Edit', 'core-functionality' ),
				'parent' => 'cf-shortcuts-courses',
				'id'     => 'cf-shortcuts-courses-edit-courses',
				'href'   => \admin_url( 'edit.php?post_type=course', 'admin' ),
				'meta'   => array(
					'title' => \__( 'Link to Sensei LMS', 'core-functionality' )
							. ' '
							. \__( 'Courses', 'core-functionality' ),
				),
			),
		'visibility' => 'both',
		'dependency' => array(
			'plugin' => 'Sensei with WooCommerce Paid Courses',
		),
	),
	// Courses -> Lessons.
	array(
		'node_args'  =>
			array(
				'title'  => \__( 'Lessons', 'core-functionality' ),
				'parent' => 'cf-shortcuts-courses',
				'id'     => 'cf-shortcuts-courses-lessons',
				'href'   => \admin_url( 'edit.php?post_type=lesson', 'admin' ),
				'meta'   => array(
					'title' => \__( 'Link to Sensei LMS', 'core-functionality' )
							. ' '
							. \__( 'Lessons', 'core-functionality' ),
				),
			),
		'visibility' => 'both',
		'dependency' => array(
			'plugin' => 'Sensei with WooCommerce Paid Courses',
		),
	),
	// Courses -> Categories.
	array(
		'node_args'  =>
			array(
				'title'  => \__( 'Categories', 'core-functionality' ),
				'parent' => 'cf-shortcuts-courses',
				'id'     => 'cf-shortcuts-courses-categories',
				'href'   => \admin_url( 'edit-tags.php?taxonomy=course-category&post_type=course', 'admin' ),
				'meta'   => array(
					'title' => \__( 'Link to Sensei LMS', 'core-functionality' )
							. ' '
							. \__( 'Course Categories', 'core-functionality' ),
				),
			),
		'visibility' => 'both',
		'dependency' => array(
			'plugin' => 'Sensei with WooCommerce Paid Courses',
		),
	),
	// Courses -> Modules.
	array(
		'node_args'  =>
			array(
				'title'  => \__( 'Modules', 'core-functionality' ),
				'parent' => 'cf-shortcuts-courses',
				'id'     => 'cf-shortcuts-courses-modules',
				'href'   => \admin_url( 'edit-tags.php?taxonomy=module', 'admin' ),
				'meta'   => array(
					'title' => \__( 'Link to Sensei LMS', 'core-functionality' )
							. ' '
							. \__( 'Modules', 'core-functionality' ),
				),
			),
		'visibility' => 'both',
		'dependency' => array(
			'plugin' => 'Sensei with WooCommerce Paid Courses',
		),
	),
	// Courses -> Module Order.
	array(
		'node_args'  =>
			array(
				'title'  => \__( 'Module Order', 'core-functionality' ),
				'parent' => 'cf-shortcuts-courses',
				'id'     => 'cf-shortcuts-courses-module-order',
				'href'   => \admin_url( 'edit.php?post_type=course&page=module-order', 'admin' ),
				'meta'   => array(
					'title' => \__( 'Link to Sensei LMS', 'core-functionality' )
							. ' '
							. \__( 'Module Order', 'core-functionality' ),
				),
			),
		'visibility' => 'both',
		'dependency' => array(
			'plugin' => 'Sensei with WooCommerce Paid Courses',
		),
	),
	// Courses -> Lesson Tags.
	array(
		'node_args'  =>
			array(
				'title'  => \__( 'Lesson Tags', 'core-functionality' ),
				'parent' => 'cf-shortcuts-courses',
				'id'     => 'cf-shortcuts-courses-lesson-tags',
				'href'   => \admin_url( 'edit-tags.php?taxonomy=lesson-tag&post_type=lesson', 'admin' ),
				'meta'   => array(
					'title' => \__( 'Link to Sensei LMS', 'core-functionality' )
							. ' '
							. \__( 'Lesson Tags', 'core-functionality' ),
				),
			),
		'visibility' => 'both',
		'dependency' => array(
			'plugin' => 'Sensei with WooCommerce Paid Courses',
		),
	),
	// Courses -> Questions.
	array(
		'node_args'  =>
			array(
				'title'  => \__( 'Questions', 'core-functionality' ),
				'parent' => 'cf-shortcuts-courses',
				'id'     => 'cf-shortcuts-courses-questions',
				'href'   => \admin_url( 'edit.php?post_type=question', 'admin' ),
				'meta'   => array(
					'title' => \__( 'Link to Sensei LMS', 'core-functionality' )
							. ' '
							. \__( 'Questions', 'core-functionality' ),
				),
			),
		'visibility' => 'both',
		'dependency' => array(
			'plugin' => 'Sensei with WooCommerce Paid Courses',
		),
	),
	// Courses -> Messages.
	array(
		'node_args'  =>
			array(
				'title'  => \__( 'Messages', 'core-functionality' ),
				'parent' => 'cf-shortcuts-courses',
				'id'     => 'cf-shortcuts-courses-messages',
				'href'   => \admin_url( 'edit.php?post_type=sensei_message', 'admin' ),
				'meta'   => array(
					'title' => \__( 'Link to Sensei LMS', 'core-functionality' )
							. ' '
							. \__( 'Messages', 'core-functionality' ),
				),
			),
		'visibility' => 'both',
		'dependency' => array(
			'plugin' => 'Sensei with WooCommerce Paid Courses',
		),
	),
	// Courses -> Settings.
	array(
		'node_args'  =>
			array(
				'title'  => \__( 'Settings', 'core-functionality' ),
				'parent' => 'cf-shortcuts-courses',
				'id'     => 'cf-shortcuts-courses-sensei-settings',
				'href'   => \admin_url( 'admin.php?page=sensei-settings', 'admin' ),
				'meta'   => array(
					'title' => \__( 'Link to Sensei LMS', 'core-functionality' )
							. ' '
							. \__( 'Settings', 'core-functionality' ),
				),
			),
		'visibility' => 'both',
		'dependency' => array(
			'plugin' => 'Sensei with WooCommerce Paid Courses',
		),
	),
	// Courses -> Analytics.
	array(
		'node_args'  =>
			array(
				'title'  => \__( 'Analytics', 'core-functionality' ),
				'parent' => 'cf-shortcuts-courses',
				'id'     => 'cf-shortcuts-courses-analytics',
				'href'   => \admin_url( 'admin.php?page=sensei_analysis', 'admin' ),
				'meta'   => array(
					'title' => \__( 'Link to Sensei LMS', 'core-functionality' )
							. ' '
							. \__( 'Analytics', 'core-functionality' ),
				),
			),
		'visibility' => 'both',
		'dependency' => array(
			'plugin' => 'Sensei with WooCommerce Paid Courses',
		),
	),
);
