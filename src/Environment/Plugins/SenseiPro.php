<?php
namespace DeWittePrins\CoreFunctionality\Environment\Plugins;

use DeWittePrins\CoreFunctionality\Enums\Orientation;
use DeWittePrins\CoreFunctionality\DTO\ToolbarShortcut;
use DeWittePrins\CoreFunctionality\Environment\DTO\ProofRequirement;
use Override;

use function __;
use function admin_url;
use function home_url;

class SenseiLms extends AbstractPlugin {

	#[Override]
	public function get_id(): string {
		return 'sensei-lms';
	}

	#[Override]
	protected function get_proof_requirements(): ProofRequirement {
		return new ProofRequirement( classes: 'Sensei_Main' );
	}

	#[Override]
	protected function get_toolbar_nodes(): array {
		return array(
			new ToolbarShortcut(
				id:      'cf-admin-courses',
				title:   __( 'Courses', 'core-functionality' ),
				href:    admin_url( 'edit.php?post_type=course' ),
				label:   __( 'Link to', 'core-functionality' ) . ' ' . __( 'Sensei LMS Courses', 'core-functionality' ),
				orientation: Orientation::ADMIN,
			),
			new ToolbarShortcut(
				id:      'cf-admin-courses-grading',
				title:   __( 'Grading', 'core-functionality' ),
				href:    admin_url( 'admin.php?page=sensei_grading' ),
				label:   __( 'Link to', 'core-functionality' ) . ' ' . __( 'Sensei LMS Grading', 'core-functionality' ),
				orientation: Orientation::ADMIN,
			),
			new ToolbarShortcut(
				id:      'cf-admin-courses-student-management',
				title:   __( 'Student Management', 'core-functionality' ),
				href:    admin_url( 'admin.php?page=sensei_learners' ),
				label:   __( 'Link to', 'core-functionality' ) . ' ' . __( 'Sensei LMS Student Management', 'core-functionality' ),
				orientation: Orientation::ADMIN,
			),
			new ToolbarShortcut(
				id:      'cf-admin-courses-edit-courses',
				title:   __( 'Edit Courses', 'core-functionality' ),
				href:    admin_url( 'edit.php?post_type=course' ),
				label:   __( 'Link to', 'core-functionality' ) . ' ' . __( 'Sensei LMS Courses', 'core-functionality' ),
				orientation: Orientation::ADMIN,
			),
			new ToolbarShortcut(
				id:      'cf-admin-courses-lessons',
				title:   __( 'Lessons', 'core-functionality' ),
				href:    admin_url( 'edit.php?post_type=lesson' ),
				label:   __( 'Link to', 'core-functionality' ) . ' ' . __( 'Sensei LMS Lessons', 'core-functionality' ),
				orientation: Orientation::ADMIN,
			),
			new ToolbarShortcut(
				id:      'cf-admin-courses-categories',
				title:   __( 'Categories', 'core-functionality' ),
				href:    admin_url( 'edit-tags.php?taxonomy=course-category&post_type=course' ),
				label:   __( 'Link to', 'core-functionality' ) . ' ' . __( 'Sensei LMS Course Categories', 'core-functionality' ),
				orientation: Orientation::ADMIN,
			),
			new ToolbarShortcut(
				id:      'cf-admin-courses-modules',
				title:   __( 'Modules', 'core-functionality' ),
				href:    admin_url( 'edit-tags.php?taxonomy=module' ),
				label:   __( 'Link to', 'core-functionality' ) . ' ' . __( 'Sensei LMS Modules', 'core-functionality' ),
				orientation: Orientation::ADMIN,
			),
			new ToolbarShortcut(
				id:      'cf-admin-courses-module-order',
				title:   __( 'Module Order', 'core-functionality' ),
				href:    admin_url( 'edit.php?post_type=course&page=module-order' ),
				label:   __( 'Link to', 'core-functionality' ) . ' ' . __( 'Sensei LMS Module Order', 'core-functionality' ),
				orientation: Orientation::ADMIN,
			),
			new ToolbarShortcut(
				id:      'cf-admin-courses-lesson-tags',
				title:   __( 'Lesson Tags', 'core-functionality' ),
				href:    admin_url( 'edit-tags.php?taxonomy=lesson-tag&post_type=lesson' ),
				label:   __( 'Link to', 'core-functionality' ) . ' ' . __( 'Sensei LMS Lesson Tags', 'core-functionality' ),
				orientation: Orientation::ADMIN,
			),
			new ToolbarShortcut(
				id:      'cf-admin-courses-questions',
				title:   __( 'Questions', 'core-functionality' ),
				href:    admin_url( 'edit.php?post_type=question' ),
				label:   __( 'Link to', 'core-functionality' ) . ' ' . __( 'Sensei LMS Questions', 'core-functionality' ),
				orientation: Orientation::ADMIN,
			),
			new ToolbarShortcut(
				id:      'cf-admin-courses-messages',
				title:   __( 'Messages', 'core-functionality' ),
				href:    admin_url( 'edit.php?post_type=sensei_message' ),
				label:   __( 'Link to', 'core-functionality' ) . ' ' . __( 'Sensei LMS Messages', 'core-functionality' ),
				orientation: Orientation::ADMIN,
			),
			new ToolbarShortcut(
				id:      'cf-admin-courses-sensei-settings',
				title:   __( 'Settings', 'core-functionality' ),
				href:    admin_url( 'admin.php?page=sensei-settings' ),
				label:   __( 'Link to', 'core-functionality' ) . ' ' . __( 'Sensei LMS Settings', 'core-functionality' ),
				orientation: Orientation::ADMIN,
			),
			new ToolbarShortcut(
				id:      'cf-admin-courses-analytics',
				title:   __( 'Analytics', 'core-functionality' ),
				href:    admin_url( 'admin.php?page=sensei_analysis' ),
				label:   __( 'Link to', 'core-functionality' ) . ' ' . __( 'Sensei LMS Analytics', 'core-functionality' ),
				orientation: Orientation::ADMIN,
			),
			new ToolbarShortcut(
				id:      'cf-woocommerce-my-account-sensei-courses',
 				title:   __( 'My Account: Courses', 'core-functionality' ),
				href:    fn() => function_exists( 'wc_get_page_permalink' ) ? wc_get_endpoint_url( 'courses_endpoint', '', wc_get_page_permalink( 'myaccount' ) ) : home_url( __( '/myaccount/courses_endpoint/', 'core-functionality' ) ),
				label:   __( 'Link to', 'core-functionality' ) . ' ' . __( 'My Account: Courses', 'core-functionality' ),
				orientation: Orientation::FRONTEND,
			),
			new ToolbarShortcut(
				id:      'cf-courses',
				title:   __( 'View', 'core-functionality' ),
				href:    home_url( '/cursussen/' ),
				label:   __( 'Link to', 'core-functionality' ) . ' ' . __( 'Sensei LMS Courses', 'core-functionality' ),
				orientation: Orientation::FRONTEND,
			),
			new ToolbarShortcut(
				id:      'cf-view-sensei-lms',
				title:   __( 'Courses', 'core-functionality' ),
				href:    home_url( '/cursussen/' ),
				label:   __( 'Link to', 'core-functionality' ) . ' ' . __( 'Sensei LMS Courses', 'core-functionality' ),
				orientation: Orientation::FRONTEND,
			),
			new ToolbarShortcut(
				id:      'cf-view-sensei-lms-courses-list',
				title:   __( 'Course List', 'core-functionality' ),
				href:    home_url( '/cursusoverzicht/' ),
				label:   __( 'Link to', 'core-functionality' ) . ' ' . __( 'Sensei LMS Course List', 'core-functionality' ),
				orientation: Orientation::FRONTEND,
			),
			new ToolbarShortcut(
				id:      'cf-view-sensei-lms-category-mediumschap',
				title:   __( 'Course-category: Mediumschap', 'core-functionality' ),
				href:    home_url( '/cursuscategorie/mediumschap/' ),
				label:   __( 'Link to', 'core-functionality' ) . ' ' . __( 'Sensei LMS Course-category: Mediumschap', 'core-functionality' ),
				orientation: Orientation::FRONTEND,
			),
			new ToolbarShortcut(
				id:      'cf-view-sensei-lms-my-courses',
				title:   __( 'My Courses', 'core-functionality' ),
				href:    home_url( '/mijn-cursussen/' ),
				label:   __( 'Link to', 'core-functionality' ) . ' ' . __( 'Sensei LMS My Courses', 'core-functionality' ),
				orientation: Orientation::FRONTEND,
			),
			new ToolbarShortcut(
				id:      'cf-view-sensei-lms-learners-profile',
				title:   __( 'Learners Profile', 'core-functionality' ),
				href:    home_url( '/student/hansepans/' ),
				label:   __( 'Link to', 'core-functionality' ) . ' ' . __( 'Sensei LMS Learners Profile', 'core-functionality' ),
				orientation: Orientation::FRONTEND,
			),
			new ToolbarShortcut(
				id:      'cf-shortcuts-view-sensei-lms-basis-mediumschap',
				title:   __( 'Course: Basis Mediumschap', 'core-functionality' ),
				href:    home_url( '/cursus/mediumschap-basis/' ),
				label:   __( 'Link to', 'core-functionality' ) . ' ' . __( 'Sensei LMS Course: Basis Mediumschap', 'core-functionality' ),
				orientation: Orientation::FRONTEND,
			),
			new ToolbarShortcut(
				id:      'cf-view-sensei-lms-basis-mediumschap-results',
				title:   __( 'Course results', 'core-functionality' ),
				href:    home_url( '/cursus/mediumschap-basis/results/' ),
				label:   __( 'Link to', 'core-functionality' ) . ' ' . __( 'Sensei LMS Course results', 'core-functionality' ),
				orientation: Orientation::FRONTEND,
			),
			new ToolbarShortcut(
				id:      'cf-view-sensei-lms-module-orakelen',
				title:   __( 'Module: Orakelen als een orakel', 'core-functionality' ),
				href:    home_url( '/modules/orakelen-als-een-orakel/?course_id=6732' ),
				label:   __( 'Link to', 'core-functionality' ) . ' ' . __( 'Sensei LMS Module: Orakelen als een orakel', 'core-functionality' ),
				orientation: Orientation::FRONTEND,
			),
			new ToolbarShortcut(
				id:      'cf-shortcuts-view-sensei-lms-lessons',
				title:   __( 'Lessons', 'core-functionality' ),
				href:    home_url( '/les/' ),
				label:   __( 'Link to', 'core-functionality' ) . ' ' . __( 'Sensei LMS Lessons', 'core-functionality' ),
				orientation: Orientation::FRONTEND,
			),
			new ToolbarShortcut(
				id:      'cf-shortcuts-view-sensei-lms-lesson-tag-aura',
				title:   __( 'Lesson-tag: Aura', 'core-functionality' ),
				href:    home_url( '/lestag/aura/' ),
				label:   __( 'Link to', 'core-functionality' ) . ' ' . __( 'Sensei LMS Lesson-tag: Aura', 'core-functionality' ),
				orientation: Orientation::FRONTEND,
			),
			new ToolbarShortcut(
				id:      'cf-shortcuts-view-sensei-lms-lesson-aura-reading',
				title:   __( 'Lesson: Aura Reading', 'core-functionality' ),
				href:    home_url( '/les/auras-waarnemen-en-lezen/' ),
				label:   __( 'Link to', 'core-functionality' ) . ' ' . __( 'Sensei LMS Lesson: Aura Reading', 'core-functionality' ),
				orientation: Orientation::FRONTEND,
			),
			new ToolbarShortcut(
				id:      'cf-shortcuts-view-sensei-lms-quiz-my-psychic-ability',
				title:   __( 'Quiz: The History of My Psychic Ability', 'core-functionality' ),
				href:    home_url( '/test/hoe-ik-mijn-mediumschap-ontwikkelde/' ),
				label:   __( 'Link to', 'core-functionality' ) . ' ' . __( 'Sensei LMS Quiz: The History of My Psychic Ability', 'core-functionality' ),
				orientation: Orientation::FRONTEND,
			),
			new ToolbarShortcut(
				id:      'cf-shortcuts-view-sensei-lms-messages',
				title:   __( 'Messages', 'core-functionality' ),
				href:    home_url( '/berichten/' ),
				label:   __( 'Link to', 'core-functionality' ) . ' ' . __( 'Sensei LMS Messages', 'core-functionality' ),
				orientation: Orientation::FRONTEND,
			),
			new ToolbarShortcut(
				id:      'cf-shortcuts-view-sensei-lms-message-questions',
				title:   __( 'Message: I have some questions', 'core-functionality' ),
				href:    home_url( '/berichten/ik-heb-een-paar-vragen-over-de-roos/' ),
				label:   __( 'Link to', 'core-functionality' ) . ' ' . __( 'Sensei LMS Message: I have some questions', 'core-functionality' ),
				orientation: Orientation::FRONTEND,
			),
		);
	}

	#[Override]
	protected function get_menu_nodes(): array { return array(); }
}
