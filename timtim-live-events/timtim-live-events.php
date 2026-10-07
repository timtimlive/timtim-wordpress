<?php
/**
 * Plugin Name:       TimTim.Live Events
 * Plugin URI:        https://timtim.live/partners/docs#wordpress
 * Description:       Show live events from TimTim.Live on your site, with TimTim.Live ticket links that count for you. A block and a shortcode, powered by the TimTim.Live Partner API.
 * Version:           1.0.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            TimTim.Live
 * Author URI:        https://timtim.live/partners
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       timtim-live-events
 * Update URI:        https://timtim.live/partner-api/wordpress/
 *
 * Built and hosted by TimTim.Live. It talks to one place only — the TimTim.Live
 * Partner API at https://timtim.live/v1 — and updates itself only from
 * https://timtim.live (the Update URI above stops WordPress.org from ever
 * replacing it with a different plugin of the same name).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'TTLE_VERSION', '1.0.0' );
define( 'TTLE_FILE', __FILE__ );
define( 'TTLE_DIR', plugin_dir_path( __FILE__ ) );
define( 'TTLE_URL', plugin_dir_url( __FILE__ ) );
/* The only origin this plugin ever contacts. */
define( 'TTLE_ORIGIN', 'https://timtim.live' );
define( 'TTLE_OPTION', 'ttle_settings' );

require_once TTLE_DIR . 'includes/class-ttle-api.php';
require_once TTLE_DIR . 'includes/class-ttle-render.php';
require_once TTLE_DIR . 'includes/class-ttle-settings.php';
require_once TTLE_DIR . 'includes/class-ttle-blocks.php';
require_once TTLE_DIR . 'includes/class-ttle-updater.php';

add_action(
	'plugins_loaded',
	static function () {
		load_plugin_textdomain( 'timtim-live-events', false, dirname( plugin_basename( TTLE_FILE ) ) . '/languages' );
	}
);

TTLE_Settings::init();
TTLE_Blocks::init();
TTLE_Updater::init();

/** A link to Settings on the Plugins screen, so "Connect" is one click away. */
add_filter(
	'plugin_action_links_' . plugin_basename( TTLE_FILE ),
	static function ( $links ) {
		$links[] = '<a href="' . esc_url( admin_url( 'options-general.php?page=timtim-live-events' ) ) . '">' . esc_html__( 'Connect', 'timtim-live-events' ) . '</a>';
		return $links;
	}
);
