<?php
/**
 * The TimTim.Live Partner API, from WordPress.
 *
 * The key never reaches a visitor's browser: every call is made here, on the
 * server. Answers are cached for ten minutes, and the last good answer is kept
 * for a day so a short TimTim.Live outage shows yesterday's list instead of an
 * empty box.
 *
 * @package TimTimLiveEvents
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class TTLE_Api {

	const CACHE_SECONDS = 600;
	const KEEP_LAST_SECONDS = DAY_IN_SECONDS;

	/** Filters a visitor or editor may pass, matching GET /v1/events. Anything else is dropped. */
	const FILTERS = array( 'city', 'country', 'category', 'near', 'from', 'to', 'artist', 'limit', 'commissioned' );

	public static function key() {
		$settings = TTLE_Settings::get();
		return isset( $settings['key'] ) ? (string) $settings['key'] : '';
	}

	/**
	 * Clean filters into the exact query the API understands.
	 *
	 * @param array $args Raw shortcode/block attributes.
	 * @return array
	 */
	public static function query( $args ) {
		$query = array();
		foreach ( self::FILTERS as $name ) {
			if ( ! isset( $args[ $name ] ) || '' === $args[ $name ] || null === $args[ $name ] ) {
				continue;
			}
			$value = is_bool( $args[ $name ] ) ? ( $args[ $name ] ? 'true' : 'false' ) : sanitize_text_field( (string) $args[ $name ] );
			if ( 'limit' === $name ) {
				$value = (string) max( 1, min( 24, absint( $value ) ) );
			}
			if ( 'country' === $name ) {
				$value = strtoupper( substr( $value, 0, 2 ) );
			}
			if ( 'commissioned' === $name && 'true' !== $value ) {
				continue;
			}
			$query[ $name ] = substr( $value, 0, 120 );
		}
		return $query;
	}

	/**
	 * GET a Partner API path. Returns array( 'ok' => bool, 'body' => array, 'problem' => array|null ).
	 *
	 * @param string $path  e.g. '/v1/events'.
	 * @param array  $query Query parameters.
	 * @param bool   $fresh Skip the cache (connection test).
	 * @return array
	 */
	public static function get( $path, $query = array(), $fresh = false ) {
		$key = self::key();
		if ( '' === $key ) {
			return array( 'ok' => false, 'body' => array(), 'problem' => array( 'title' => __( 'Add your TimTim.Live key in Settings → TimTim.Live Events.', 'timtim-live-events' ), 'request_id' => '' ) );
		}
		$url       = TTLE_ORIGIN . $path . ( $query ? '?' . http_build_query( $query, '', '&', PHP_QUERY_RFC3986 ) : '' );
		$cache_key = 'ttle_' . md5( $url . '|' . substr( $key, 0, 16 ) );
		if ( ! $fresh ) {
			$cached = get_transient( $cache_key );
			if ( is_array( $cached ) ) {
				return $cached;
			}
		}

		$response = wp_remote_get(
			$url,
			array(
				'timeout'     => 6,
				'redirection' => 0,
				'headers'     => array(
					'Authorization' => 'Bearer ' . $key,
					'Accept'        => 'application/json',
				),
				'user-agent'  => 'TimTim.Live-WordPress/' . TTLE_VERSION . '; ' . home_url( '/' ),
			)
		);

		if ( is_wp_error( $response ) ) {
			$result = array( 'ok' => false, 'body' => array(), 'problem' => array( 'title' => __( 'TimTim.Live could not be reached just now.', 'timtim-live-events' ), 'request_id' => '' ) );
		} else {
			$status = (int) wp_remote_retrieve_response_code( $response );
			$body   = json_decode( (string) wp_remote_retrieve_body( $response ), true );
			$body   = is_array( $body ) ? $body : array();
			$result = $status >= 200 && $status < 300
				? array( 'ok' => true, 'body' => $body, 'problem' => null )
				: array(
					'ok'      => false,
					'body'    => array(),
					'problem' => array(
						'title'      => isset( $body['title'] ) ? (string) $body['title'] : sprintf( /* translators: %d: HTTP status */ __( 'TimTim.Live answered %d.', 'timtim-live-events' ), $status ),
						'detail'     => isset( $body['detail'] ) ? (string) $body['detail'] : '',
						'request_id' => isset( $body['request_id'] ) ? (string) $body['request_id'] : (string) wp_remote_retrieve_header( $response, 'timtim-request-id' ),
					),
				);
		}

		if ( $result['ok'] ) {
			set_transient( $cache_key, $result, self::CACHE_SECONDS );
			set_transient( $cache_key . '_last', $result, self::KEEP_LAST_SECONDS );
			return $result;
		}
		/* A failure: show the last good answer if there is one, and still report the problem to admins. */
		$last = get_transient( $cache_key . '_last' );
		if ( is_array( $last ) && ! $fresh ) {
			$last['problem'] = $result['problem'];
			return $last;
		}
		return $result;
	}

	/** Forget every cached answer (after the key or the defaults change). */
	public static function flush() {
		global $wpdb;
		$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s", $wpdb->esc_like( '_transient_ttle_' ) . '%', $wpdb->esc_like( '_transient_timeout_ttle_' ) . '%' ) );
	}
}
