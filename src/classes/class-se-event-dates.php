<?php
/**
 * Event Date Class.
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Event Date Class.
 */
class SE_Event_Dates {


	/**
	 * Endpoint namespace.
	 *
	 * @var string
	 */
	protected $namespace = 'simple-events';

	/**
	 * The dates rest base.
	 *
	 * @var string
	 */
	protected $rest_base_dates = 'event-dates';

	/**
	 * Initialize the class.
	 *
	 * @since 1.0.0
	 */
	public static function init() {
		$instance = new self();

		add_action( 'rest_api_init', array( $instance, 'register_rest_routes' ) );
	}

	/**
	 * Registers all rest routes.
	 *
	 * @since 2.0.0
	 *
	 * @return void
	 */
	public function register_rest_routes(): void {
		// Get event dates.
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base_dates . '/(?P<event_id>[\d]+)',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_event_dates' ),
				'permission_callback' => '__return_true',
			)
		);
	}

	/**
	 * Get event dates.
	 *
	 * @param WP_REST_Request $request The request object.
	 *
	 * @return WP_REST_Response
	 */
	public function get_event_dates( WP_REST_Request $request ): WP_REST_Response {
		// If we dont have an event ID, return an error.
		$event_id = $request->get_param( 'event_id' );
		if ( empty( $event_id ) || ! is_numeric( $event_id ) ) {
			return new WP_REST_Response(
				array(
					'code'    => 'invalid_event_id',
					'message' => __( 'Invalid event ID provided.', 'simple-events' ),
				),
				400
			);
		}

		// Check if we have a valid event.
		$event = get_post( $event_id );
		if ( ! $event || 'se-event' !== $event->post_type ) {
			return new WP_REST_Response(
				array(
					'code'    => 'invalid_event',
					'message' => __( 'Invalid event provided.', 'simple-events' ),
				),
				404
			);
		}

		try {
			$dates = se_event_get_event_dates( $event_id );
		} catch (\Throwable $th) {
			return new WP_REST_Response(
				array(
					'code'    => 'server_error',
					'message' => __( 'An error occurred while fetching event dates.', 'simple-events' ),
				),
				500
			);
		}

		// Create the return.
		$data = [
			'event_id' => $event_id,
			'dates'    => $dates,
			'timezone' => get_post_meta( $event_id, 'se_event_timezone', true ) ?: wp_timezone_string(),
		];

		// Return the response.
		return new WP_REST_Response(
			$data,
			200
		);

		adie("Fetching dates for event ID: {$event_id}");
	}

	/** */
}
SE_Event_Dates::init();