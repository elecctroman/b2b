<?php
namespace BP\DealerSuite\Infrastructure\Cron;

use BP\DealerSuite\Infrastructure\Loader\ServiceContainer;

class CronRegistrar {
    public function __construct( private ServiceContainer $container ) {}

    public function register(): void {
        add_action( 'bp_dealer_rebuild_stats', [ $this, 'rebuildStats' ] );
    }

    public function rebuildStats(): void {
        // Placeholder for stats rebuild implementation.
    }
}
