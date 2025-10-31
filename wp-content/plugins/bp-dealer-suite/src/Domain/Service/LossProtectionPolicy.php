<?php
namespace BP\DealerSuite\Domain\Service;

use BP\DealerSuite\Support\OptionRepository;

class LossProtectionPolicy {
    public function __construct( private OptionRepository $options ) {
    }

    public function isLossProtectionEnabled(): bool {
        return (bool) $this->options->get( 'loss_protection_enabled', true );
    }

    public function getMode(): string {
        return (string) $this->options->get( 'loss_protection_mode', 'auto_adjust_discount' );
    }

    public function minMarginRate( ?float $override = null ): float {
        $default = (float) $this->options->get( 'min_margin_rate', 0.1 );
        $min     = $override ?? $default;

        /**
         * Filter the minimum margin rate used for loss protection.
         *
         * @param float $min
         */
        return (float) apply_filters( 'bp_dealer_min_margin_rate', $min );
    }

    public function costFromSaleAndCommission( float $salePrice, float $commissionRate ): float {
        if ( $commissionRate <= -1 ) {
            $commissionRate = 0.0;
        }

        $cost = $salePrice / ( 1 + $commissionRate );

        /**
         * Filter the resolved cost before returning.
         */
        return (float) apply_filters( 'bp_dealer_commission_cost_resolved', $cost, $salePrice, $commissionRate );
    }

    public function effectivePriceAfterDiscount( float $salePrice, float $discountRate ): float {
        $discountRate = max( 0.0, min( 1.0, $discountRate ) );
        $price        = $salePrice * ( 1 - $discountRate );

        return (float) apply_filters( 'bp_dealer_effective_price', $price, $salePrice, $discountRate );
    }

    public function isLossProtected( float $effectivePrice, float $cost, float $minMarginRate ): bool {
        $minMarginRate = max( 0.0, $minMarginRate );
        $required      = $cost * ( 1 + $minMarginRate );

        return $effectivePrice >= $required;
    }

    public function autoTrimDiscountRate( float $salePrice, float $cost, float $minMarginRate ): float {
        if ( $salePrice <= 0 ) {
            return 0.0;
        }

        $requiredPrice = $cost * ( 1 + $minMarginRate );
        $maxDiscount   = 1 - ( $requiredPrice / $salePrice );

        if ( $maxDiscount < 0 ) {
            $maxDiscount = 0.0;
        }

        return min( 1.0, max( 0.0, $maxDiscount ) );
    }
}
