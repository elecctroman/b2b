<?php
namespace BP\DealerSuite\Infrastructure\CLI;

use BP\DealerSuite\Infrastructure\Loader\ServiceContainer;
use WP_CLI; // phpcs:ignore

class ImportLevelsCommand {
    public function __construct( private ServiceContainer $container ) {}

    public function handle( $args, $assocArgs ): void {
        WP_CLI::success( __( 'Levels import not implemented in demo.', 'bp-dealer-suite' ) );
    }
}
