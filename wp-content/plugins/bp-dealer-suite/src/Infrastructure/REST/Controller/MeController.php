<?php
namespace BP\DealerSuite\Infrastructure\REST\Controller;

use BP\DealerSuite\Persistence\WPDB\DealerProfileRepository;
use BP\DealerSuite\Persistence\WPDB\DealerLevelRepository;
use BP\DealerSuite\Domain\Service\PricingService;
use BP\DealerSuite\Integration\TerraWallet\TerraWalletAdapter;
use WP_REST_Server;
use WP_REST_Request;
use WP_REST_Response;

class MeController extends AbstractController {
    public function register_routes(): void {
        register_rest_route( $this->namespace, '/me', [
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => [ $this, 'handleMe' ],
            'permission_callback' => [ $this, 'permissionCheck' ],
        ] );
    }

    public function handleMe( WP_REST_Request $request ): WP_REST_Response {
        $userId   = get_current_user_id();
        $profiles = $this->container->get( DealerProfileRepository::class );
        $levels   = $this->container->get( DealerLevelRepository::class );
        $pricing  = $this->container->get( PricingService::class );
        $wallet   = $this->container->get( TerraWalletAdapter::class );

        $profile = $profiles->findByUserId( $userId );

        if ( ! $profile ) {
            return new WP_REST_Response( [ 'user_id' => $userId, 'message' => __( 'No dealer profile found.', 'bp-dealer-suite' ) ] );
        }

        $level = $levels->findByCode( $profile->levelCode() );

        return new WP_REST_Response( [
            'user_id'              => $userId,
            'level'                => $level ? [
                'code'          => $level->code(),
                'name'          => $level->name(),
                'discount_rate' => $level->discountRate(),
            ] : null,
            'custom_discount_rate' => $profile->customDiscountRate(),
            'resolved_discount_rate' => $pricing->resolveDealerDiscountRate( $userId ),
            'lifetime_spend'       => $profile->lifetimeSpend(),
            'lifetime_orders'      => $profile->lifetimeOrders(),
            'wallet_balance'       => $wallet->getBalance( $userId ),
        ] );
    }
}
