<?php
namespace BP\DealerSuite\Domain\Service;

use BP\DealerSuite\Persistence\WPDB\DealerLevelRepository;
use BP\DealerSuite\Domain\Entity\DealerProfile;
use BP\DealerSuite\Domain\Entity\DealerLevel;

class DealerLevelEvaluator {
    public function __construct( private DealerLevelRepository $levels ) {
    }

    public function determineLevel( DealerProfile $profile ): ?DealerLevel {
        $levels = $this->levels->allActive();

        usort(
            $levels,
            static fn( DealerLevel $a, DealerLevel $b ) => $b->priority() <=> $a->priority()
        );

        foreach ( $levels as $level ) {
            if ( $profile->lifetimeSpend() >= $level->minSpend() && $profile->lifetimeOrders() >= $level->minOrders() ) {
                return $level;
            }
        }

        return null;
    }
}
