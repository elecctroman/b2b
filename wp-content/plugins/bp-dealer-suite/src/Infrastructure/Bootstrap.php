<?php
namespace BP\DealerSuite\Infrastructure;

use BP\DealerSuite\Infrastructure\Loader\ServiceContainer;
use BP\DealerSuite\Infrastructure\Hooks\HooksRegistrar;
use BP\DealerSuite\Infrastructure\Settings\SettingsRegistrar;
use BP\DealerSuite\Infrastructure\CLI\CommandRegistrar;
use BP\DealerSuite\Infrastructure\REST\RoutesRegistrar;
use BP\DealerSuite\Infrastructure\Cron\CronRegistrar;
use BP\DealerSuite\Persistence\WPDB\TableManager;
use BP\DealerSuite\Support\LoggerFactory;
use BP\DealerSuite\Support\OptionRepository;
use BP\DealerSuite\Integration\TerraWallet\TerraWalletAdapter;
use BP\DealerSuite\Domain\Service\PricingService;
use BP\DealerSuite\Domain\Service\LossProtectionPolicy;
use BP\DealerSuite\Persistence\WPDB\DealerLevelRepository;
use BP\DealerSuite\Persistence\WPDB\DealerProfileRepository;
use BP\DealerSuite\Persistence\WPDB\BonusTierRepository;
use BP\DealerSuite\Persistence\WPDB\CommissionRuleRepository;
use BP\DealerSuite\Persistence\WPDB\TransactionRepository;
use BP\DealerSuite\Persistence\WPDB\StatsSnapshotRepository;
use BP\DealerSuite\Domain\Service\DealerLevelEvaluator;
use BP\DealerSuite\Application\UseCase\RecomputeDealerLevel;
use BP\DealerSuite\Application\UseCase\ApplyDepositBonus;
use BP\DealerSuite\Support\MoneyFormatter;
use BP\DealerSuite\Infrastructure\Admin\MyAccountEndpoint;
use Psr\Log\LoggerInterface;

class Bootstrap {
    private static ?self $instance = null;

    private ServiceContainer $container;

    private function __construct() {
        $this->container = new ServiceContainer();
    }

    public static function instance(): self {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function init(): void {
        $this->register_services();
        $this->container->get( HooksRegistrar::class )->register();
        $this->container->get( SettingsRegistrar::class )->register();
        $this->container->get( RoutesRegistrar::class )->register();
        $this->container->get( CommandRegistrar::class )->register();
        $this->container->get( CronRegistrar::class )->register();
        $this->container->get( MyAccountEndpoint::class )->register();
    }

    public function activate(): void {
        $this->register_services();
        /** @var TableManager $table_manager */
        $table_manager = $this->container->get( TableManager::class );
        $table_manager->install();

        $this->container->get( MyAccountEndpoint::class )->registerEndpoint();
        flush_rewrite_rules();
    }

    public function deactivate(): void {
        // Placeholder for deactivation logic (cron cleanup etc.).
        flush_rewrite_rules();
    }

    private function register_services(): void {
        if ( $this->container->has( ServiceContainer::class ) ) {
            return;
        }

        $this->container->set( ServiceContainer::class, $this->container );

        $this->container->set( LoggerInterface::class, static fn () => LoggerFactory::create() );
        $this->container->set( OptionRepository::class, static fn () => new OptionRepository() );
        $this->container->set( TableManager::class, static fn () => new TableManager() );
        $this->container->set( MoneyFormatter::class, static fn () => new MoneyFormatter() );
        $this->container->set( TerraWalletAdapter::class, static fn () => new TerraWalletAdapter() );

        $this->container->set( DealerLevelRepository::class, static fn () => new DealerLevelRepository() );
        $this->container->set( DealerProfileRepository::class, static fn () => new DealerProfileRepository() );
        $this->container->set( BonusTierRepository::class, static fn () => new BonusTierRepository() );
        $this->container->set( CommissionRuleRepository::class, static fn () => new CommissionRuleRepository() );
        $this->container->set( TransactionRepository::class, static fn () => new TransactionRepository() );
        $this->container->set( StatsSnapshotRepository::class, static fn () => new StatsSnapshotRepository() );

        $this->container->set( LossProtectionPolicy::class, function () {
            $options = $this->container->get( OptionRepository::class );
            return new LossProtectionPolicy( $options );
        } );

        $this->container->set( PricingService::class, function () {
            return new PricingService(
                $this->container->get( LossProtectionPolicy::class ),
                $this->container->get( DealerLevelRepository::class ),
                $this->container->get( DealerProfileRepository::class ),
                $this->container->get( CommissionRuleRepository::class ),
                $this->container->get( OptionRepository::class )
            );
        } );

        $this->container->set( DealerLevelEvaluator::class, function () {
            return new DealerLevelEvaluator(
                $this->container->get( DealerLevelRepository::class )
            );
        } );

        $this->container->set( RecomputeDealerLevel::class, function () {
            return new RecomputeDealerLevel(
                $this->container->get( DealerProfileRepository::class ),
                $this->container->get( DealerLevelEvaluator::class ),
                $this->container->get( DealerLevelRepository::class )
            );
        } );

        $this->container->set( ApplyDepositBonus::class, function () {
            return new ApplyDepositBonus(
                $this->container->get( TerraWalletAdapter::class ),
                $this->container->get( BonusTierRepository::class ),
                $this->container->get( TransactionRepository::class ),
                $this->container->get( OptionRepository::class )
            );
        } );

        $this->container->set( HooksRegistrar::class, function () {
            return new HooksRegistrar(
                $this->container,
                $this->container->get( PricingService::class ),
                $this->container->get( RecomputeDealerLevel::class ),
                $this->container->get( ApplyDepositBonus::class ),
            );
        } );

        $this->container->set( SettingsRegistrar::class, function () {
            return new SettingsRegistrar( $this->container );
        } );

        $this->container->set( RoutesRegistrar::class, function () {
            return new RoutesRegistrar( $this->container );
        } );

        $this->container->set( CommandRegistrar::class, function () {
            return new CommandRegistrar( $this->container );
        } );

        $this->container->set( CronRegistrar::class, function () {
            return new CronRegistrar( $this->container );
        } );

        $this->container->set( MyAccountEndpoint::class, function () {
            return new MyAccountEndpoint(
                $this->container->get( DealerProfileRepository::class ),
                $this->container->get( DealerLevelRepository::class ),
                $this->container->get( TerraWalletAdapter::class )
            );
        } );
    }
}
