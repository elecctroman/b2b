<?php
namespace BP\DealerSuite\Infrastructure\REST\Controller;

use BP\DealerSuite\Infrastructure\Loader\ServiceContainer;
use WP_REST_Request;
use WP_Error;

abstract class AbstractController {
    protected string $namespace = 'bp-dealer/v1';

    public function __construct( protected ServiceContainer $container ) {}

    abstract public function register_routes(): void;

    protected function permissionCheck( WP_REST_Request $request ): bool|WP_Error {
        if ( ! is_user_logged_in() ) {
            return new WP_Error( 'bp_dealer_error_auth', __( 'Authentication required.', 'bp-dealer-suite' ), [ 'status' => 401 ] );
        }

        if ( ! current_user_can( 'bp_dealer_use_api' ) && ! current_user_can( 'manage_bp_dealer' ) ) {
            return new WP_Error( 'bp_dealer_error_forbidden', __( 'You are not allowed to access dealer API.', 'bp-dealer-suite' ), [ 'status' => 403 ] );
        }

        return true;
    }
}
