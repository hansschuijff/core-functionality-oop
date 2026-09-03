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

use DeWittePrins\Corefunctionality\Modules\AdminToolbar\AdminToolbar;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	// View.
	array(
		'node_args'  =>
			array(
				'title'  => \__( 'View', 'core-functionality-dwp' ),
				'parent' => 'site-name',
				'id'     => 'cf-shortcuts-view',
				'href'   => '#',
			),
		'visibility' => 'both',
	),
	...$this->get_config( 'admin-toolbar-add-view-courses', AdminToolbar::get_id() ),
	...$this->get_config( 'admin-toolbar-add-view-shop', AdminToolbar::get_id() ),
	...$this->get_config( 'admin-toolbar-add-view-events', AdminToolbar::get_id() ),
	// View -> Posts.
	array(
		'node_args'  =>
			array(
				'title'  => \__( 'Posts', 'core-functionality-dwp' ),
				'parent' => 'cf-shortcuts-view',
				'id'     => 'cf-shortcuts-view-posts',
				'href'   =>
					\get_permalink( \get_option( 'page_for_posts' ) )
					? \get_permalink( \get_option( 'page_for_posts' ) )
					: \get_site_url( null, '/artikelen/' ),
				'meta'   => array(
					'title' => \__( 'Link to', 'core-functionality-dwp' )
							. ' '
							. \__( 'the posts', 'core-functionality-dwp' ),
				),
			),
		'visibility' => 'both',
	),
	// View -> Frontpage.
	array(
		'node_args'  =>
			array(
				'title'  => \__( 'Frontpage', 'core-functionality-dwp' ),
				'parent' => 'cf-shortcuts-view',
				'id'     => 'view-site',
				'href'   => \get_site_url( null, '' ),
				'meta'   => array(
					'title' => \__( 'Link to', 'core-functionality-dwp' )
							. ' '
							. \__( 'Frontpage', 'core-functionality-dwp' ),
				),
			),
		'visibility' => 'both',
	),
	// View -> Testimonials.
	array(
		'node_args'  =>
			array(
				'title'  => \__( 'Testimonials', 'core-functionality-dwp' ),
				'parent' => 'cf-shortcuts-view',
				'id'     => 'cf-shortcuts-view-testimonials',
				'href'   => \get_site_url( null, '/schrijf-een-aanbeveling/' ),
				'meta'   => array(
					'title' => \__( 'Link to', 'core-functionality-dwp' )
							. ' '
							. \__( 'Testimonials', 'core-functionality-dwp' ),
				),
			),
		'visibility' => 'both',
	),
);
