<?php
/**
 * Registers topic and region taxonomies for report filters.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Discovery_Taxonomies {

	/**
	 * Related post type slug.
	 */
	const REPORT_POST_TYPE = Discovery_Report_Type::POST_TYPE;

	/**
	 * Topic taxonomy slug.
	 */
	const TOPIC_TAXONOMY = 'discovery_topic';

	/**
	 * Region taxonomy slug.
	 */
	const REGION_TAXONOMY = 'discovery_region';

	/**
	 * Register hooks for this module.
	 */
	public static function register_hooks() {
		add_action( 'init', array( __CLASS__, 'register_taxonomies' ) );
	}

	/**
	 * Register both filter taxonomies for reports.
	 */
	public static function register_taxonomies() {
		$topic_labels = array(
			'name'                       => __( 'Topics', 'demo-faceted-discovery' ),
			'singular_name'              => __( 'Topic', 'demo-faceted-discovery' ),
			'search_items'               => __( 'Search Topics', 'demo-faceted-discovery' ),
			'popular_items'              => __( 'Popular Topics', 'demo-faceted-discovery' ),
			'all_items'                  => __( 'All Topics', 'demo-faceted-discovery' ),
			'edit_item'                  => __( 'Edit Topic', 'demo-faceted-discovery' ),
			'update_item'                => __( 'Update Topic', 'demo-faceted-discovery' ),
			'add_new_item'               => __( 'Add New Topic', 'demo-faceted-discovery' ),
			'new_item_name'              => __( 'New Topic Name', 'demo-faceted-discovery' ),
			'separate_items_with_commas' => __( 'Separate topics with commas', 'demo-faceted-discovery' ),
			'add_or_remove_items'        => __( 'Add or remove topics', 'demo-faceted-discovery' ),
			'choose_from_most_used'      => __( 'Choose from the most used topics', 'demo-faceted-discovery' ),
		);

		register_taxonomy(
			self::TOPIC_TAXONOMY,
			array( self::REPORT_POST_TYPE ),
			array(
				'labels'            => $topic_labels,
				'public'            => true,
				'hierarchical'      => false,
				'show_admin_column' => true,
				'show_in_rest'      => true,
				'rewrite'           => array( 'slug' => 'topic' ),
			)
		);

		$region_labels = array(
			'name'              => __( 'Regions', 'demo-faceted-discovery' ),
			'singular_name'     => __( 'Region', 'demo-faceted-discovery' ),
			'search_items'      => __( 'Search Regions', 'demo-faceted-discovery' ),
			'all_items'         => __( 'All Regions', 'demo-faceted-discovery' ),
			'parent_item'       => __( 'Parent Region', 'demo-faceted-discovery' ),
			'parent_item_colon' => __( 'Parent Region:', 'demo-faceted-discovery' ),
			'edit_item'         => __( 'Edit Region', 'demo-faceted-discovery' ),
			'update_item'       => __( 'Update Region', 'demo-faceted-discovery' ),
			'add_new_item'      => __( 'Add New Region', 'demo-faceted-discovery' ),
			'new_item_name'     => __( 'New Region Name', 'demo-faceted-discovery' ),
			'menu_name'         => __( 'Regions', 'demo-faceted-discovery' ),
		);

		register_taxonomy(
			self::REGION_TAXONOMY,
			array( self::REPORT_POST_TYPE ),
			array(
				'labels'            => $region_labels,
				'public'            => true,
				'hierarchical'      => true,
				'show_admin_column' => true,
				'show_in_rest'      => true,
				'rewrite'           => array( 'slug' => 'region' ),
			)
		);
	}
}
