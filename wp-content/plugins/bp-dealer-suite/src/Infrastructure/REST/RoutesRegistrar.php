<?php
namespace BP\DealerSuite\Infrastructure\REST;

use BP\DealerSuite\Infrastructure\Loader\ServiceContainer;
use BP\DealerSuite\Infrastructure\REST\Controller\MeController;
use BP\DealerSuite\Infrastructure\REST\Controller\ProductsController;
use BP\DealerSuite\Infrastructure\REST\Controller\OrdersController;
use BP\DealerSuite\Infrastructure\REST\Controller\WalletController;
use BP\DealerSuite\Infrastructure\REST\Controller\StatsController;
use BP\DealerSuite\Infrastructure\REST\Controller\ConfigController;

class RoutesRegistrar {
    public function __construct( private ServiceContainer $container ) {
    }

    public function register(): void {
        add_action( 'rest_api_init', function () {
            $this->container->set( MeController::class, new MeController( $this->container ) );
            $this->container->set( ProductsController::class, new ProductsController( $this->container ) );
            $this->container->set( OrdersController::class, new OrdersController( $this->container ) );
            $this->container->set( WalletController::class, new WalletController( $this->container ) );
            $this->container->set( StatsController::class, new StatsController( $this->container ) );
            $this->container->set( ConfigController::class, new ConfigController( $this->container ) );

            $controllers = [
                MeController::class,
                ProductsController::class,
                OrdersController::class,
                WalletController::class,
                StatsController::class,
                ConfigController::class,
            ];

            foreach ( $controllers as $controller ) {
                $this->container->get( $controller )->register_routes();
            }
        } );
    }
}
