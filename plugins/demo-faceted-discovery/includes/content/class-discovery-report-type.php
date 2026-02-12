<?php
/**
 * Registers the custom post type for reports.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Discovery_Report_Type {

	/**
	 * Post type slug.
	 */
	const POST_TYPE = 'discovery_report';

	/**
	 * Register hooks for this module.
	 */
	public static function register_hooks() {
		add_action( 'init', array( __CLASS__, 'register_post_type' ) );
	}

	/**
	 * Register the post type with editor and REST support.
	 */
	public static function register_post_type() {
		$labels = array(
			'name'               => __( 'Reports', 'demo-faceted-discovery' ),
			'singular_name'      => __( 'Report', 'demo-faceted-discovery' ),
			'add_new'            => __( 'Add New', 'demo-faceted-discovery' ),
			'add_new_item'       => __( 'Add New Report', 'demo-faceted-discovery' ),
			'edit_item'          => __( 'Edit Report', 'demo-faceted-discovery' ),
			'new_item'           => __( 'New Report', 'demo-faceted-discovery' ),
			'view_item'          => __( 'View Report', 'demo-faceted-discovery' ),
			'search_items'       => __( 'Search Reports', 'demo-faceted-discovery' ),
			'not_found'          => __( 'No reports found.', 'demo-faceted-discovery' ),
			'not_found_in_trash' => __( 'No reports found in Trash.', 'demo-faceted-discovery' ),
			'all_items'          => __( 'Reports', 'demo-faceted-discovery' ),
		);

		$args = array(
			'labels'             => $labels,
			'public'             => true,
			'has_archive'        => true,
			'show_in_rest'       => true,
			'rewrite'            => array( 'slug' => 'reports' ),
			'supports'           => array( 'title', 'editor', 'excerpt', 'thumbnail', 'revisions' ),
			'show_in_nav_menus'  => true,
			'menu_position'      => 20,
			'menu_icon'          => 'dashicons-media-document',
		);

		register_post_type( self::POST_TYPE, $args );
	}
}
