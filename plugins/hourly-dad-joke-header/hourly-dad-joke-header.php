<?php
/**
 * Plugin Name: Hourly Dad Joke Header
 * Description: Refreshes a dad joke hourly and exposes it for theme headers.
 * Version: 0.1.0
 * Author: Ajay Khampariya
 * License: GPL-2.0-or-later
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class HDJH_Plugin {
	const EVENT_HOOK = 'hdjh_refresh_joke_event';
	const TRANSIENT  = 'hdjh_hourly_joke';

	/**
	 * Boot plugin hooks.
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'ensure_schedule' ) );
		add_action( self::EVENT_HOOK, array( __CLASS__, 'refresh_joke' ) );
		add_filter( 'demo_minimal_header_joke', array( __CLASS__, 'filter_header_joke' ) );
	}

	/**
	 * Activation callback.
	 */
	public static function activate() {
		self::ensure_schedule();
		self::refresh_joke();
	}

	/**
	 * Deactivation callback.
	 */
	public static function deactivate() {
		$timestamp = wp_next_scheduled( self::EVENT_HOOK );
		if ( $timestamp ) {
			wp_unschedule_event( $timestamp, self::EVENT_HOOK );
		}
		delete_transient( self::TRANSIENT );
	}

	/**
	 * Keep hourly cron scheduled.
	 */
	public static function ensure_schedule() {
		if ( ! wp_next_scheduled( self::EVENT_HOOK ) ) {
			wp_schedule_event( time() + MINUTE_IN_SECONDS, 'hourly', self::EVENT_HOOK );
		}
	}

	/**
	 * Refresh joke cache from remote API with local fallback.
	 */
	public static function refresh_joke() {
		$joke = self::fetch_remote_joke();
		if ( '' === $joke ) {
			$joke = self::fallback_joke();
		}

		set_transient(
			self::TRANSIENT,
			array(
				'joke'       => $joke,
				'updated_at' => time(),
			),
			2 * HOUR_IN_SECONDS
		);
	}

	/**
	 * Provide joke to theme filter.
	 *
	 * @param string $value Existing value.
	 * @return string
	 */
	public static function filter_header_joke( $value ) {
		$payload = get_transient( self::TRANSIENT );
		if ( is_array( $payload ) && ! empty( $payload['joke'] ) ) {
			return (string) $payload['joke'];
		}

		self::refresh_joke();
		$payload = get_transient( self::TRANSIENT );
		if ( is_array( $payload ) && ! empty( $payload['joke'] ) ) {
			return (string) $payload['joke'];
		}

		return (string) $value;
	}

	/**
	 * Fetch one joke from icanhazdadjoke API.
	 *
	 * @return string
	 */
	private static function fetch_remote_joke() {
		$response = wp_remote_get(
			'https://icanhazdadjoke.com/',
			array(
				'timeout' => 5,
				'headers' => array(
					'Accept'     => 'application/json',
					'User-Agent' => 'WordPress Hourly Dad Joke Header',
				),
			)
		);

		if ( is_wp_error( $response ) ) {
			return '';
		}

		$code = wp_remote_retrieve_response_code( $response );
		if ( 200 !== (int) $code ) {
			return '';
		}

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		if ( ! is_array( $data ) || empty( $data['joke'] ) ) {
			return '';
		}

		return trim( wp_strip_all_tags( (string) $data['joke'] ) );
	}

	/**
	 * Local fallback joke list when API is unreachable.
	 *
	 * @return string
	 */
	private static function fallback_joke() {
		$jokes = array(
			'I only know 25 letters of the alphabet. I do not know y.',
			'How does a penguin build its house? Igloos it together.',
			'I used to hate facial hair, but then it grew on me.',
			'Why did the math book look sad? It had too many problems.',
			'I am reading a book on anti-gravity. It is impossible to put down.',
		);

		return $jokes[ array_rand( $jokes ) ];
	}
}

register_activation_hook( __FILE__, array( 'HDJH_Plugin', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'HDJH_Plugin', 'deactivate' ) );
add_action( 'plugins_loaded', array( 'HDJH_Plugin', 'init' ) );
