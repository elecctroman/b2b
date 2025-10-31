<?php
namespace BP\DealerSuite\Infrastructure\REST\Controller;

use WP_REST_Server;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

class OrdersController extends AbstractController {
    public function register_routes(): void {
        register_rest_route( $this->namespace, '/orders', [
            [
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => [ $this, 'createOrder' ],
                'permission_callback' => [ $this, 'permissionCheck' ],
            ],
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'listOrders' ],
                'permission_callback' => [ $this, 'permissionCheck' ],
            ],
        ] );

        register_rest_route( $this->namespace, '/orders/(?P<id>\d+)', [
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => [ $this, 'getOrder' ],
            'permission_callback' => [ $this, 'permissionCheck' ],
        ] );
    }

    public function createOrder( WP_REST_Request $request ): WP_REST_Response|WP_Error {
        $body = $request->get_json_params();

        if ( empty( $body['idempotency_key'] ) ) {
            return new WP_Error( 'bp_dealer_error_idempotency', __( 'Idempotency key required.', 'bp-dealer-suite' ), [ 'status' => 400 ] );
        }

        $key = sanitize_text_field( $body['idempotency_key'] );

        if ( get_transient( 'bp_dealer_order_' . $key ) ) {
            return new WP_Error( 'bp_dealer_error_duplicate', __( 'Order already submitted.', 'bp-dealer-suite' ), [ 'status' => 409 ] );
        }

        $order = wc_create_order();

        foreach ( (array) ( $body['lines'] ?? [] ) as $line ) {
            $productId = absint( $line['product_id'] ?? 0 );
            $quantity  = absint( $line['quantity'] ?? 1 );

            if ( ! $productId ) {
                continue;
            }

            $product = wc_get_product( $productId );

            if ( ! $product ) {
                continue;
            }

            $order->add_product( $product, $quantity );
        }

        $order->calculate_totals();
        $order->update_meta_data( '_bp_dealer_idempotency', $key );
        $order->save();

        set_transient( 'bp_dealer_order_' . $key, $order->get_id(), HOUR_IN_SECONDS );

        return new WP_REST_Response( [
            'id'     => $order->get_id(),
            'status' => $order->get_status(),
            'total'  => $order->get_total(),
        ], 201 );
    }

    public function listOrders( WP_REST_Request $request ): WP_REST_Response {
        $userId = get_current_user_id();
        $query  = new \WC_Order_Query( [
            'customer_id' => $userId,
            'limit'       => 20,
            'orderby'     => 'date',
            'order'       => 'DESC',
        ] );

        $orders = $query->get_orders();
        $items  = [];

        foreach ( $orders as $order ) {
            $items[] = [
                'id'     => $order->get_id(),
                'status' => $order->get_status(),
                'total'  => $order->get_total(),
            ];
        }

        return new WP_REST_Response( [ 'items' => $items ] );
    }

    public function getOrder( WP_REST_Request $request ): WP_REST_Response|WP_Error {
        $orderId = absint( $request['id'] );
        $order   = wc_get_order( $orderId );

        if ( ! $order ) {
            return new WP_Error( 'bp_dealer_error_not_found', __( 'Order not found.', 'bp-dealer-suite' ), [ 'status' => 404 ] );
        }

        return new WP_REST_Response( [
            'id'     => $order->get_id(),
            'status' => $order->get_status(),
            'total'  => $order->get_total(),
            'lines'  => array_map( static function ( $item ) {
                return [
                    'product_id' => $item->get_product_id(),
                    'quantity'   => $item->get_quantity(),
                    'total'      => $item->get_total(),
                ];
            }, $order->get_items() ),
        ] );
    }
}
