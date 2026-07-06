<?php
/**
 * REST API Wishlist Lists controller class.
 *
 * @package YITH\Wishlist\RestApi
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'YITH_WCWL_Rest_V1_Lists_Controller' ) ) {
	/**
	 * REST API Wishlist Lists controller class.
	 *
	 * @package YITH\Wishlist\RestApi
	 */
	class YITH_WCWL_Rest_V1_Lists_Controller extends YITH_WCWL_Rest_V1_Controller {

		/**
		 * Endpoint namespace.
		 *
		 * @var string
		 */
		protected $namespace = 'yith/wishlist/v1';

		/**
		 * Route base.
		 *
		 * @var string
		 */
		protected $rest_base = 'lists';

		/**
		 * Register the routes.
		 */
		public function register_routes() {
			register_rest_route(
				$this->namespace,
				'/' . $this->rest_base,
				array(
					array(
						'methods'             => WP_REST_Server::READABLE,
						'callback'            => array( $this, 'get_lists' ),
						'permission_callback' => array( $this, 'get_lists_permissions_check' ),
						'args'                => $this->get_endpoint_args_for_item_schema(),
					),
					'schema' => array( $this, 'get_public_item_schema' ),
				)
			);
			register_rest_route(
				$this->namespace,
				'/' . $this->rest_base,
				array(
					array(
						'methods'             => WP_REST_Server::CREATABLE,
						'callback'            => array( $this, 'create_list' ),
						'permission_callback' => array( $this, 'create_list_permissions_check' ),
						'args'                => array(
							'wishlist_name'       => array(
								'description' => _x( 'The wishlist name.', '[REST-API] The schema field description', 'yith-woocommerce-wishlist' ),
								'type'        => 'string',
								'required'    => true,
							),
							'wishlist_visibility' => array(
								'description' => _x( 'The wishlist visibility value.', '[REST-API] The schema field description', 'yith-woocommerce-wishlist' ),
								'type'        => 'integer',
								'required'    => true,
							),
							'user_id'             => array(
								'description' => _x( 'The unique identifier for the user.', '[REST-API] The schema field description', 'yith-woocommerce-wishlist' ),
								'type'        => 'integer',
							),
							'session_id'          => array(
								'description' => _x( 'The unique identifier for the session.', '[REST-API] The schema field description', 'yith-woocommerce-wishlist' ),
								'type'        => 'string',
							),
						),
					),
					'schema' => array( $this, 'get_public_item_schema' ),
				)
			);
		}


		/**
		 * Get lists
		 *
		 * @return WP_Error|WP_HTTP_Response|WP_REST_Response
		 */
		public function get_lists() {
			$response = array(
				'lists' => $this->prepare_wishlists_for_rest( YITH_WCWL_Wishlists::get_instance()->get_current_user_wishlists() ),
			);

			return rest_ensure_response( $response );
		}

		/**
		 * Check if the current user can view wishlists.
		 *
		 * Allows access only if the user is logged in or has an active session.
		 *
		 * @param \WP_REST_Request $request The rest request.
		 * @return true|\WP_Error
		 */
		public function get_lists_permissions_check( $request ) {
			if ( ! is_user_logged_in() && ! YITH_WCWL_Session()->maybe_get_session_id() ) {
				return new \WP_Error(
					'wishlist_rest_cannot_view',
					__( 'Sorry, you must be logged in or have an active session to view wishlists.', 'yith-woocommerce-wishlist' ),
					array( 'status' => 401 )
				);
			}

			return true;
		}

		/**
		 * Create list
		 *
		 * @param \WP_REST_Request $request The rest request.
		 * @return WP_Error|WP_HTTP_Response|WP_REST_Response
		 */
		public function create_list( $request ) {
			$args = $request->get_params();

			try {
				$wishlist = YITH_WCWL_Wishlists::get_instance()->create( $args );
				$response = array(
					'wishlist_data' => $this->prepare_wishlist_for_rest( $wishlist ),
				);
			} catch ( YITH_WCWL_Exception $e ) {
				$response = array(
					'success' => false,
					'message' => $e->getMessage(),
				);
			}

			return rest_ensure_response( $response );
		}

		/**
		 * Check if the current user can create a wishlist.
		 *
		 * Prevents unauthenticated users from creating wishlists for arbitrary user IDs.
		 *
		 * @param \WP_REST_Request $request The rest request.
		 * @return true|\WP_Error
		 */
		public function create_list_permissions_check( $request ) {
			$user_id = $request->get_param( 'user_id' );

			// If a user_id is provided, only allow if the current user matches or is an admin.
			if ( $user_id ) {
				if ( ! is_user_logged_in() ) {
					return new \WP_Error(
						'wishlist_rest_cannot_create',
						__( 'Sorry, you must be logged in to create a wishlist for a user.', 'yith-woocommerce-wishlist' ),
						array( 'status' => 401 )
					);
				}

				if ( (int) get_current_user_id() !== (int) $user_id && ! current_user_can( 'manage_woocommerce' ) ) { // phpcs:ignore WordPress.WP.Capabilities.Unknown
					return new \WP_Error(
						'wishlist_rest_cannot_create',
						__( 'Sorry, you are not allowed to create wishlists for other users.', 'yith-woocommerce-wishlist' ),
						array( 'status' => 403 )
					);
				}
			}

			return true;
		}
	}
}
