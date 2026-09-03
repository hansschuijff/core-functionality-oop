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
	// View -> Courses.
	array(
		'node_args'  =>
			array(
				'title'  => \__( 'Courses', 'core-functionality-dwp' ),
				'parent' => 'cf-shortcuts-view',
				'id'     => 'cf-shortcuts-view-sensei-lms',
				'href'   => \get_site_url( null, '/cursussen/' ),
				'meta'   => array(
					'title' => \__( 'Link to Sensei LMS', 'core-functionality-dwp' )
							. ' '
							. \__( 'Courses', 'core-functionality-dwp' ),
				),
			),
		'visibility' => 'both',
		'dependency' => array(
			'plugin' => 'Sensei with WooCommerce Paid Courses',
		),
	),
	// View -> Courses -> Course List.
	array(
		'node_args'  =>
			array(
				'title'  => \__( 'Course List', 'core-functionality-dwp' ),
				'parent' => 'cf-shortcuts-view-sensei-lms',
				'id'     => 'cf-shortcuts-view-sensei-lms-courses-list',
				'href'   => \get_site_url( null, '/cursusoverzicht/' ),
				'meta'   => array(
					'title' => \__( 'Link to Sensei LMS', 'core-functionality-dwp' )
							. ' '
							. \__( 'Course List', 'core-functionality-dwp' ),
				),
			),
		'visibility' => 'both',
		'dependency' => array(
			'plugin' => 'Sensei with WooCommerce Paid Courses',
		),
	),
	// View -> Courses -> Course-category: Mediumschap.
	array(
		'node_args'  =>
			array(
				'title'  => \__( 'Course-category: Mediumschap', 'core-functionality-dwp' ),
				'parent' => 'cf-shortcuts-view-sensei-lms',
				'id'     => 'cf-shortcuts-view-sensei-lms-category-mediumschap',
				'href'   => \get_site_url( null, '/cursuscategorie/mediumschap/' ),
				'meta'   => array(
					'title' => \__( 'Link to Sensei LMS', 'core-functionality-dwp' )
							. ' '
							. \__( 'Course-category: Mediumschap', 'core-functionality-dwp' ),
				),
			),
		'visibility' => 'both',
		'dependency' => array(
			'plugin' => 'Sensei with WooCommerce Paid Courses',
		),
	),
	// View -> Courses -> My Courses.
	array(
		'node_args'  =>
			array(
				'title'  => \__( 'My Courses', 'core-functionality-dwp' ),
				'parent' => 'cf-shortcuts-view-sensei-lms',
				'id'     => 'cf-shortcuts-view-sensei-lms-my-courses',
				'href'   => \get_site_url( null, '/mijn-cursussen/' ),
				'meta'   => array(
					'title' => \__( 'Link to Sensei LMS', 'core-functionality-dwp' )
							. ' '
							. \__( 'My Courses', 'core-functionality-dwp' ),
				),
			),
		'visibility' => 'both',
		'dependency' => array(
			'plugin' => 'Sensei with WooCommerce Paid Courses',
		),
	),
	// View -> Courses -> Learners Profile.
	array(
		'node_args'  =>
			array(
				'title'  => \__( 'Learners Profile', 'core-functionality-dwp' ),
				'parent' => 'cf-shortcuts-view-sensei-lms',
				'id'     => 'cf-shortcuts-view-sensei-lms-learners-profile',
				'href'   => \get_site_url( null, '/student/hansepans/' ),
				'meta'   => array(
					'title' => \__( 'Link to Sensei LMS', 'core-functionality-dwp' )
							. ' '
							. \__( 'Learners Profile', 'core-functionality-dwp' ),
				),
			),
		'visibility' => 'both',
		'dependency' => array(
			'plugin' => 'Sensei with WooCommerce Paid Courses',
		),
	),
	// View -> Courses -> Course: Basis Mediumschap.
	array(
		'node_args'  =>
			array(
				'title'  => \__( 'Course: Basis Mediumschap', 'core-functionality-dwp' ),
				'parent' => 'cf-shortcuts-view-sensei-lms',
				'id'     => 'cf-shortcuts-view-sensei-lms-basis-mediumschap',
				'href'   => \get_site_url( null, '/cursus/mediumschap-basis/' ),
				'meta'   => array(
					'title' => \__( 'Link to Sensei LMS', 'core-functionality-dwp' )
							. ' '
							. \__( 'Course: Basis Mediumschap', 'core-functionality-dwp' ),
				),
			),
		'visibility' => 'both',
		'dependency' => array(
			'plugin' => 'Sensei with WooCommerce Paid Courses',
		),
	),
	// View -> Courses -> Course results.
	array(
		'node_args'  =>
			array(
				'title'  => \__( 'Course results', 'core-functionality-dwp' ),
				'parent' => 'cf-shortcuts-view-sensei-lms-basis-mediumschap',
				'id'     => 'cf-shortcuts-view-sensei-lms-basis-mediumschap-results',
				'href'   => \get_site_url( null, '/cursus/mediumschap-basis/results/' ),
				'meta'   => array(
					'title' => \__( 'Link to Sensei LMS', 'core-functionality-dwp' )
							. ' '
							. \__( 'Course results', 'core-functionality-dwp' ),
				),
			),
		'visibility' => 'both',
		'dependency' => array(
			'plugin' => 'Sensei with WooCommerce Paid Courses',
		),
	),
	// View -> Courses -> Module: Orakelen als een orakel.
	array(
		'node_args'  =>
			array(
				'title'  => \__( 'Module: Orakelen als een orakel', 'core-functionality-dwp' ),
				'parent' => 'cf-shortcuts-view-sensei-lms',
				'id'     => 'cf-shortcuts-view-sensei-lms-module-orakelen',
				'href'   => \get_site_url( null, '/modules/orakelen-als-een-orakel/?course_id=6732' ),
				'meta'   => array(
					'title' => \__( 'Link to Sensei LMS', 'core-functionality-dwp' )
							. ' '
							. \__( 'Module: Orakelen als een orakel', 'core-functionality-dwp' ),
				),
			),
		'visibility' => 'both',
		'dependency' => array(
			'plugin' => 'Sensei with WooCommerce Paid Courses',
		),
	),
	// View -> Courses -> Lessons.
	array(
		'node_args'  =>
			array(
				'title'  => \__( 'Lessons', 'core-functionality-dwp' ),
				'parent' => 'cf-shortcuts-view-sensei-lms',
				'id'     => 'cf-shortcuts-view-sensei-lms-lessons',
				'href'   => \get_site_url( null, '/les/' ),
				'meta'   => array(
					'title' => \__( 'Link to Sensei LMS', 'core-functionality-dwp' )
							. ' '
							. \__( 'Lessons', 'core-functionality-dwp' ),
				),
			),
		'visibility' => 'both',
		'dependency' => array(
			'plugin' => 'Sensei with WooCommerce Paid Courses',
		),
	),
	// View -> Courses -> Lesson-tag: Aura.
	array(
		'node_args'  =>
			array(
				'title'  => \__( 'Lesson-tag: Aura', 'core-functionality-dwp' ),
				'parent' => 'cf-shortcuts-view-sensei-lms',
				'id'     => 'cf-shortcuts-view-sensei-lms-lesson-tag-aura',
				'href'   => \get_site_url( null, '/lestag/aura/' ),
				'meta'   => array(
					'title' => \__( 'Link to Sensei LMS', 'core-functionality-dwp' )
							. ' '
							. \__( 'Lesson-tag: Aura', 'core-functionality-dwp' ),
				),
			),
		'visibility' => 'both',
		'dependency' => array(
			'plugin' => 'Sensei with WooCommerce Paid Courses',
		),
	),
	// View -> Courses -> Lesson: Aura Reading.
	array(
		'node_args'  =>
			array(
				'title'  => \__( 'Lesson: Aura Reading', 'core-functionality-dwp' ),
				'parent' => 'cf-shortcuts-view-sensei-lms',
				'id'     => 'cf-shortcuts-view-sensei-lms-lesson-aura-reading',
				'href'   => \get_site_url( null, '/les/auras-waarnemen-en-lezen/' ),
				'meta'   => array(
					'title' => \__( 'Link to Sensei LMS', 'core-functionality-dwp' )
							. ' '
							. \__( 'Lesson: Aura Reading', 'core-functionality-dwp' ),
				),
			),
		'visibility' => 'both',
		'dependency' => array(
			'plugin' => 'Sensei with WooCommerce Paid Courses',
		),
	),
	// View -> Courses -> Quiz: The History of My Psychic Ability.
	array(
		'node_args'  =>
			array(
				'title'  => \__( 'Quiz: The History of My Psychic Ability', 'core-functionality-dwp' ),
				'parent' => 'cf-shortcuts-view-sensei-lms',
				'id'     => 'cf-shortcuts-view-sensei-lms-quiz-my-psychic-ability',
				'href'   => \get_site_url( null, '/test/hoe-ik-mijn-mediumschap-ontwikkelde/' ),
				'meta'   => array(
					'title' => \__( 'Link to Sensei LMS', 'core-functionality-dwp' )
							. ' '
							. \__( 'Quiz: The History of My Psychic Ability', 'core-functionality-dwp' ),
				),
			),
		'visibility' => 'both',
		'dependency' => array(
			'plugin' => 'Sensei with WooCommerce Paid Courses',
		),
	),
	// View -> Courses -> Messages.
	array(
		'node_args'  =>
			array(
				'title'  => \__( 'Messages', 'core-functionality-dwp' ),
				'parent' => 'cf-shortcuts-view-sensei-lms',
				'id'     => 'cf-shortcuts-view-sensei-lms-messages',
				'href'   => \get_site_url( null, '/berichten/' ),
				'meta'   => array(
					'title' => \__( 'Link to Sensei LMS', 'core-functionality-dwp' )
							. ' '
							. \__( 'Messages', 'core-functionality-dwp' ),
				),
			),
		'visibility' => 'both',
		'dependency' => array(
			'plugin' => 'Sensei with WooCommerce Paid Courses',
		),
	),
	// View -> Courses -> Message: I have some questions.
	array(
		'node_args'  =>
			array(
				'title'  => \__( 'Message: I have some questions', 'core-functionality-dwp' ),
				'parent' => 'cf-shortcuts-view-sensei-lms',
				'id'     => 'cf-shortcuts-view-sensei-lms-message-questions',
				'href'   => \get_site_url( null, '/berichten/ik-heb-een-paar-vragen-over-de-roos/' ),
				'meta'   => array(
					'title' => \__( 'Link to Sensei LMS', 'core-functionality-dwp' )
							. ' '
							. \__( 'Message: I have some questions', 'core-functionality-dwp' ),
				),
			),
		'visibility' => 'both',
		'dependency' => array(
			'plugin' => 'Sensei with WooCommerce Paid Courses',
		),
	),
);
