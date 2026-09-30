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
				title:   __( 'Courses', 'dwp-cf' ),
				href:    admin_url( 'edit.php?post_type=course' ),
				label:   __( 'Link to', 'dwp-cf' ) . ' ' . __( 'Sensei LMS Courses', 'dwp-cf' ),
				orientation: Orientation::ADMIN,
			),
			new ToolbarShortcut(
				id:      'cf-admin-courses-grading',
				title:   __( 'Grading', 'dwp-cf' ),
				href:    admin_url( 'admin.php?page=sensei_grading' ),
				label:   __( 'Link to', 'dwp-cf' ) . ' ' . __( 'Sensei LMS Grading', 'dwp-cf' ),
				orientation: Orientation::ADMIN,
			),
			new ToolbarShortcut(
				id:      'cf-admin-courses-student-management',
				title:   __( 'Student Management', 'dwp-cf' ),
				href:    admin_url( 'admin.php?page=sensei_learners' ),
				label:   __( 'Link to', 'dwp-cf' ) . ' ' . __( 'Sensei LMS Student Management', 'dwp-cf' ),
				orientation: Orientation::ADMIN,
			),
			new ToolbarShortcut(
				id:      'cf-admin-courses-edit-courses',
				title:   __( 'Edit Courses', 'dwp-cf' ),
				href:    admin_url( 'edit.php?post_type=course' ),
				label:   __( 'Link to', 'dwp-cf' ) . ' ' . __( 'Sensei LMS Courses', 'dwp-cf' ),
				orientation: Orientation::ADMIN,
			),
			new ToolbarShortcut(
				id:      'cf-admin-courses-lessons',
				title:   __( 'Lessons', 'dwp-cf' ),
				href:    admin_url( 'edit.php?post_type=lesson' ),
				label:   __( 'Link to', 'dwp-cf' ) . ' ' . __( 'Sensei LMS Lessons', 'dwp-cf' ),
				orientation: Orientation::ADMIN,
			),
			new ToolbarShortcut(
				id:      'cf-admin-courses-categories',
				title:   __( 'Categories', 'dwp-cf' ),
				href:    admin_url( 'edit-tags.php?taxonomy=course-category&post_type=course' ),
				label:   __( 'Link to', 'dwp-cf' ) . ' ' . __( 'Sensei LMS Course Categories', 'dwp-cf' ),
				orientation: Orientation::ADMIN,
			),
			new ToolbarShortcut(
				id:      'cf-admin-courses-modules',
				title:   __( 'Modules', 'dwp-cf' ),
				href:    admin_url( 'edit-tags.php?taxonomy=module' ),
				label:   __( 'Link to', 'dwp-cf' ) . ' ' . __( 'Sensei LMS Modules', 'dwp-cf' ),
				orientation: Orientation::ADMIN,
			),
			new ToolbarShortcut(
				id:      'cf-admin-courses-module-order',
				title:   __( 'Module Order', 'dwp-cf' ),
				href:    admin_url( 'edit.php?post_type=course&page=module-order' ),
				label:   __( 'Link to', 'dwp-cf' ) . ' ' . __( 'Sensei LMS Module Order', 'dwp-cf' ),
				orientation: Orientation::ADMIN,
			),
			new ToolbarShortcut(
				id:      'cf-admin-courses-lesson-tags',
				title:   __( 'Lesson Tags', 'dwp-cf' ),
				href:    admin_url( 'edit-tags.php?taxonomy=lesson-tag&post_type=lesson' ),
				label:   __( 'Link to', 'dwp-cf' ) . ' ' . __( 'Sensei LMS Lesson Tags', 'dwp-cf' ),
				orientation: Orientation::ADMIN,
			),
			new ToolbarShortcut(
				id:      'cf-admin-courses-questions',
				title:   __( 'Questions', 'dwp-cf' ),
				href:    admin_url( 'edit.php?post_type=question' ),
				label:   __( 'Link to', 'dwp-cf' ) . ' ' . __( 'Sensei LMS Questions', 'dwp-cf' ),
				orientation: Orientation::ADMIN,
			),
			new ToolbarShortcut(
				id:      'cf-admin-courses-messages',
				title:   __( 'Messages', 'dwp-cf' ),
				href:    admin_url( 'edit.php?post_type=sensei_message' ),
				label:   __( 'Link to', 'dwp-cf' ) . ' ' . __( 'Sensei LMS Messages', 'dwp-cf' ),
				orientation: Orientation::ADMIN,
			),
			new ToolbarShortcut(
				id:      'cf-admin-courses-sensei-settings',
				title:   __( 'Settings', 'dwp-cf' ),
				href:    admin_url( 'admin.php?page=sensei-settings' ),
				label:   __( 'Link to', 'dwp-cf' ) . ' ' . __( 'Sensei LMS Settings', 'dwp-cf' ),
				orientation: Orientation::ADMIN,
			),
			new ToolbarShortcut(
				id:      'cf-admin-courses-analytics',
				title:   __( 'Analytics', 'dwp-cf' ),
				href:    admin_url( 'admin.php?page=sensei_analysis' ),
				label:   __( 'Link to', 'dwp-cf' ) . ' ' . __( 'Sensei LMS Analytics', 'dwp-cf' ),
				orientation: Orientation::ADMIN,
			),
			new ToolbarShortcut(
				id:      'cf-courses',
				title:   __( 'View', 'dwp-cf' ),
				href:    home_url( '/cursussen/' ),
				label:   __( 'Link to', 'dwp-cf' ) . ' ' . __( 'Sensei LMS Courses', 'dwp-cf' ),
				orientation: Orientation::FRONTEND,
			),
			new ToolbarShortcut(
				id:      'cf-view-sensei-lms',
				title:   __( 'Courses', 'dwp-cf' ),
				href:    home_url( '/cursussen/' ),
				label:   __( 'Link to', 'dwp-cf' ) . ' ' . __( 'Sensei LMS Courses', 'dwp-cf' ),
				orientation: Orientation::FRONTEND,
			),
			new ToolbarShortcut(
				id:      'cf-view-sensei-lms-courses-list',
				title:   __( 'Course List', 'dwp-cf' ),
				href:    home_url( '/cursusoverzicht/' ),
				label:   __( 'Link to', 'dwp-cf' ) . ' ' . __( 'Sensei LMS Course List', 'dwp-cf' ),
				orientation: Orientation::FRONTEND,
			),
			new ToolbarShortcut(
				id:      'cf-view-sensei-lms-category-mediumschap',
				title:   __( 'Course-category: Mediumschap', 'dwp-cf' ),
				href:    home_url( '/cursuscategorie/mediumschap/' ),
				label:   __( 'Link to', 'dwp-cf' ) . ' ' . __( 'Sensei LMS Course-category: Mediumschap', 'dwp-cf' ),
				orientation: Orientation::FRONTEND,
			),
			new ToolbarShortcut(
				id:      'cf-view-sensei-lms-my-courses',
				title:   __( 'My Courses', 'dwp-cf' ),
				href:    home_url( '/mijn-cursussen/' ),
				label:   __( 'Link to', 'dwp-cf' ) . ' ' . __( 'Sensei LMS My Courses', 'dwp-cf' ),
				orientation: Orientation::FRONTEND,
			),
			new ToolbarShortcut(
				id:      'cf-view-sensei-lms-learners-profile',
				title:   __( 'Learners Profile', 'dwp-cf' ),
				href:    home_url( '/student/hansepans/' ),
				label:   __( 'Link to', 'dwp-cf' ) . ' ' . __( 'Sensei LMS Learners Profile', 'dwp-cf' ),
				orientation: Orientation::FRONTEND,
			),
			new ToolbarShortcut(
				id:      'cf-shortcuts-view-sensei-lms-basis-mediumschap',
				title:   __( 'Course: Basis Mediumschap', 'dwp-cf' ),
				href:    home_url( '/cursus/mediumschap-basis/' ),
				label:   __( 'Link to', 'dwp-cf' ) . ' ' . __( 'Sensei LMS Course: Basis Mediumschap', 'dwp-cf' ),
				orientation: Orientation::FRONTEND,
			),
			new ToolbarShortcut(
				id:      'cf-view-sensei-lms-basis-mediumschap-results',
				title:   __( 'Course results', 'dwp-cf' ),
				href:    home_url( '/cursus/mediumschap-basis/results/' ),
				label:   __( 'Link to', 'dwp-cf' ) . ' ' . __( 'Sensei LMS Course results', 'dwp-cf' ),
				orientation: Orientation::FRONTEND,
			),
			new ToolbarShortcut(
				id:      'cf-view-sensei-lms-module-orakelen',
				title:   __( 'Module: Orakelen als een orakel', 'dwp-cf' ),
				href:    home_url( '/modules/orakelen-als-een-orakel/?course_id=6732' ),
				label:   __( 'Link to', 'dwp-cf' ) . ' ' . __( 'Sensei LMS Module: Orakelen als een orakel', 'dwp-cf' ),
				orientation: Orientation::FRONTEND,
			),
			new ToolbarShortcut(
				id:      'cf-shortcuts-view-sensei-lms-lessons',
				title:   __( 'Lessons', 'dwp-cf' ),
				href:    home_url( '/les/' ),
				label:   __( 'Link to', 'dwp-cf' ) . ' ' . __( 'Sensei LMS Lessons', 'dwp-cf' ),
				orientation: Orientation::FRONTEND,
			),
			new ToolbarShortcut(
				id:      'cf-shortcuts-view-sensei-lms-lesson-tag-aura',
				title:   __( 'Lesson-tag: Aura', 'dwp-cf' ),
				href:    home_url( '/lestag/aura/' ),
				label:   __( 'Link to', 'dwp-cf' ) . ' ' . __( 'Sensei LMS Lesson-tag: Aura', 'dwp-cf' ),
				orientation: Orientation::FRONTEND,
			),
			new ToolbarShortcut(
				id:      'cf-shortcuts-view-sensei-lms-lesson-aura-reading',
				title:   __( 'Lesson: Aura Reading', 'dwp-cf' ),
				href:    home_url( '/les/auras-waarnemen-en-lezen/' ),
				label:   __( 'Link to', 'dwp-cf' ) . ' ' . __( 'Sensei LMS Lesson: Aura Reading', 'dwp-cf' ),
				orientation: Orientation::FRONTEND,
			),
			new ToolbarShortcut(
				id:      'cf-shortcuts-view-sensei-lms-quiz-my-psychic-ability',
				title:   __( 'Quiz: The History of My Psychic Ability', 'dwp-cf' ),
				href:    home_url( '/test/hoe-ik-mijn-mediumschap-ontwikkelde/' ),
				label:   __( 'Link to', 'dwp-cf' ) . ' ' . __( 'Sensei LMS Quiz: The History of My Psychic Ability', 'dwp-cf' ),
				orientation: Orientation::FRONTEND,
			),
			new ToolbarShortcut(
				id:      'cf-shortcuts-view-sensei-lms-messages',
				title:   __( 'Messages', 'dwp-cf' ),
				href:    home_url( '/berichten/' ),
				label:   __( 'Link to', 'dwp-cf' ) . ' ' . __( 'Sensei LMS Messages', 'dwp-cf' ),
				orientation: Orientation::FRONTEND,
			),
			new ToolbarShortcut(
				id:      'cf-shortcuts-view-sensei-lms-message-questions',
				title:   __( 'Message: I have some questions', 'dwp-cf' ),
				href:    home_url( '/berichten/ik-heb-een-paar-vragen-over-de-roos/' ),
				label:   __( 'Link to', 'dwp-cf' ) . ' ' . __( 'Sensei LMS Message: I have some questions', 'dwp-cf' ),
				orientation: Orientation::FRONTEND,
			),
		);
	}

	#[Override]
	protected function get_menu_nodes(): array { return array(); }
}
