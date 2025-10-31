<?php
namespace BP\DealerSuite\Application\UseCase;

use BP\DealerSuite\Persistence\WPDB\DealerProfileRepository;
use BP\DealerSuite\Domain\Service\DealerLevelEvaluator;
use BP\DealerSuite\Persistence\WPDB\DealerLevelRepository;
use WP_User;

class RecomputeDealerLevel {
    public function __construct(
        private DealerProfileRepository $profiles,
        private DealerLevelEvaluator $evaluator,
        private DealerLevelRepository $levels
    ) {
    }

    public function execute( int $userId ): void {
        $profile = $this->profiles->findByUserId( $userId );

        if ( ! $profile ) {
            return;
        }

        $level = $this->evaluator->determineLevel( $profile );

        if ( ! $level ) {
            return;
        }

        $this->profiles->updateLevel( $userId, $level->code() );
    }
}
