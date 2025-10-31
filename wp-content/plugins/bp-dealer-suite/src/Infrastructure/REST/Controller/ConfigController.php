<?php
namespace BP\DealerSuite\Infrastructure\REST\Controller;

use BP\DealerSuite\Persistence\WPDB\DealerLevelRepository;
use BP\DealerSuite\Persistence\WPDB\BonusTierRepository;
use WP_REST_Server;
use WP_REST_Response;
use WP_REST_Request;

class ConfigController extends AbstractController {
    public function register_routes(): void {
        register_rest_route( $this->namespace, '/levels', [
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => [ $this, 'levels' ],
            'permission_callback' => [ $this, 'permissionCheck' ],
        ] );

        register_rest_route( $this->namespace, '/bonus-tiers', [
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => [ $this, 'bonusTiers' ],
            'permission_callback' => [ $this, 'permissionCheck' ],
        ] );
    }

    public function levels( WP_REST_Request $request ): WP_REST_Response {
        $levels = $this->container->get( DealerLevelRepository::class )->allActive();

        return new WP_REST_Response( array_map( static function ( $level ) {
            return [
                'code'          => $level->code(),
                'name'          => $level->name(),
                'discount_rate' => $level->discountRate(),
                'min_spend'     => $level->minSpend(),
                'min_orders'    => $level->minOrders(),
            ];
        }, $levels ) );
    }

    public function bonusTiers( WP_REST_Request $request ): WP_REST_Response {
        $tiers = $this->container->get( BonusTierRepository::class )->allActive();

        return new WP_REST_Response( array_map( static function ( $tier ) {
            return [
                'min_deposit' => $tier->minDeposit(),
                'bonus_rate'  => $tier->bonusRate(),
            ];
        }, $tiers ) );
    }
}
