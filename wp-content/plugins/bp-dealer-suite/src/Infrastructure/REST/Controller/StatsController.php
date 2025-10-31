<?php
namespace BP\DealerSuite\Infrastructure\REST\Controller;

use BP\DealerSuite\Persistence\WPDB\StatsSnapshotRepository;
use WP_REST_Server;
use WP_REST_Request;
use WP_REST_Response;

class StatsController extends AbstractController {
    public function register_routes(): void {
        register_rest_route( $this->namespace, '/stats', [
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => [ $this, 'handleStats' ],
            'permission_callback' => [ $this, 'permissionCheck' ],
            'args'                => [
                'period' => [ 'type' => 'string', 'default' => 'daily' ],
            ],
        ] );
    }

    public function handleStats( WP_REST_Request $request ): WP_REST_Response {
        $period = $request->get_param( 'period' );

        return new WP_REST_Response( [
            'period' => $period,
            'orders' => 0,
            'revenue' => 0,
            'discount_total' => 0,
            'bonus_total' => 0,
            'gross_margin' => 0,
        ] );
    }
}
