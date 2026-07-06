<?php
/**
 * REST API Wishlist Products controller class.
 *
 * @package YITH\Wishlist\RestApi
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'YITH_WCWL_REST_V1_Products_Controller' ) ) {
	/**
	 * REST API Wishlist Products controller class.
	 *
	 * @package YITH\Wishlist\RestApi
	 */
	class YITH_WCWL_Rest_V1_Products_Controller extends YITH_WCWL_Rest_V1_Controller {

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
		protected $rest_base = 'products';

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
						'callback'            => array( $this, 'get_products_data' ),
						'permission_callback' => array( $this, 'get_items_permissions_check' ),
						'args'                => array(
							'product_ids' => array(
								'description' => _x( 'The list of product ids for which data is being requested.', '[REST-API] The schema field description', 'yith-woocommerce-wishlist' ),
								'type'        => 'array',
								'required'    => true,
							),
						),
					),
					'schema' => array( $this, 'get_public_item_schema' ),
				)
			);

			register_rest_route(
				$this->namespace,
				'/' . $this->rest_base . '/(?P<product_id>[\d]+)',
				array(
					'args'   => array(
						'product_id' => array(
							'description' => _x( 'The unique identifier for the product.', '[REST-API] The schema field description', 'yith-woocommerce-wishlist' ),
							'type'        => 'integer',
							'required'    => true,
						),
					),
					array(
						'methods'             => WP_REST_Server::READABLE,
						'callback'            => array( $this, 'get_product_data' ),
						'permission_callback' => array( $this, 'get_item_permissions_check' ),
						'args'                => $this->get_endpoint_args_for_item_schema(),
					),
					'schema' => array( $this, 'get_public_item_schema' ),
				)
			);
		}

		/**
		 * Get wishlist data related to multiple products
		 *
		 * @param WP_REST_Request $request The rest request.
		 *
		 * @return WP_Error|WP_HTTP_Response|WP_REST_Response
		 */
		public function get_products_data( $request ) {
			$args = $request->get_params();

			$products_data = array();

			if ( ! empty( $args['product_ids'] ) ) {
				$products_data = $this->prepare_products_for_rest( $args['product_ids'] );
			}

			return rest_ensure_response( $products_data );
		}

		/**
		 * Get wishlist data related to a single products
		 *
		 * @param WP_REST_Request $request The rest request.
		 *
		 * @return WP_Error|WP_HTTP_Response|WP_REST_Response
		 */
		public function get_product_data( $request ) {
			$args = $request->get_params();

			$product_id = $args['product_id'];

			$response = $this->prepare_product_for_rest( $product_id );

			return rest_ensure_response( $response );
		}

		/**
		 * Check if a given request has access to read a single product.
		 *
		 * @param  \WP_REST_Request $request Full details about the request.
		 * @return \WP_Error|boolean
		 */
		public function get_item_permissions_check( $request ) {
			$product_id = absint( $request->get_param( 'product_id' ) );
			$product    = wc_get_product( $product_id );

			if ( ! $product || 'publish' !== $product->get_status() ) {
				return new \WP_Error(
					'woocommerce_rest_cannot_view',
					sprintf(
						/* translators: %d: product ID */
						__( 'Sorry, the product #%d is not accessible.', 'yith-woocommerce-wishlist' ),
						$product_id
					),
					array( 'status' => 403 )
				);
			}

			return true;
		}

		/**
		 * Check if a given request has access to read items.
		 *
		 * @param  \WP_REST_Request $request Full details about the request.
		 * @return \WP_Error|boolean
		 */
		public function get_items_permissions_check( $request ) {
			$product_ids = $request->get_param( 'product_ids' );

			if ( empty( $product_ids ) || ! is_array( $product_ids ) ) {
				return new \WP_Error(
					'woocommerce_rest_missing_product_ids',
					__( 'Please provide a valid list of product IDs.', 'yith-woocommerce-wishlist' ),
					array( 'status' => 400 )
				);
			}

			foreach ( $product_ids as $product_id ) {
				$product = wc_get_product( absint( $product_id ) );

				if ( ! $product || 'publish' !== $product->get_status() ) {
					return new \WP_Error(
						'woocommerce_rest_cannot_view',
						sprintf(
							/* translators: %d: product ID */
							__( 'Sorry, the product #%d is not accessible.', 'yith-woocommerce-wishlist' ),
							absint( $product_id )
						),
						array( 'status' => 403 )
					);
				}
			}

			return true;
		}
	}
}
