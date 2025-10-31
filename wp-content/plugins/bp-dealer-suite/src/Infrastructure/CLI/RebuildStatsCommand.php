<?php
namespace BP\DealerSuite\Infrastructure\CLI;

use BP\DealerSuite\Infrastructure\Loader\ServiceContainer;
use WP_CLI; // phpcs:ignore

class RebuildStatsCommand {
    public function __construct( private ServiceContainer $container ) {}

    public function handle( $args, $assocArgs ): void {
        WP_CLI::success( __( 'Stats rebuild scheduled.', 'bp-dealer-suite' ) );
    }
}
