<?php
namespace BP\DealerSuite\Infrastructure\REST\Controller;

use BP\DealerSuite\Domain\Service\PricingService;
use WP_REST_Server;
use WP_REST_Request;
use WP_REST_Response;

class ProductsController extends AbstractController {
    public function register_routes(): void {
        register_rest_route( $this->namespace, '/products', [
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => [ $this, 'handleProducts' ],
            'permission_callback' => [ $this, 'permissionCheck' ],
            'args'                => [
                'page'     => [ 'type' => 'integer', 'default' => 1 ],
                'per_page' => [ 'type' => 'integer', 'default' => 10 ],
                'search'   => [ 'type' => 'string' ],
            ],
        ] );
    }

    public function handleProducts( WP_REST_Request $request ): WP_REST_Response {
        $page     = max( 1, (int) $request->get_param( 'page' ) );
        $perPage  = max( 1, min( 100, (int) $request->get_param( 'per_page' ) ) );
        $search   = $request->get_param( 'search' );
        $userId   = get_current_user_id();
        $pricing  = $this->container->get( PricingService::class );

        $args = [
            'limit'  => $perPage,
            'page'   => $page,
            'status' => 'publish',
        ];

        if ( $search ) {
            $args['search'] = sanitize_text_field( $search );
        }

        $products = wc_get_products( $args );

        $items = [];

        foreach ( $products as $product ) {
            $price    = (float) $product->get_price();
            $analysis = $pricing->determineEffectivePrice( $product->get_id(), $price, $userId );

            $items[] = [
                'id'                 => $product->get_id(),
                'name'               => $product->get_name(),
                'price'              => $price,
                'dealer_price'       => $analysis['effective_price'],
                'discount_applied'   => $analysis['discount_rate'],
                'min_margin_guard'   => [
                    'active'                => empty( $analysis['blocked'] ),
                    'adjusted_discount_rate'=> $analysis['discount_rate'],
                ],
                'stock_status'       => $product->get_stock_status(),
            ];
        }

        return new WP_REST_Response( [
            'page'     => $page,
            'per_page' => $perPage,
            'total'    => count( $items ),
            'items'    => $items,
        ] );
    }
}
