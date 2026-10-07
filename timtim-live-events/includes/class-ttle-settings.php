<?php
/**
 * Settings → TimTim.Live Events: connect a key, choose the defaults, test the
 * connection. Only administrators (manage_options) see or change any of it;
 * the Settings API checks the nonce, and the test button has its own.
 *
 * The key is stored in this site's database (option ttle_settings, not
 * autoloaded) and sent only to https://timtim.live. A WEBSITE KEY
 * (tt_pk_live_…) is the right key here: it can read events and nothing else.
 *
 * @package TimTimLiveEvents
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class TTLE_Settings {

	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'menu' ) );
		add_action( 'admin_init', array( __CLASS__, 'register' ) );
		add_action( 'admin_post_ttle_test', array( __CLASS__, 'test' ) );
	}

	public static function get() {
		$value = get_option( TTLE_OPTION, array() );
		return is_array( $value ) ? $value : array();
	}

	public static function menu() {
		add_options_page( __( 'TimTim.Live Events', 'timtim-live-events' ), __( 'TimTim.Live Events', 'timtim-live-events' ), 'manage_options', 'timtim-live-events', array( __CLASS__, 'page' ) );
	}

	public static function register() {
		register_setting(
			'ttle',
			TTLE_OPTION,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( __CLASS__, 'sanitize' ),
				'default'           => array(),
				'show_in_rest'      => false,
			)
		);
	}

	/**
	 * Clean what an administrator saved. An empty key field keeps the saved key,
	 * so the key never has to be shown again in the page.
	 *
	 * @param mixed $input Posted values.
	 * @return array
	 */
	public static function sanitize( $input ) {
		$old   = self::get();
		$input = is_array( $input ) ? $input : array();
		$key   = isset( $input['key'] ) ? trim( sanitize_text_field( (string) $input['key'] ) ) : '';
		if ( '' === $key ) {
			$key = isset( $old['key'] ) ? $old['key'] : '';
		} elseif ( ! preg_match( '/^tt_(test|pk_live|sk_live)_[A-Za-z0-9_-]{8,200}$/', $key ) ) {
			add_settings_error( TTLE_OPTION, 'ttle_key', __( 'That does not look like a TimTim.Live key. It starts with tt_pk_live_ or tt_test_.', 'timtim-live-events' ) );
			$key = isset( $old['key'] ) ? $old['key'] : '';
		}
		$clean = array(
			'key'       => $key,
			'city'      => isset( $input['city'] ) ? substr( sanitize_text_field( (string) $input['city'] ), 0, 80 ) : '',
			'country'   => isset( $input['country'] ) ? strtoupper( substr( preg_replace( '/[^A-Za-z]/', '', (string) $input['country'] ), 0, 2 ) ) : '',
			'category'  => isset( $input['category'] ) ? sanitize_key( (string) $input['category'] ) : '',
			'limit'     => isset( $input['limit'] ) ? max( 1, min( 24, absint( $input['limit'] ) ) ) : 6,
			'earn_only' => ! empty( $input['earn_only'] ),
		);
		TTLE_Api::flush();
		return $clean;
	}

	private static function mask( $key ) {
		return $key ? substr( $key, 0, strpos( $key, '_', 3 ) + 1 ) . '…' . substr( $key, -4 ) : '';
	}

	public static function page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$s    = self::get();
		$test = get_transient( 'ttle_test_result_' . get_current_user_id() );
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'TimTim.Live Events', 'timtim-live-events' ); ?></h1>
			<p><?php esc_html_e( 'Show live events from TimTim.Live on your site. Every "Get Tickets" button is your own TimTim.Live link, so the people you send are counted for you.', 'timtim-live-events' ); ?></p>
			<p><a href="<?php echo esc_url( TTLE_ORIGIN . '/partners/dashboard' ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Get a key on your TimTim.Live partner dashboard', 'timtim-live-events' ); ?></a></p>
			<?php settings_errors( TTLE_OPTION ); ?>
			<?php if ( is_array( $test ) ) : ?>
				<div class="notice <?php echo esc_attr( $test['ok'] ? 'notice-success' : 'notice-error' ); ?>"><p><?php echo esc_html( $test['message'] ); ?></p></div>
			<?php endif; ?>
			<form method="post" action="options.php">
				<?php settings_fields( 'ttle' ); ?>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><label for="ttle-key"><?php esc_html_e( 'Your connection key', 'timtim-live-events' ); ?></label></th>
						<td>
							<input id="ttle-key" type="password" class="regular-text" autocomplete="off" name="<?php echo esc_attr( TTLE_OPTION ); ?>[key]" value="" placeholder="<?php echo esc_attr( self::mask( isset( $s['key'] ) ? $s['key'] : '' ) ); ?>" />
							<p class="description"><?php esc_html_e( 'Use a website key (tt_pk_live_…), or a test key (tt_test_…) to try it with sample events. Leave empty to keep the saved key.', 'timtim-live-events' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="ttle-city"><?php esc_html_e( 'City', 'timtim-live-events' ); ?></label></th>
						<td><input id="ttle-city" type="text" class="regular-text" name="<?php echo esc_attr( TTLE_OPTION ); ?>[city]" value="<?php echo esc_attr( isset( $s['city'] ) ? $s['city'] : '' ); ?>" /></td>
					</tr>
					<tr>
						<th scope="row"><label for="ttle-country"><?php esc_html_e( 'Country (two letters, for example US)', 'timtim-live-events' ); ?></label></th>
						<td><input id="ttle-country" type="text" maxlength="2" class="small-text" name="<?php echo esc_attr( TTLE_OPTION ); ?>[country]" value="<?php echo esc_attr( isset( $s['country'] ) ? $s['country'] : '' ); ?>" /></td>
					</tr>
					<tr>
						<th scope="row"><label for="ttle-category"><?php esc_html_e( 'Category', 'timtim-live-events' ); ?></label></th>
						<td>
							<select id="ttle-category" name="<?php echo esc_attr( TTLE_OPTION ); ?>[category]">
								<?php
								$cats = array(
									''           => __( 'Any category', 'timtim-live-events' ),
									'music'      => __( 'Music', 'timtim-live-events' ),
									'festival'   => __( 'Festival', 'timtim-live-events' ),
									'conference' => __( 'Conference', 'timtim-live-events' ),
									'nightlife'  => __( 'Nightlife', 'timtim-live-events' ),
								);
								foreach ( $cats as $value => $label ) {
									echo '<option value="' . esc_attr( $value ) . '"' . selected( isset( $s['category'] ) ? $s['category'] : '', $value, false ) . '>' . esc_html( $label ) . '</option>';
								}
								?>
							</select>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="ttle-limit"><?php esc_html_e( 'How many events', 'timtim-live-events' ); ?></label></th>
						<td><input id="ttle-limit" type="number" min="1" max="24" class="small-text" name="<?php echo esc_attr( TTLE_OPTION ); ?>[limit]" value="<?php echo esc_attr( (string) ( isset( $s['limit'] ) ? $s['limit'] : 6 ) ); ?>" /></td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Earning opportunities', 'timtim-live-events' ); ?></th>
						<td><label><input type="checkbox" name="<?php echo esc_attr( TTLE_OPTION ); ?>[earn_only]" value="1" <?php checked( ! empty( $s['earn_only'] ) ); ?> /> <?php esc_html_e( 'Only show events that pay a reward', 'timtim-live-events' ); ?></label></td>
					</tr>
				</table>
				<?php submit_button(); ?>
			</form>

			<h2><?php esc_html_e( 'Check the connection', 'timtim-live-events' ); ?></h2>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="ttle_test" />
				<?php wp_nonce_field( 'ttle_test' ); ?>
				<?php submit_button( __( 'Test Connection', 'timtim-live-events' ), 'secondary', 'submit', false ); ?>
			</form>

			<h2><?php esc_html_e( 'Show events on a page', 'timtim-live-events' ); ?></h2>
			<p><?php esc_html_e( 'Add the "TimTim.Live Events" block in the editor, or paste this shortcode:', 'timtim-live-events' ); ?></p>
			<p><code>[timtim_events city="Washington" category="music" limit="6"]</code></p>
			<p><?php esc_html_e( 'Shortcode options: city, country, category, near, from, to, artist, limit (1–24), earn="true", columns (1–4).', 'timtim-live-events' ); ?></p>
		</div>
		<?php
	}

	/** "Test Connection": one fresh request, the answer kept for this admin for a minute. */
	public static function test() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You are not allowed to do this.', 'timtim-live-events' ), 403 );
		}
		check_admin_referer( 'ttle_test' );
		$result = TTLE_Api::get( '/v1/events', array( 'limit' => '1' ), true );
		if ( $result['ok'] ) {
			$mode    = isset( $result['body']['mode'] ) ? (string) $result['body']['mode'] : '';
			$message = 'test' === $mode
				? __( 'Connected with a test key. You will see sample events marked TEST EVENT — NO REAL MONEY.', 'timtim-live-events' )
				: __( 'Connected. Your events come from TimTim.Live.', 'timtim-live-events' );
		} else {
			$message = trim( $result['problem']['title'] . ' ' . ( isset( $result['problem']['detail'] ) ? $result['problem']['detail'] : '' ) . ( $result['problem']['request_id'] ? ' (' . $result['problem']['request_id'] . ')' : '' ) );
		}
		set_transient( 'ttle_test_result_' . get_current_user_id(), array( 'ok' => $result['ok'], 'message' => $message ), MINUTE_IN_SECONDS );
		wp_safe_redirect( admin_url( 'options-general.php?page=timtim-live-events' ) );
		exit;
	}
}
