<?php
namespace BP\DealerSuite\Infrastructure\REST\Controller;

use BP\DealerSuite\Integration\TerraWallet\TerraWalletAdapter;
use WP_REST_Server;
use WP_REST_Request;
use WP_REST_Response;

class WalletController extends AbstractController {
    public function register_routes(): void {
        register_rest_route( $this->namespace, '/wallet/deposit', [
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => [ $this, 'deposit' ],
            'permission_callback' => [ $this, 'permissionCheck' ],
        ] );
    }

    public function deposit( WP_REST_Request $request ): WP_REST_Response {
        $amount = floatval( $request->get_param( 'amount' ) );

        if ( $amount <= 0 ) {
            return new WP_REST_Response( [ 'message' => __( 'Amount must be positive.', 'bp-dealer-suite' ) ], 400 );
        }

        $wallet = $this->container->get( TerraWalletAdapter::class );
        $userId = get_current_user_id();
        $wallet->credit( $userId, $amount, __( 'API deposit', 'bp-dealer-suite' ) );

        return new WP_REST_Response( [
            'user_id'        => $userId,
            'wallet_balance' => $wallet->getBalance( $userId ),
        ], 201 );
    }
}
