<?php
/**
 * The "TimTim.Live Events" block and the [timtim_events] shortcode. Both render
 * on the server through TTLE_Render, so the key never reaches the browser and
 * the block, the shortcode and the editor preview are the same HTML.
 *
 * @package TimTimLiveEvents
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class TTLE_Blocks {

	public static function init() {
		add_action( 'init', array( __CLASS__, 'register' ) );
		add_shortcode( 'timtim_events', array( __CLASS__, 'shortcode' ) );
	}

	public static function register() {
		wp_register_style( 'ttle-events', TTLE_URL . 'assets/style.css', array(), TTLE_VERSION );
		wp_register_script(
			'ttle-block-editor',
			TTLE_URL . 'assets/block.js',
			array( 'wp-blocks', 'wp-element', 'wp-components', 'wp-block-editor', 'wp-server-side-render', 'wp-i18n' ),
			TTLE_VERSION,
			true
		);
		if ( function_exists( 'wp_set_script_translations' ) ) {
			wp_set_script_translations( 'ttle-block-editor', 'timtim-live-events', TTLE_DIR . 'languages' );
		}
		register_block_type(
			'timtim-live/events',
			array(
				'api_version'     => 2,
				'editor_script'   => 'ttle-block-editor',
				'style'           => 'ttle-events',
				'render_callback' => array( __CLASS__, 'render_block' ),
				'attributes'      => array(
					'city'         => array( 'type' => 'string', 'default' => '' ),
					'country'      => array( 'type' => 'string', 'default' => '' ),
					'category'     => array( 'type' => 'string', 'default' => '' ),
					'limit'        => array( 'type' => 'number', 'default' => 6 ),
					'columns'      => array( 'type' => 'number', 'default' => 3 ),
					'commissioned' => array( 'type' => 'boolean', 'default' => false ),
				),
			)
		);
	}

	/**
	 * Empty block fields fall back to the site defaults from Settings.
	 *
	 * @param array $attributes Block attributes.
	 * @return string
	 */
	public static function render_block( $attributes ) {
		$args = array_filter(
			(array) $attributes,
			static function ( $value ) {
				return '' !== $value && null !== $value && false !== $value;
			}
		);
		return TTLE_Render::events( $args );
	}

	/**
	 * [timtim_events city="Paris" category="music" limit="6" earn="true" columns="3"]
	 *
	 * @param array|string $atts Shortcode attributes.
	 * @return string
	 */
	public static function shortcode( $atts ) {
		$atts = shortcode_atts(
			array(
				'city'     => '',
				'country'  => '',
				'category' => '',
				'near'     => '',
				'from'     => '',
				'to'       => '',
				'artist'   => '',
				'limit'    => '',
				'earn'     => '',
				'columns'  => '',
			),
			$atts,
			'timtim_events'
		);
		$atts['commissioned'] = 'true' === strtolower( (string) $atts['earn'] ) ? 'true' : '';
		unset( $atts['earn'] );
		return TTLE_Render::events( array_filter( $atts, 'strlen' ) );
	}
}
