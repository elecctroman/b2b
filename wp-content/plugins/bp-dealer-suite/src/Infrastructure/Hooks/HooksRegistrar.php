<?php
namespace BP\DealerSuite\Infrastructure\Hooks;

use BP\DealerSuite\Infrastructure\Loader\ServiceContainer;
use BP\DealerSuite\Domain\Service\PricingService;
use BP\DealerSuite\Application\UseCase\RecomputeDealerLevel;
use BP\DealerSuite\Application\UseCase\ApplyDepositBonus;

class HooksRegistrar {
    public function __construct(
        private ServiceContainer $container,
        private PricingService $pricing,
        private RecomputeDealerLevel $recomputeLevel,
        private ApplyDepositBonus $applyBonus,
    ) {
    }

    public function register(): void {
        add_action( 'init', [ $this, 'registerRoles' ] );
        add_action( 'woocommerce_cart_calculate_fees', [ $this, 'applyCartDiscount' ] );
        add_filter( 'woocommerce_product_get_price', [ $this, 'filterPrice' ], 20, 2 );
        add_filter( 'woocommerce_product_variation_get_price', [ $this, 'filterPrice' ], 20, 2 );
        add_action( 'woocommerce_order_status_completed', [ $this, 'handleOrderCompleted' ] );
        add_action( 'terra_wallet_deposit_completed', [ $this, 'handleDepositBonus' ], 10, 2 );
    }

    public function registerRoles(): void {
        add_role( 'dealer', __( 'Dealer', 'bp-dealer-suite' ), [
            'read'              => true,
            'bp_dealer_view'    => true,
            'bp_dealer_use_api' => true,
            'bp_dealer_view_stats' => true,
        ] );

        $admin = get_role( 'administrator' );

        if ( $admin ) {
            $admin->add_cap( 'manage_bp_dealer' );
            $admin->add_cap( 'manage_bp_discounts' );
            $admin->add_cap( 'manage_bp_bonus' );
            $admin->add_cap( 'manage_bp_settings' );
        }
    }

    public function applyCartDiscount(): void {
        if ( ! is_user_logged_in() ) {
            return;
        }

        $userId      = get_current_user_id();
        if ( ! function_exists( 'WC' ) ) {
            return;
        }

        $cart        = WC()->cart;
        $discount    = 0.0;

        foreach ( $cart->get_cart() as $item ) {
            $productId    = $item['product_id'];
            $product      = $item['data'];
            $salePrice    = $product ? (float) $product->get_price() : (float) $item['line_total'];
            $analysis     = $this->pricing->determineEffectivePrice( $productId, $salePrice, $userId );
            $eligibleRate = $analysis['discount_rate'];

            if ( ! empty( $analysis['blocked'] ) ) {
                wc_add_notice( __( 'Dealer discount blocked due to loss protection.', 'bp-dealer-suite' ), 'error' );
                do_action( 'bp_dealer_before_order_blocked', $item, $analysis );
                return;
            }

            if ( $eligibleRate <= 0 ) {
                continue;
            }

            $discount += $salePrice * $item['quantity'] * $eligibleRate;
        }

        if ( $discount > 0 ) {
            $cart->add_fee( __( 'Dealer Discount', 'bp-dealer-suite' ), -1 * $discount );
        }
    }

    public function filterPrice( $price, $product ) {
        if ( ! is_user_logged_in() ) {
            return $price;
        }

        $userId = get_current_user_id();
        $sale   = (float) $price;

        if ( $sale <= 0 ) {
            return $price;
        }

        $analysis = $this->pricing->determineEffectivePrice( $product->get_id(), $sale, $userId );

        if ( ! empty( $analysis['blocked'] ) || ! empty( $analysis['requires_approval'] ) ) {
            return $price;
        }

        return $analysis['effective_price'];
    }

    public function handleOrderCompleted( $orderId ): void {
        $order = wc_get_order( $orderId );

        if ( ! $order ) {
            return;
        }

        $userId = $order->get_user_id();

        if ( ! $userId ) {
            return;
        }

        $this->recomputeLevel->execute( $userId );
    }

    public function handleDepositBonus( int $userId, array $deposit ): void {
        $amount    = (float) ( $deposit['amount'] ?? 0 );
        $reference = (string) ( $deposit['reference'] ?? uniqid( 'deposit_', true ) );

        if ( $amount <= 0 ) {
            return;
        }

        $this->applyBonus->execute( $userId, $amount, $reference );
    }
}
