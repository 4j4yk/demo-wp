<?php
/**
 * Exposes the public REST endpoint for report discovery.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Discovery_Reports_REST {

	/**
	 * REST namespace.
	 */
	const REST_NAMESPACE = 'discovery/v1';

	/**
	 * Route path.
	 */
	const REST_ROUTE = '/reports';

	/**
	 * Default page number when none is provided.
	 */
	const DEFAULT_PAGE = 1;

	/**
	 * Default page size when none is provided.
	 */
	const DEFAULT_PER_PAGE = 10;

	/**
	 * Hard upper bound for page size.
	 */
	const MAX_PER_PAGE = 50;

	/**
	 * Excerpt fallback length in words.
	 */
	const EXCERPT_WORD_LIMIT = 35;

	/**
	 * Register hooks for this module.
	 */
	public static function register_hooks() {
		add_action( 'rest_api_init', array( __CLASS__, 'register_routes' ) );
	}

	/**
	 * Register GET /wp-json/discovery/v1/reports.
	 */
	public static function register_routes() {
		register_rest_route(
			self::REST_NAMESPACE,
			self::REST_ROUTE,
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( __CLASS__, 'handle_get_reports_request' ),
				'permission_callback' => '__return_true',
				'args'                => array(
					'topic'    => array(
						'description'       => __( 'Topic term slug.', 'demo-faceted-discovery' ),
						'type'              => 'string',
						'sanitize_callback' => array( __CLASS__, 'sanitize_slug_param' ),
					),
					'region'   => array(
						'description'       => __( 'Region term slug.', 'demo-faceted-discovery' ),
						'type'              => 'string',
						'sanitize_callback' => array( __CLASS__, 'sanitize_slug_param' ),
					),
					'search'   => array(
						'description'       => __( 'Free-text search over report content.', 'demo-faceted-discovery' ),
						'type'              => 'string',
						'sanitize_callback' => 'sanitize_text_field',
					),
					'page'     => array(
						'description'       => __( 'Page number (1-based).', 'demo-faceted-discovery' ),
						'type'              => 'integer',
						'default'           => self::DEFAULT_PAGE,
						'sanitize_callback' => array( __CLASS__, 'sanitize_page_param' ),
					),
					'per_page' => array(
						'description'       => __( 'Items per page.', 'demo-faceted-discovery' ),
						'type'              => 'integer',
						'default'           => self::DEFAULT_PER_PAGE,
						'sanitize_callback' => array( __CLASS__, 'sanitize_per_page_param' ),
					),
				),
			)
		);
	}

	/**
	 * Handle GET reports requests.
	 *
	 * @param WP_REST_Request $request REST request.
	 * @return WP_REST_Response
	 */
	public static function handle_get_reports_request( $request ) {
		$request_filters = self::normalize_request_filters( $request );

		$reports_query = new WP_Query( self::build_reports_query_args( $request_filters ) );

		$report_items = array();
		foreach ( $reports_query->posts as $report_post ) {
			$report_items[] = self::format_report_for_response( $report_post );
		}

		$response_payload = array(
			'page'        => (int) $request_filters['page'],
			'per_page'    => (int) $request_filters['per_page'],
			'total'       => (int) $reports_query->found_posts,
			'total_pages' => (int) $reports_query->max_num_pages,
			'items'       => $report_items,
			'facets'      => array(
				// Topic counts keep region/search filters but ignore selected topic.
				'topics'  => self::build_contextual_facet_counts(
					Discovery_Taxonomies::TOPIC_TAXONOMY,
					array(
						'region' => $request_filters['region'],
						'search' => $request_filters['search'],
					)
				),
				// Region counts keep topic/search filters but ignore selected region.
				'regions' => self::build_contextual_facet_counts(
					Discovery_Taxonomies::REGION_TAXONOMY,
					array(
						'topic'  => $request_filters['topic'],
						'search' => $request_filters['search'],
					)
				),
			),
		);

		return rest_ensure_response( $response_payload );
	}

	/**
	 * Normalize and sanitize request filters.
	 *
	 * @param WP_REST_Request $request REST request.
	 * @return array
	 */
	private static function normalize_request_filters( $request ) {
		return array(
			'topic'    => self::sanitize_slug_param( $request->get_param( 'topic' ) ),
			'region'   => self::sanitize_slug_param( $request->get_param( 'region' ) ),
			'search'   => sanitize_text_field( (string) $request->get_param( 'search' ) ),
			'page'     => self::sanitize_page_param( $request->get_param( 'page' ) ),
			'per_page' => self::sanitize_per_page_param( $request->get_param( 'per_page' ) ),
		);
	}

	/**
	 * Build query arguments for the main report list.
	 *
	 * @param array $request_filters Sanitized request filters.
	 * @return array
	 */
	private static function build_reports_query_args( $request_filters ) {
		$query_args = array(
			'post_type'              => Discovery_Report_Type::POST_TYPE,
			'post_status'            => 'publish',
			'paged'                  => (int) $request_filters['page'],
			'posts_per_page'         => (int) $request_filters['per_page'],
			'orderby'                => 'date',
			'order'                  => 'DESC',
			'no_found_rows'          => false, // Keep total counts for pagination data.
			'update_post_meta_cache' => true,
			'update_post_term_cache' => true,
		);

		if ( '' !== $request_filters['search'] ) {
			$query_args['s'] = $request_filters['search'];
		}

		$taxonomy_filter_query = self::build_taxonomy_filter_query(
			array(
				'topic'  => $request_filters['topic'],
				'region' => $request_filters['region'],
			)
		);

		if ( ! empty( $taxonomy_filter_query ) ) {
			$query_args['tax_query'] = $taxonomy_filter_query;
		}

		return $query_args;
	}

	/**
	 * Build contextual facet counts for one taxonomy.
	 *
	 * This query only collects matching IDs for counting, so pagination totals are unnecessary.
	 *
	 * @param string $taxonomy_slug         Taxonomy to count terms for.
	 * @param array  $facet_context_filters Filters to keep while counting.
	 * @return array
	 */
	private static function build_contextual_facet_counts( $taxonomy_slug, $facet_context_filters ) {
		$facet_query_args = array(
			'post_type'              => Discovery_Report_Type::POST_TYPE,
			'post_status'            => 'publish',
			'posts_per_page'         => -1,
			'fields'                 => 'ids',
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
		);

		if ( ! empty( $facet_context_filters['search'] ) ) {
			$facet_query_args['s'] = $facet_context_filters['search'];
		}

		$taxonomy_filter_query = self::build_taxonomy_filter_query(
			array(
				'topic'  => isset( $facet_context_filters['topic'] ) ? $facet_context_filters['topic'] : '',
				'region' => isset( $facet_context_filters['region'] ) ? $facet_context_filters['region'] : '',
			)
		);

		if ( ! empty( $taxonomy_filter_query ) ) {
			$facet_query_args['tax_query'] = $taxonomy_filter_query;
		}

		$facet_query = new WP_Query( $facet_query_args );
		$matching_report_ids = array_map( 'intval', (array) $facet_query->posts );

		if ( empty( $matching_report_ids ) ) {
			return array();
		}

		$term_relations = wp_get_object_terms(
			$matching_report_ids,
			$taxonomy_slug,
			array(
				'fields' => 'all_with_object_id',
			)
		);

		if ( is_wp_error( $term_relations ) || empty( $term_relations ) ) {
			return array();
		}

		$facet_counts_by_term_id = array();
		foreach ( $term_relations as $term_relation ) {
			$term_id = (int) $term_relation->term_id;

			if ( ! isset( $facet_counts_by_term_id[ $term_id ] ) ) {
				$facet_counts_by_term_id[ $term_id ] = array(
					'slug'  => $term_relation->slug,
					'name'  => $term_relation->name,
					'count' => 0,
				);
			}

			$facet_counts_by_term_id[ $term_id ]['count']++;
		}

		$facet_items = array_values( $facet_counts_by_term_id );
		usort(
			$facet_items,
			function ( $left, $right ) {
				return strcasecmp( $left['name'], $right['name'] );
			}
		);

		return $facet_items;
	}

	/**
	 * Build a WordPress tax_query array from optional topic/region filters.
	 *
	 * @param array $taxonomy_filters Filter values from the request.
	 * @return array
	 */
	private static function build_taxonomy_filter_query( $taxonomy_filters ) {
		$filter_clauses = array();

		if ( ! empty( $taxonomy_filters['topic'] ) ) {
			$filter_clauses[] = array(
				'taxonomy' => Discovery_Taxonomies::TOPIC_TAXONOMY,
				'field'    => 'slug',
				'terms'    => array( $taxonomy_filters['topic'] ),
			);
		}

		if ( ! empty( $taxonomy_filters['region'] ) ) {
			$filter_clauses[] = array(
				'taxonomy' => Discovery_Taxonomies::REGION_TAXONOMY,
				'field'    => 'slug',
				'terms'    => array( $taxonomy_filters['region'] ),
			);
		}

		if ( empty( $filter_clauses ) ) {
			return array();
		}

		if ( count( $filter_clauses ) > 1 ) {
			$filter_clauses['relation'] = 'AND';
		}

		return $filter_clauses;
	}

	/**
	 * Convert a WP_Post report into a simple API object.
	 *
	 * @param WP_Post $report_post Report post object.
	 * @return array
	 */
	private static function format_report_for_response( $report_post ) {
		return array(
			'id'        => (int) $report_post->ID,
			'title'     => get_the_title( $report_post ),
			'permalink' => get_permalink( $report_post ),
			'excerpt'   => self::build_report_excerpt( $report_post ),
			'topics'    => self::get_report_terms( (int) $report_post->ID, Discovery_Taxonomies::TOPIC_TAXONOMY ),
			'regions'   => self::get_report_terms( (int) $report_post->ID, Discovery_Taxonomies::REGION_TAXONOMY ),
		);
	}

	/**
	 * Fetch and normalize report terms into a simple slug/name list.
	 *
	 * @param int    $report_id     Report ID.
	 * @param string $taxonomy_slug Taxonomy slug.
	 * @return array
	 */
	private static function get_report_terms( $report_id, $taxonomy_slug ) {
		$report_terms = get_the_terms( $report_id, $taxonomy_slug );
		if ( is_wp_error( $report_terms ) || empty( $report_terms ) ) {
			return array();
		}

		$term_items = array();
		foreach ( $report_terms as $report_term ) {
			$term_items[] = array(
				'slug' => $report_term->slug,
				'name' => $report_term->name,
			);
		}

		return $term_items;
	}

	/**
	 * Return a safe excerpt for API responses.
	 *
	 * Uses manual excerpt first, then falls back to trimmed content.
	 *
	 * @param WP_Post $report_post Report post object.
	 * @return string
	 */
	private static function build_report_excerpt( $report_post ) {
		$manual_excerpt = trim( (string) $report_post->post_excerpt );
		if ( '' !== $manual_excerpt ) {
			return wp_strip_all_tags( $manual_excerpt );
		}

		$content_without_shortcodes = strip_shortcodes( (string) $report_post->post_content );
		$plain_text_content         = wp_strip_all_tags( $content_without_shortcodes );

		return wp_trim_words( $plain_text_content, self::EXCERPT_WORD_LIMIT, '...' );
	}

	/**
	 * Sanitize slug-like request values (topic and region).
	 *
	 * @param mixed $value Raw value.
	 * @return string
	 */
	public static function sanitize_slug_param( $value ) {
		return sanitize_title( (string) $value );
	}

	/**
	 * Sanitize and clamp the page parameter.
	 *
	 * @param mixed $value Raw value.
	 * @return int
	 */
	public static function sanitize_page_param( $value ) {
		$page = absint( $value );
		return $page > 0 ? $page : self::DEFAULT_PAGE;
	}

	/**
	 * Sanitize and clamp the per_page parameter.
	 *
	 * @param mixed $value Raw value.
	 * @return int
	 */
	public static function sanitize_per_page_param( $value ) {
		$per_page_value = absint( $value );

		if ( $per_page_value < 1 ) {
			$per_page_value = self::DEFAULT_PER_PAGE;
		}

		return min( self::MAX_PER_PAGE, $per_page_value );
	}
}
