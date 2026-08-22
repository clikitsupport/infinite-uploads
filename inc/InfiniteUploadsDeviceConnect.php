<?php
/**
 * Device-authorization connect — plugin side.
 *
 * The headless connect flow (device flow, like GitHub CLI / Claude Code) lets
 * an agent or CLI connect a site to the Infinite Uploads cloud without a
 * browser round-trip into wp-admin. The site asks the API for a short code, a
 * human approves it while logged in at infiniteuploads.com, and the plugin
 * polls the token route until it receives the api_token + site_id pair.
 *
 * This class is the plugin's half of the domain-verification step that keeps
 * that flow from being a token-phishing vector. See the security note on
 * verify_connect() and the "Agent Connect (Device Flow)" API ticket (§A).
 *
 * The rest of the client (start_device_connect() → poll, the WP-CLI command,
 * the start-connect / check-connect abilities) lands once the API endpoints
 * exist; this ships first so the API side has a real responder to verify
 * against instead of a stub. verify-connect is inert until an attempt is
 * begun, so it is safe to ship ahead of the rest.
 */

namespace ClikIT\InfiniteUploads;

class InfiniteUploadsDeviceConnect {
	private static $instance;

	/**
	 * Network-wide site transient holding the SHA-256 of the in-progress
	 * attempt's client_state. Hash, not the value, so a readable options row
	 * never carries the secret.
	 */
	const STATE_TRANSIENT = 'iup_device_client_state';

	/**
	 * How long an in-progress attempt stays verifiable. Matches the API's
	 * device-code expiry so the two windows line up.
	 */
	const STATE_TTL = 900; // 15 * MINUTE_IN_SECONDS.

	public function __construct() {
		add_action( 'rest_api_init', [ $this, 'register_rest_routes' ] );
	}

	/**
	 * @return InfiniteUploadsDeviceConnect
	 */
	public static function get_instance() {
		if ( ! self::$instance ) {
			self::$instance = new InfiniteUploadsDeviceConnect();
		}

		return self::$instance;
	}

	public function register_rest_routes() {
		register_rest_route( 'infinite-uploads/v1', '/verify-connect', [
			'methods'             => 'POST',
			'callback'            => [ $this, 'rest_verify_connect' ],
			// Called server-to-server by the Infinite Uploads API before the
			// site holds any token, so there is no user session or nonce to
			// check. Authentication is the shared secret itself: the endpoint
			// only ever confirms a client_state the caller already presents,
			// and that value is known only to this site and the API it sent
			// it to over TLS.
			'permission_callback' => '__return_true',
			'args'                => [
				'client_state' => [
					'type'     => 'string',
					'required' => true,
				],
			],
		] );
	}

	/**
	 * Begin a device-connect attempt: mint a fresh client_state, remember it
	 * (hashed) so the verify callback can recognise it, and hand the plaintext
	 * back to the caller to send to the API in the issuance request.
	 *
	 * start_device_connect() (follow-up) calls this, then POSTs the returned
	 * value with the domain to POST api/v1/device.
	 *
	 * @return string The client_state to send to the API.
	 */
	public function begin_attempt() {
		$client_state = wp_generate_password( 64, false, false );
		$this->store_pending_client_state( $client_state );

		return $client_state;
	}

	/**
	 * @param  string  $client_state
	 */
	public function store_pending_client_state( $client_state ) {
		set_site_transient( self::STATE_TRANSIENT, hash( 'sha256', $client_state ), self::STATE_TTL );
	}

	/**
	 * Clear the attempt once the connect completes (or is abandoned), so a
	 * stale client_state can't be verified after the fact. Called by the
	 * poll/complete path in the follow-up.
	 */
	public function clear_pending_client_state() {
		delete_site_transient( self::STATE_TRANSIENT );
	}

	/**
	 * Constant-time check that the presented client_state matches this site's
	 * in-progress attempt.
	 *
	 * @param  string  $client_state
	 *
	 * @return bool
	 */
	public function client_state_matches( $client_state ) {
		$stored = get_site_transient( self::STATE_TRANSIENT );
		if ( empty( $stored ) || ! is_string( $stored ) ) {
			return false;
		}

		return hash_equals( $stored, hash( 'sha256', (string) $client_state ) );
	}

	/**
	 * Verify-connect endpoint: proves to the API that whoever requested a
	 * device code for this domain actually controls this site.
	 *
	 * The API calls this during issuance with the client_state the requester
	 * sent it. Only a site that began the attempt itself holds a matching
	 * value, so an attacker who POSTs someone else's domain to the API can't
	 * make that domain's plugin answer 200 here — which is what stops the
	 * device flow from being used to attach a victim's slot to an attacker's
	 * poller.
	 *
	 * A non-matching value and no-attempt-in-progress return the SAME 403, so
	 * the endpoint can't be used to probe whether a site is mid-connect.
	 *
	 * @param  \WP_REST_Request  $request
	 *
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function rest_verify_connect( $request ) {
		$decision = $this->evaluate_verify( $request->get_param( 'client_state' ) );

		if ( 200 === $decision['status'] ) {
			return new \WP_REST_Response( [ 'verified' => true ], 200 );
		}

		return new \WP_Error( $decision['code'], $decision['message'], [ 'status' => $decision['status'] ] );
	}

	/**
	 * The verify-connect decision, split out from the REST transport so the
	 * security-critical logic is unit-testable without a request/response
	 * double. rest_verify_connect() is the thin glue over this.
	 *
	 * @param  mixed  $client_state  Raw value from the request.
	 *
	 * @return array{status:int,code:string,message:string}
	 */
	public function evaluate_verify( $client_state ) {
		if ( ! is_string( $client_state ) || ! preg_match( '/^[A-Za-z0-9]{32,128}$/', $client_state ) ) {
			return [ 'status' => 400, 'code' => 'iu_invalid_request', 'message' => __( 'Missing or malformed client_state.', 'infinite-uploads' ) ];
		}

		if ( $this->client_state_matches( $client_state ) ) {
			return [ 'status' => 200, 'code' => 'ok', 'message' => '' ];
		}

		// Identical result whether there is no pending attempt or the value
		// simply doesn't match — never reveal which.
		return [ 'status' => 403, 'code' => 'iu_not_verified', 'message' => __( 'Not verified.', 'infinite-uploads' ) ];
	}
}
