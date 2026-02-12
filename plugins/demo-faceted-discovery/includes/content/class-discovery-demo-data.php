<?php
/**
 * Seeds minimal demo content for quick showcases.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Discovery_Demo_Data {

	/**
	 * Option used to prevent reseeding on repeated activations.
	 */
	const SEEDED_OPTION = 'discovery_demo_data_seeded';

	/**
	 * Create demo terms and reports when the site has no report content yet.
	 */
	public static function seed_on_activation() {
		if ( get_option( self::SEEDED_OPTION, false ) ) {
			return;
		}

		if ( self::has_existing_reports() ) {
			update_option( self::SEEDED_OPTION, 1, false );
			return;
		}

		self::ensure_term( Discovery_Taxonomies::TOPIC_TAXONOMY, 'Health', 'health' );
		self::ensure_term( Discovery_Taxonomies::TOPIC_TAXONOMY, 'Housing', 'housing' );
		self::ensure_term( Discovery_Taxonomies::REGION_TAXONOMY, 'North America', 'north-america' );
		self::ensure_term( Discovery_Taxonomies::REGION_TAXONOMY, 'Europe', 'europe' );

		self::create_demo_report(
			'Community Health Access Snapshot',
			'An overview of primary care access trends across major metro areas.',
			'This demo report summarizes community-level access to clinics, preventive care participation, and appointment wait times.',
			'health',
			'north-america'
		);

		self::create_demo_report(
			'Affordable Housing Supply Outlook',
			'A quick look at rental pressure and affordable housing pipeline progress.',
			'This demo report highlights permit activity, rental growth, and affordable inventory constraints across selected cities.',
			'housing',
			'north-america'
		);

		self::create_demo_report(
			'Regional Health Equity Brief',
			'Comparison of preventive care outcomes by region and population segment.',
			'This demo report compares public health indicators, access patterns, and outreach program effectiveness across regions.',
			'health',
			'europe'
		);

		update_option( self::SEEDED_OPTION, 1, false );
	}

	/**
	 * Check if any report records already exist.
	 *
	 * @return bool
	 */
	private static function has_existing_reports() {
		$existing_reports = get_posts(
			array(
				'post_type'      => Discovery_Report_Type::POST_TYPE,
				'post_status'    => 'any',
				'posts_per_page' => 1,
				'fields'         => 'ids',
				'no_found_rows'  => true,
			)
		);

		return ! empty( $existing_reports );
	}

	/**
	 * Ensure a taxonomy term exists by slug.
	 *
	 * @param string $taxonomy Taxonomy slug.
	 * @param string $name     Human readable term name.
	 * @param string $slug     URL-safe term slug.
	 */
	private static function ensure_term( $taxonomy, $name, $slug ) {
		$existing_term = get_term_by( 'slug', $slug, $taxonomy );
		if ( $existing_term && ! is_wp_error( $existing_term ) ) {
			return;
		}

		wp_insert_term(
			$name,
			$taxonomy,
			array(
				'slug' => $slug,
			)
		);
	}

	/**
	 * Insert one report and connect it to topic + region terms.
	 *
	 * @param string $title       Report title.
	 * @param string $excerpt     Short excerpt.
	 * @param string $content     Full content.
	 * @param string $topic_slug  Topic term slug.
	 * @param string $region_slug Region term slug.
	 */
	private static function create_demo_report( $title, $excerpt, $content, $topic_slug, $region_slug ) {
		$post_slug      = sanitize_title( $title );
		$existing_post  = get_page_by_path( $post_slug, OBJECT, Discovery_Report_Type::POST_TYPE );

		if ( $existing_post instanceof WP_Post ) {
			return;
		}

		$report_id = wp_insert_post(
			array(
				'post_type'    => Discovery_Report_Type::POST_TYPE,
				'post_status'  => 'publish',
				'post_title'   => $title,
				'post_name'    => $post_slug,
				'post_excerpt' => $excerpt,
				'post_content' => $content,
			),
			true
		);

		if ( is_wp_error( $report_id ) || $report_id <= 0 ) {
			return;
		}

		wp_set_object_terms( $report_id, array( $topic_slug ), Discovery_Taxonomies::TOPIC_TAXONOMY, false );
		wp_set_object_terms( $report_id, array( $region_slug ), Discovery_Taxonomies::REGION_TAXONOMY, false );
	}
}
