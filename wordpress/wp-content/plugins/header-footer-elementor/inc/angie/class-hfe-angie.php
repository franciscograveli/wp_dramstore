<?php
/**
 * Angie MCP Integration.
 *
 * Bridges HFE abilities to Elementor's Angie AI assistant via REST API
 * and a JavaScript MCP server. Follows the WS Form pattern:
 *
 * 1. PHP registers REST routes for each ability
 * 2. JS fetches ability schemas, registers tools with AngieMcpSdk
 * 3. Angie calls tools → JS POSTs to REST → PHP executes handler
 *
 * @package header-footer-elementor
 * @since 2.9.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class HFE_Angie
 *
 * @since 2.9.0
 */
class HFE_Angie {

	/**
	 * REST namespace.
	 *
	 * @var string
	 */
	const REST_NAMESPACE = 'hfe/v1';

	/**
	 * Registry instance.
	 *
	 * @var HFE_Ability_Registry
	 */
	private $registry;

	/**
	 * Constructor.
	 *
	 * @param HFE_Ability_Registry $registry The ability registry.
	 */
	public function __construct( HFE_Ability_Registry $registry ) {
		$this->registry = $registry;

		add_action( 'rest_api_init', [ $this, 'register_rest_routes' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
	}

	/**
	 * Register REST routes for Angie.
	 *
	 * Creates two types of routes:
	 * - POST /hfe/v1/angie/abilities/ → returns all ability schemas
	 * - POST /hfe/v1/angie/{ability_id}/ → executes a specific ability
	 *
	 * @return void
	 */
	public function register_rest_routes() {
		// Abilities list endpoint.
		register_rest_route(
			self::REST_NAMESPACE,
			'/angie/abilities/',
			[
				'methods'             => 'POST',
				'callback'            => [ $this, 'handle_abilities_list' ],
				'permission_callback' => [ $this, 'check_list_permission' ],
			]
		);

		// Single wildcard route for executing any ability by Angie ID.
		// Uses a regex parameter instead of per-ability routes because
		// rest_api_init may fire before wp_abilities_api_init (discover).
		register_rest_route(
			self::REST_NAMESPACE,
			'/angie/(?P<ability_id>[a-zA-Z0-9_-]+)/',
			[
				'methods'             => 'POST',
				'callback'            => [ $this, 'handle_execute' ],
				'permission_callback' => [ $this, 'check_execute_permission' ],
				'args'                => [
					'ability_id' => [
						'required'          => true,
						'validate_callback' => function ( $param ) {
							return is_string( $param ) && preg_match( '/^[a-zA-Z0-9_-]+$/', $param );
						},
					],
				],
			]
		);
	}

	/**
	 * Handle the abilities list request.
	 *
	 * Returns all registered ability schemas for the JS MCP server
	 * to register as tools with Angie.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return array Array of ability schema objects.
	 */
	/**
	 * Abilities to hide from Angie because its AI can't use them correctly.
	 * These tools work fine via MCP Adapter (Claude Code/Desktop).
	 *
	 * @var array
	 */
	private static $angie_hidden_abilities = [
		'builder/add-section',   // Complex params — Angie should use insert-widget or builder/build.
		'builder/add-column',    // Complex params — same issue.
	];

	public function handle_abilities_list( $request ) {
		$abilities_info = $this->registry->get_abilities_info();
		$result         = [];

		foreach ( $abilities_info as $info ) {
			// Skip abilities Angie can't use correctly.
			if ( in_array( $info['name'], self::$angie_hidden_abilities, true ) ) {
				continue;
			}

			$handler = $this->registry->get_handler( $info['name'] );

			if ( ! $handler ) {
				continue;
			}

			$args     = $handler->get_registration_args();
			$angie_id = self::ability_name_to_angie_id( $info['full_name'] );

			$result[] = [
				'type'          => 'tool',
				'name'          => $angie_id,
				'label'         => $info['label'],
				'description'   => $info['description'],
				'input_schema'  => $args['input_schema'] ?? [ 'type' => 'object', 'properties' => (object) [] ],
				'output_schema' => $args['output_schema'] ?? [ 'type' => 'object' ],
			];
		}

		return $result;
	}

	/**
	 * Enqueue the Angie MCP server JS module.
	 *
	 * Only enqueues when Angie is active (ANGIE_VERSION defined)
	 * and Angie integration is enabled in settings.
	 *
	 * @return void
	 */
	public function enqueue_scripts() {
		// Only load when Angie is active.
		if ( ! defined( 'ANGIE_VERSION' ) ) {
			return;
		}

		// Angie sits on top of the abilities feature, so both gates must be on.
		// Default to off when a flag is missing so the script is never enqueued
		// for users who have not opted in.
		$settings = get_option( 'uae_mcp_settings', [] );

		if ( empty( $settings['enable_abilities'] ) || empty( $settings['angie_enabled'] ) ) {
			return;
		}

		wp_enqueue_script_module(
			'hfe-angie-mcp-server',
			HFE_URL . 'inc/angie/hfe-angie-mcp-server.min.js',
			[],
			defined( 'HFE_VER' ) ? HFE_VER : '1.0.0',
			[ 'in_footer' => true ]
		);
	}

	/**
	 * Convert a WordPress ability name to an Angie-compatible ID.
	 *
	 * Angie requires tool names matching ^[a-zA-Z0-9_-]+$.
	 * Strips the plugin prefix and replaces / with -.
	 *
	 * @param string $ability_name Full ability name.
	 * @return string Angie-compatible ID.
	 */
	public static function ability_name_to_angie_id( $ability_name ) {
		// Remove plugin prefix.
		$prefix = HFE_Ability_Registry::PREFIX;

		if ( 0 === strpos( $ability_name, $prefix ) ) {
			$ability_name = substr( $ability_name, strlen( $prefix ) );
		}

		// Replace / with - and strip invalid characters.
		$ability_name = str_replace( '/', '-', $ability_name );
		$ability_name = preg_replace( '/[^a-zA-Z0-9_-]/', '', $ability_name );

		return $ability_name;
	}

	/**
	 * Convert an Angie ID back to a handler name (without prefix).
	 *
	 * @param string $angie_id Angie tool ID.
	 * @return string|null Handler name or null if not found.
	 */
	private function angie_id_to_handler_name( $angie_id ) {
		// Try direct match with - → / conversion.
		$parts = explode( '-', $angie_id, 2 );

		if ( 2 === count( $parts ) ) {
			$candidate = $parts[0] . '/' . $parts[1];

			if ( $this->registry->get_handler( $candidate ) ) {
				return $candidate;
			}
		}

		// Fallback: search all handlers.
		foreach ( $this->registry->get_handler_names() as $name ) {
			if ( self::ability_name_to_angie_id( HFE_Ability_Registry::PREFIX . $name ) === $angie_id ) {
				return $name;
			}
		}

		return null;
	}

	/**
	 * Handle ability execution via the wildcard REST route.
	 *
	 * @param WP_REST_Request $request Request with ability_id parameter.
	 * @return array|WP_REST_Response Result data or error response.
	 */
	public function handle_execute( $request ) {
		$angie_id     = sanitize_text_field( $request->get_param( 'ability_id' ) );
		$handler_name = $this->angie_id_to_handler_name( $angie_id );

		if ( ! $handler_name ) {
			return new \WP_REST_Response(
				[
					'error'   => true,
					'code'    => 'hfe_ability_not_found',
					'message' => sprintf(
						/* translators: %s: ability ID */
						__( 'Ability "%s" not found.', 'header-footer-elementor' ),
						$angie_id
					),
				],
				404
			);
		}

		// Verify the ability passed discover() gating (allow_modifications + disabled_abilities).
		if ( ! $this->registry->is_registered( $handler_name ) ) {
			$reason       = $this->registry->get_gate_reason( $handler_name );
			$settings_url = admin_url( 'admin.php?page=hfe#settings' );

			if ( 'allow_modifications' === $reason ) {
				$message = sprintf(
					/* translators: %s: settings URL */
					__( 'BLOCKED: Modifications are disabled by the site administrator. Do NOT attempt to enable this yourself. Tell the user: "Allow Modifications" must be turned on in UAE Settings → AI Tools at %s', 'header-footer-elementor' ),
					$settings_url
				);
			} else {
				$message = sprintf(
					/* translators: 1: ability ID, 2: settings URL */
					__( 'BLOCKED: The ability "%1$s" has been disabled by the site administrator. Do NOT retry. Tell the user to enable it in UAE Settings → AI Tools at %2$s', 'header-footer-elementor' ),
					$angie_id,
					$settings_url
				);
			}

			return new \WP_REST_Response(
				[
					'error'        => true,
					'code'         => 'hfe_ability_disabled',
					'message'      => $message,
					'reason'       => $reason,
					'settings_url' => $settings_url,
				],
				403
			);
		}

		$params = $request->get_json_params();

		if ( ! is_array( $params ) ) {
			$params = [];
		}

		// Remove the ability_id from params (it's a URL param, not an input).
		unset( $params['ability_id'] );

		// Angie's AI sometimes passes complex params (arrays/objects) as JSON strings.
		// Decode them so handlers receive proper PHP arrays.
		$params = self::decode_stringified_params( $params );

		$result = $this->registry->execute( $handler_name, $params );

		// Strip pro_alternative from widget list responses — it confuses Angie's AI
		// into thinking HFE widgets require a Pro upgrade and skipping them.
		if ( is_array( $result ) && ! is_wp_error( $result ) ) {
			$result = self::strip_pro_alternatives( $result );
		}

		if ( is_wp_error( $result ) ) {
			$data   = $result->get_error_data();
			$status = isset( $data['status'] ) ? $data['status'] : 500;

			return new \WP_REST_Response(
				[
					'error'   => true,
					'code'    => $result->get_error_code(),
					'message' => $result->get_error_message(),
				],
				$status
			);
		}

		return $result;
	}

	/**
	 * Check permission for the wildcard execute route.
	 *
	 * Looks up the handler by Angie ID and delegates to its permission_callback.
	 *
	 * @param WP_REST_Request $request Request with ability_id parameter.
	 * @return bool|WP_Error Whether the request is allowed.
	 */
	public function check_execute_permission( $request ) {
		// Capability floor: never expose the execute surface to roles below
		// content editors (e.g. Subscribers), regardless of per-handler callbacks.
		if ( ! current_user_can( 'edit_posts' ) ) {
			return false;
		}

		// These routes are only ever called by the in-editor Angie bridge over
		// cookie auth, so require a valid REST nonce (defence in depth alongside
		// core's cookie nonce check, and consistent with the settings REST API).
		$nonce_check = $this->verify_rest_nonce( $request );

		if ( is_wp_error( $nonce_check ) ) {
			return $nonce_check;
		}

		$angie_id     = sanitize_text_field( $request->get_param( 'ability_id' ) );
		$handler_name = $this->angie_id_to_handler_name( $angie_id );

		if ( ! $handler_name ) {
			// Unknown ability ID — let handle_execute return a 404 without running anything.
			return true;
		}

		$handler = $this->registry->get_handler( $handler_name );

		if ( ! $handler ) {
			return true;
		}

		$args     = $handler->get_registration_args();
		$callback = $args['permission_callback'] ?? null;

		if ( is_callable( $callback ) ) {
			return call_user_func( $callback );
		}

		// Fail closed: a handler with no permission_callback is a bug, not a
		// reason to allow execution. Deny rather than fall back to "logged in".
		return false;
	}

	/**
	 * Permission check for the abilities-list endpoint.
	 *
	 * The tool schema describes everything the AI can do, so it should not be
	 * enumerable by roles that cannot use any of it. Require a content-editing
	 * capability rather than mere authentication.
	 *
	 * @since 2.9.0
	 *
	 * @return bool Whether the current user may list abilities.
	 */
	public function check_list_permission( $request = null ) {
		if ( ! current_user_can( 'edit_posts' ) ) {
			return false;
		}

		return $this->verify_rest_nonce( $request );
	}

	/**
	 * Verify the REST nonce for the cookie-authenticated Angie routes.
	 *
	 * These endpoints are only called by the in-editor Angie bridge (cookie
	 * auth + X-WP-Nonce), so a valid wp_rest nonce is required. Mirrors the
	 * settings REST API and adds defence in depth over core's cookie check.
	 *
	 * @since 2.9.0
	 *
	 * @param WP_REST_Request|null $request Request object.
	 * @return true|WP_Error True when valid, WP_Error (403) otherwise.
	 */
	private function verify_rest_nonce( $request ) {
		$nonce = ( $request instanceof \WP_REST_Request ) ? $request->get_header( 'X-WP-Nonce' ) : '';

		if ( ! wp_verify_nonce( $nonce, 'wp_rest' ) ) {
			return new \WP_Error(
				'hfe_rest_invalid_nonce',
				__( 'Invalid or missing nonce.', 'header-footer-elementor' ),
				[ 'status' => 403 ]
			);
		}

		return true;
	}

	/**
	 * Decode stringified JSON params from Angie tool calls.
	 *
	 * Angie's AI sometimes passes complex values (arrays, objects) as JSON strings
	 * rather than native types. This detects and decodes them so PHP handlers
	 * receive proper arrays/objects.
	 *
	 * @param array $params Request parameters.
	 * @return array Parameters with decoded JSON strings.
	 */
	private static function decode_stringified_params( $params ) {
		foreach ( $params as $key => $value ) {
			if ( ! is_string( $value ) ) {
				continue;
			}

			// Only attempt to decode strings that look like JSON arrays/objects.
			$trimmed = trim( $value );

			if ( strlen( $trimmed ) > 2 && ( '[' === $trimmed[0] || '{' === $trimmed[0] ) ) {
				$decoded = json_decode( $trimmed, true );

				if ( JSON_ERROR_NONE === json_last_error() && is_array( $decoded ) ) {
					$params[ $key ] = $decoded;
				}
			}
		}

		return $params;
	}

	/**
	 * Strip pro_alternative from tool responses for Angie.
	 *
	 * The pro_alternative field in widget lists confuses Angie's AI into
	 * thinking HFE widgets require Pro and skipping them. This is only
	 * stripped for Angie; MCP Adapter responses keep the field for upsells.
	 *
	 * @param array $result Tool execution result.
	 * @return array Result with pro_alternative removed.
	 */
	private static function strip_pro_alternatives( $result ) {
		// Handle array of widget objects (from list-widget-types).
		if ( isset( $result[0] ) && is_array( $result[0] ) ) {
			foreach ( $result as &$item ) {
				if ( is_array( $item ) ) {
					unset( $item['pro_alternative'] );
				}
			}
			unset( $item );
		}

		return $result;
	}
}
