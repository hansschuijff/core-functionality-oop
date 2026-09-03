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
	// Posts.
	array(
		'node_args'  =>
			array(
				'title'  => \__( 'Posts', 'core-functionality-dwp' ),
				'parent' => 'site-name',
				'id'     => 'cf-shortcuts-posts',
				'href'   => \get_permalink( \get_option( 'page_for_posts' ) )
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
	// Posts -> View Post.
	array(
		'node_args'  =>
			array(
				'title'  => \__( 'View', 'core-functionality-dwp' ),
				'parent' => 'cf-shortcuts-posts',
				'id'     => 'cf-shortcuts-posts-view-posts',
				'href'   => \get_permalink( \get_option( 'page_for_posts' ) )
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
	// Posts -> Edit Posts.
	array(
		'node_args'  =>
			array(
				'title'  => \__( 'Edit', 'core-functionality-dwp' ),
				'parent' => 'cf-shortcuts-posts',
				'id'     => 'cf-shortcuts-posts-edit-posts',
				'href'   => \admin_url( 'edit.php', 'admin' ),
				'meta'   => array(
					'title' => \__( 'Link to', 'core-functionality-dwp' )
							. ' '
							. \__( 'Posts', 'core-functionality-dwp' ),
				),
			),
		'visibility' => 'both',
	),
	// Posts -> Edit Posts.
	array(
		'node_args'  =>
			array(
				'title'  => \__( 'New Post', 'core-functionality-dwp' ),
				'parent' => 'cf-shortcuts-posts',
				'id'     => 'cf-shortcuts-posts-new-post',
				'href'   => \admin_url( 'post-new.php', 'admin' ),
				'meta'   => array(
					'title' => \__( 'Link to', 'core-functionality-dwp' )
							. ' '
							. \__( 'Add New Post', 'core-functionality-dwp' ),
				),
			),
		'visibility' => 'both',
	),
	// Posts -> Post categories.
	array(
		'node_args'  =>
			array(
				'title'  => \__( 'Categories', 'core-functionality-dwp' ),
				'parent' => 'cf-shortcuts-posts',
				'id'     => 'cf-shortcuts-posts-categories',
				'href'   => \admin_url( 'edit-tags.php?taxonomy=category', 'admin' ),
				'meta'   => array(
					'title' => \__( 'Link to', 'core-functionality-dwp' )
							. ' '
							. \__( 'Post categories', 'core-functionality-dwp' ),
				),
			),
		'visibility' => 'both',
	),
	// Posts -> Post tags.
	array(
		'node_args'  =>
			array(
				'title'  => \__( 'Tags', 'core-functionality-dwp' ),
				'parent' => 'cf-shortcuts-posts',
				'id'     => 'cf-shortcuts-posts-tags',
				'href'   => \admin_url( 'edit-tags.php?taxonomy=post_tag', 'admin' ),
				'meta'   => array(
					'title' => \__( 'Link to', 'core-functionality-dwp' )
							. ' '
							. \__( 'Post tags', 'core-functionality-dwp' ),
				),
			),
		'visibility' => 'both',
	),
);
