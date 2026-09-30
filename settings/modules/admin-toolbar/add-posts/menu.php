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

use DeWittePrins\Corefunctionality\Modules\AdminToolbar\AdminToolbar;

return array(
	// Posts.
	array(
		'node_args'  =>
			array(
				'title'  => \__( 'Posts', 'dwp-cf' ),
				'parent' => 'site-name',
				'id'     => 'cf-shortcuts-posts',
				'href'   => \get_permalink( $this->get( 'page_for_posts', AdminToolbar::get_id() ) )
							? \get_permalink( $this->get( 'page_for_posts', AdminToolbar::get_id() ) )
							: \get_site_url( null, '/artikelen/' ),
				'meta'   => array(
					'title' => \__( 'Link to', 'dwp-cf' )
							. ' '
							. \__( 'the posts', 'dwp-cf' ),
				),
			),
		'visibility' => 'both',
	),
	// Posts -> View Post.
	array(
		'node_args'  =>
			array(
				'title'  => \__( 'View', 'dwp-cf' ),
				'parent' => 'cf-shortcuts-posts',
				'id'     => 'cf-shortcuts-posts-view-posts',
				'href'   => \get_permalink( $this->get( 'page_for_posts', AdminToolbar::get_id() ) )
							? \get_permalink( $this->get( 'page_for_posts', AdminToolbar::get_id() ) )
							: \get_site_url( null, '/artikelen/' ),
				'meta'   => array(
					'title' => \__( 'Link to', 'dwp-cf' )
							. ' '
							. \__( 'the posts', 'dwp-cf' ),
				),
			),
		'visibility' => 'both',
	),
	// Posts -> Edit Posts.
	array(
		'node_args'  =>
			array(
				'title'  => \__( 'Edit', 'dwp-cf' ),
				'parent' => 'cf-shortcuts-posts',
				'id'     => 'cf-shortcuts-posts-edit-posts',
				'href'   => \admin_url( 'edit.php', 'admin' ),
				'meta'   => array(
					'title' => \__( 'Link to', 'dwp-cf' )
							. ' '
							. \__( 'Posts', 'dwp-cf' ),
				),
			),
		'visibility' => 'both',
	),
	// Posts -> Edit Posts.
	array(
		'node_args'  =>
			array(
				'title'  => \__( 'New Post', 'dwp-cf' ),
				'parent' => 'cf-shortcuts-posts',
				'id'     => 'cf-shortcuts-posts-new-post',
				'href'   => \admin_url( 'post-new.php', 'admin' ),
				'meta'   => array(
					'title' => \__( 'Link to', 'dwp-cf' )
							. ' '
							. \__( 'Add New Post', 'dwp-cf' ),
				),
			),
		'visibility' => 'both',
	),
	// Posts -> Post categories.
	array(
		'node_args'  =>
			array(
				'title'  => \__( 'Categories', 'dwp-cf' ),
				'parent' => 'cf-shortcuts-posts',
				'id'     => 'cf-shortcuts-posts-categories',
				'href'   => \admin_url( 'edit-tags.php?taxonomy=category', 'admin' ),
				'meta'   => array(
					'title' => \__( 'Link to', 'dwp-cf' )
							. ' '
							. \__( 'Post categories', 'dwp-cf' ),
				),
			),
		'visibility' => 'both',
	),
	// Posts -> Post tags.
	array(
		'node_args'  =>
			array(
				'title'  => \__( 'Tags', 'dwp-cf' ),
				'parent' => 'cf-shortcuts-posts',
				'id'     => 'cf-shortcuts-posts-tags',
				'href'   => \admin_url( 'edit-tags.php?taxonomy=post_tag', 'admin' ),
				'meta'   => array(
					'title' => \__( 'Link to', 'dwp-cf' )
							. ' '
							. \__( 'Post tags', 'dwp-cf' ),
				),
			),
		'visibility' => 'both',
	),
);
