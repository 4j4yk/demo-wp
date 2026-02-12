<?php
/**
 * Plugin uninstall handler.
 *
 * Removes plugin options created for demo data seeding.
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

delete_option( 'discovery_demo_data_seeded' );
