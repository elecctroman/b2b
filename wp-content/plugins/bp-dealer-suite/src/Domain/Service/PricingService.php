<?php
namespace BP\DealerSuite\Domain\Service;

use BP\DealerSuite\Persistence\WPDB\DealerLevelRepository;
use BP\DealerSuite\Persistence\WPDB\DealerProfileRepository;
use BP\DealerSuite\Persistence\WPDB\CommissionRuleRepository;
use BP\DealerSuite\Support\OptionRepository;

class PricingService {
    public function __construct(
        private LossProtectionPolicy $lossPolicy,
        private DealerLevelRepository $levels,
        private DealerProfileRepository $profiles,
        private CommissionRuleRepository $commissions,
        private OptionRepository $options
    ) {
    }

    public function resolveDealerDiscountRate( int $userId ): float {
        $profile = $this->profiles->findByUserId( $userId );
        $rate    = 0.0;

        if ( $profile && null !== $profile->customDiscountRate() ) {
            $rate = $profile->customDiscountRate();
        } elseif ( $profile ) {
            $level = $this->levels->findByCode( $profile->levelCode() );
            if ( $level && $level->isActive() ) {
                $rate = $level->discountRate();
            }
        }

        $filteredRate = apply_filters( 'bp_dealer_calculated_discount_rate', $rate, $userId, $profile );

        return min( 1.0, max( 0.0, $filteredRate ) );
    }

    public function resolveProductCost( int $productId, float $salePrice ): float {
        $rule = $this->commissions->findByProductId( $productId );

        if ( $rule && null !== $rule->supplierCost() ) {
            $cost = $rule->supplierCost();
        } else {
            $commissionRate = $rule && null !== $rule->commissionRate()
                ? $rule->commissionRate()
                : (float) $this->options->get( 'default_commission_rate', 0.5 );
            $cost           = $this->lossPolicy->costFromSaleAndCommission( $salePrice, $commissionRate );
        }

        return max( 0.0, $cost );
    }

    public function determineEffectivePrice( int $productId, float $salePrice, int $userId ): array {
        $discountRate   = $this->resolveDealerDiscountRate( $userId );
        $effectivePrice = $this->lossPolicy->effectivePriceAfterDiscount( $salePrice, $discountRate );
        $cost           = $this->resolveProductCost( $productId, $salePrice );
        $minMarginRate  = $this->lossPolicy->minMarginRate();

        $protected = $this->lossPolicy->isLossProtected( $effectivePrice, $cost, $minMarginRate );

        if ( $protected ) {
            return [
                'discount_rate'   => $discountRate,
                'effective_price' => $effectivePrice,
                'cost'            => $cost,
                'adjusted'        => false,
            ];
        }

        $mode = $this->lossPolicy->getMode();

        if ( 'auto_adjust_discount' === $mode ) {
            $maxRate        = $this->lossPolicy->autoTrimDiscountRate( $salePrice, $cost, $minMarginRate );
            $effectivePrice = $this->lossPolicy->effectivePriceAfterDiscount( $salePrice, $maxRate );

            return [
                'discount_rate'   => $maxRate,
                'effective_price' => $effectivePrice,
                'cost'            => $cost,
                'adjusted'        => true,
            ];
        }

        if ( 'block_order' === $mode ) {
            return [
                'discount_rate'   => $discountRate,
                'effective_price' => $effectivePrice,
                'cost'            => $cost,
                'adjusted'        => false,
                'blocked'         => true,
            ];
        }

        return [
            'discount_rate'   => $discountRate,
            'effective_price' => $effectivePrice,
            'cost'            => $cost,
            'adjusted'        => false,
            'requires_approval' => true,
        ];
    }
}
