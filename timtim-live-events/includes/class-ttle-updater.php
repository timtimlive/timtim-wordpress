<?php
/**
 * Updates from timtim.live, and only from timtim.live.
 *
 * The plugin header's "Update URI: https://timtim.live/partner-api/wordpress/"
 * tells WordPress (5.8+) that this plugin is NOT on WordPress.org, so a
 * same-named plugin there can never overwrite it, and that its updates are
 * answered by the `update_plugins_timtim.live` filter below. That filter reads
 * https://timtim.live/partner-api/wordpress/info.json (cached 12 hours) and
 * offers the zip it names — which must itself be on https://timtim.live.
 *
 * @package TimTimLiveEvents
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class TTLE_Updater {

	const INFO_URL = TTLE_ORIGIN . '/partner-api/wordpress/info.json';
	const CACHE    = 'ttle_update_info';

	public static function init() {
		add_filter( 'update_plugins_timtim.live', array( __CLASS__, 'offer' ), 10, 4 );
		add_filter( 'plugins_api', array( __CLASS__, 'details' ), 10, 3 );
	}

	/** The published release, or null. Only a package on https://timtim.live/ is ever accepted. */
	public static function info() {
		$cached = get_site_transient( self::CACHE );
		if ( is_array( $cached ) ) {
			return $cached;
		}
		$response = wp_remote_get( self::INFO_URL, array( 'timeout' => 6, 'redirection' => 0 ) );
		if ( is_wp_error( $response ) || 200 !== (int) wp_remote_retrieve_response_code( $response ) ) {
			return null;
		}
		$info = json_decode( (string) wp_remote_retrieve_body( $response ), true );
		if ( ! is_array( $info ) || empty( $info['version'] ) || empty( $info['download_url'] ) || 0 !== strpos( (string) $info['download_url'], TTLE_ORIGIN . '/' ) ) {
			return null;
		}
		set_site_transient( self::CACHE, $info, 12 * HOUR_IN_SECONDS );
		return $info;
	}

	/**
	 * @param array|false $update      Update data so far.
	 * @param array       $plugin_data Header data.
	 * @param string      $plugin_file Plugin basename.
	 * @return array|false
	 */
	public static function offer( $update, $plugin_data, $plugin_file ) {
		if ( plugin_basename( TTLE_FILE ) !== $plugin_file ) {
			return $update;
		}
		$info = self::info();
		if ( ! $info ) {
			return $update;
		}
		return array(
			'id'           => TTLE_ORIGIN . '/partner-api/wordpress/',
			'slug'         => 'timtim-live-events',
			'version'      => (string) $info['version'],
			'url'          => TTLE_ORIGIN . '/partners/docs#wordpress',
			'package'      => (string) $info['download_url'],
			'requires'     => isset( $info['requires'] ) ? (string) $info['requires'] : '6.0',
			'tested'       => isset( $info['tested'] ) ? (string) $info['tested'] : '',
			'requires_php' => isset( $info['requires_php'] ) ? (string) $info['requires_php'] : '7.4',
		);
	}

	/** The "View details" window on the Plugins screen. */
	public static function details( $result, $action, $args ) {
		if ( 'plugin_information' !== $action || empty( $args->slug ) || 'timtim-live-events' !== $args->slug ) {
			return $result;
		}
		$info = self::info();
		if ( ! $info ) {
			return $result;
		}
		return (object) array(
			'name'          => 'TimTim.Live Events',
			'slug'          => 'timtim-live-events',
			'version'       => (string) $info['version'],
			'author'        => '<a href="' . esc_url( TTLE_ORIGIN ) . '">TimTim.Live</a>',
			'homepage'      => TTLE_ORIGIN . '/partners/docs#wordpress',
			'download_link' => (string) $info['download_url'],
			'requires'      => isset( $info['requires'] ) ? (string) $info['requires'] : '6.0',
			'tested'        => isset( $info['tested'] ) ? (string) $info['tested'] : '',
			'requires_php'  => isset( $info['requires_php'] ) ? (string) $info['requires_php'] : '7.4',
			'sections'      => array(
				'description' => esc_html( isset( $info['description'] ) ? (string) $info['description'] : '' ),
				'changelog'   => esc_html( isset( $info['changelog'] ) ? (string) $info['changelog'] : '' ),
			),
		);
	}
}
