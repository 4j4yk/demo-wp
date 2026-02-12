<?php
/**
 * Boots the plugin and wires core modules.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Discovery_Plugin {

	/**
	 * Prevent boot logic from running more than once.
	 *
	 * @var bool
	 */
	private static $has_booted = false;

	/**
	 * Load files and register WordPress hooks.
	 */
	public static function boot() {
		if ( self::$has_booted ) {
			return;
		}

		self::$has_booted = true;
		self::load_required_files();

		Discovery_Report_Type::register_hooks();
		Discovery_Taxonomies::register_hooks();
		Discovery_Reports_REST::register_hooks();
	}

	/**
	 * Register content types on activation, then refresh rewrite rules.
	 */
	public static function activate() {
		self::load_required_files();
		Discovery_Report_Type::register_post_type();
		Discovery_Taxonomies::register_taxonomies();
		Discovery_Demo_Data::seed_on_activation();
		flush_rewrite_rules();
	}

	/**
	 * Include all class files needed by this plugin.
	 */
	private static function load_required_files() {
		require_once __DIR__ . '/content/class-discovery-report-type.php';
		require_once __DIR__ . '/content/class-discovery-taxonomies.php';
		require_once __DIR__ . '/content/class-discovery-demo-data.php';
		require_once __DIR__ . '/rest/class-discovery-reports-rest.php';
	}
}
