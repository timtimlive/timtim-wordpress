<?php
/**
 * Event cards. Every value from the API is escaped on output (esc_html,
 * esc_url, esc_attr), links and images must be https, and a ticket link that
 * can earn a reward carries rel="sponsored" — what search engines ask of a
 * paid link.
 *
 * @package TimTimLiveEvents
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class TTLE_Render {

	/**
	 * The whole block: cards, or a friendly empty line, plus the TimTim.Live credit.
	 *
	 * @param array $args Shortcode/block attributes (filters + columns).
	 * @return string HTML.
	 */
	public static function events( $args ) {
		wp_enqueue_style( 'ttle-events' );
		$query  = TTLE_Api::query( wp_parse_args( $args, self::defaults() ) );
		$result = TTLE_Api::get( '/v1/events', $query );
		$events = isset( $result['body']['events'] ) && is_array( $result['body']['events'] ) ? $result['body']['events'] : array();
		$cols   = max( 1, min( 4, absint( isset( $args['columns'] ) ? $args['columns'] : 3 ) ) );

		$html = '<div class="ttle ttle-cols-' . esc_attr( (string) $cols ) . '">';
		if ( $result['problem'] && current_user_can( 'manage_options' ) ) {
			/* Only an administrator sees why; visitors see the events (or the empty line). */
			$html .= '<p class="ttle-admin-note">' . esc_html( sprintf( /* translators: 1: problem, 2: request id */ __( 'TimTim.Live: %1$s %2$s', 'timtim-live-events' ), $result['problem']['title'], $result['problem']['request_id'] ? '(' . $result['problem']['request_id'] . ')' : '' ) ) . '</p>';
		}
		if ( ! $events ) {
			$html .= '<p class="ttle-empty">' . esc_html__( 'No events right now. Check back soon.', 'timtim-live-events' ) . '</p>';
		} else {
			$html .= '<ul class="ttle-grid">';
			foreach ( $events as $event ) {
				$html .= self::card( is_array( $event ) ? $event : array() );
			}
			$html .= '</ul>';
		}
		$html .= '<p class="ttle-credit"><a href="' . esc_url( TTLE_ORIGIN . '/events' ) . '" target="_blank" rel="noopener">' . esc_html__( 'Events by TimTim.Live', 'timtim-live-events' ) . '</a></p>';
		return $html . '</div>';
	}

	/** Site-wide defaults from Settings, which a shortcode or block can override. */
	public static function defaults() {
		$s = TTLE_Settings::get();
		return array(
			'city'         => isset( $s['city'] ) ? $s['city'] : '',
			'country'      => isset( $s['country'] ) ? $s['country'] : '',
			'category'     => isset( $s['category'] ) ? $s['category'] : '',
			'limit'        => isset( $s['limit'] ) ? $s['limit'] : 6,
			'commissioned' => ! empty( $s['earn_only'] ) ? 'true' : '',
			'columns'      => 3,
		);
	}

	private static function https( $url ) {
		return is_string( $url ) && 0 === strpos( $url, 'https://' ) ? $url : '';
	}

	/**
	 * One event.
	 *
	 * @param array $e A public event object from /v1/events.
	 * @return string HTML.
	 */
	public static function card( $e ) {
		$name    = isset( $e['name'] ) ? (string) $e['name'] : '';
		$image   = self::https( isset( $e['image'] ) ? $e['image'] : '' );
		$tickets = isset( $e['tickets'] ) && is_array( $e['tickets'] ) ? $e['tickets'] : array();
		$buy     = self::https( isset( $tickets['buy_url'] ) ? $tickets['buy_url'] : '' );
		$loc     = isset( $e['location'] ) && is_array( $e['location'] ) ? $e['location'] : array();
		$where   = implode( ', ', array_filter( array( isset( $loc['venue'] ) ? $loc['venue'] : '', isset( $loc['city'] ) ? $loc['city'] : '' ) ) );
		$when    = '';
		if ( ! empty( $e['date'] ) && preg_match( '/^\d{4}-\d{2}-\d{2}$/', (string) $e['date'] ) ) {
			/* The venue's own day, shown in the site's language and date format. */
			$when = wp_date( get_option( 'date_format' ), strtotime( $e['date'] . ' 12:00:00 UTC' ), new DateTimeZone( 'UTC' ) );
		}
		$price = '';
		if ( isset( $tickets['from'] ) && null !== $tickets['from'] ) {
			$from  = (float) $tickets['from'];
			$cur   = isset( $tickets['currency'] ) ? (string) $tickets['currency'] : 'USD';
			$price = 0.0 === $from
				? __( 'Free', 'timtim-live-events' )
				: sprintf( /* translators: 1: amount, 2: currency code */ __( 'From %1$s %2$s', 'timtim-live-events' ), number_format_i18n( $from, floor( $from ) === $from ? 0 : 2 ), $cur );
		}
		$earns   = ! empty( $e['earn']['eligible'] );
		$open    = isset( $tickets['availability'] ) && ! in_array( $tickets['availability'], array( 'sold_out', 'ended' ), true );
		$status  = isset( $e['status'] ) ? (string) $e['status'] : '';

		$html = '<li class="ttle-card">';
		if ( $image ) {
			$html .= '<img class="ttle-img" src="' . esc_url( $image ) . '" alt="" loading="lazy" decoding="async" />';
		}
		$html .= '<div class="ttle-body">';
		if ( ! empty( $e['test'] ) ) {
			$html .= '<span class="ttle-badge">' . esc_html__( 'TEST EVENT — NO REAL MONEY', 'timtim-live-events' ) . '</span>';
		}
		if ( 'cancelled' === $status ) {
			$html .= '<span class="ttle-badge ttle-badge-off">' . esc_html__( 'Cancelled', 'timtim-live-events' ) . '</span>';
		}
		$html .= '<p class="ttle-name">' . esc_html( $name ) . '</p>';
		$html .= '<p class="ttle-meta">' . esc_html( implode( ' · ', array_filter( array( $when, $where ) ) ) ) . '</p>';
		if ( $price ) {
			$html .= '<p class="ttle-price">' . esc_html( $price ) . '</p>';
		}
		if ( $buy && $open && 'cancelled' !== $status ) {
			$rel   = $earns ? 'sponsored noopener' : 'noopener';
			$html .= '<a class="ttle-buy" href="' . esc_url( $buy ) . '" target="_blank" rel="' . esc_attr( $rel ) . '">' . esc_html__( 'Get Tickets', 'timtim-live-events' ) . '</a>';
		}
		return $html . '</div></li>';
	}
}
