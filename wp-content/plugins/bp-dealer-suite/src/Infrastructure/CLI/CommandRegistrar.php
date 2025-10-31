<?php
namespace BP\DealerSuite\Infrastructure\CLI;

use BP\DealerSuite\Infrastructure\Loader\ServiceContainer;

class CommandRegistrar {
    public function __construct( private ServiceContainer $container ) {}

    public function register(): void {
        if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
            return;
        }

        \WP_CLI::add_command( 'bp-dealer import-levels', [ new ImportLevelsCommand( $this->container ), 'handle' ] );
        \WP_CLI::add_command( 'bp-dealer import-bonus', [ new ImportBonusCommand( $this->container ), 'handle' ] );
        \WP_CLI::add_command( 'bp-dealer rebuild-stats', [ new RebuildStatsCommand( $this->container ), 'handle' ] );
        \WP_CLI::add_command( 'bp-dealer seed-test-data', [ new SeedTestDataCommand( $this->container ), 'handle' ] );
    }
}
