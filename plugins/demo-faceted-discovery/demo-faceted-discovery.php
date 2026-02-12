<?php
/**
 * Plugin Name: Demo Faceted Discovery
 * Description: A minimal work sample for taxonomy-driven report discovery with contextual facets over REST.
 * Version: 1.0.0
 * Author: Ajay Khampariya
 * License: GPL-2.0-or-later
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * Text Domain: demo-faceted-discovery
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Load the main plugin bootstrap class.
require_once __DIR__ . '/includes/class-discovery-plugin.php';

// Register custom content structures during activation so rewrites work immediately.
register_activation_hook( __FILE__, array( 'Discovery_Plugin', 'activate' ) );

// Start the plugin once all other plugins are loaded.
add_action( 'plugins_loaded', array( 'Discovery_Plugin', 'boot' ) );
