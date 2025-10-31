<?php
namespace BP\DealerSuite\Integration\TerraWallet;

class TerraWalletAdapter {
    public function getBalance( int $userId ): float {
        if ( function_exists( 'terra_wallet_get_balance' ) ) {
            return (float) terra_wallet_get_balance( $userId );
        }

        return (float) get_user_meta( $userId, '_bp_terra_wallet_balance', true );
    }

    public function credit( int $userId, float $amount, string $note = '' ): void {
        if ( function_exists( 'terra_wallet_credit' ) ) {
            terra_wallet_credit( $userId, $amount, $note );
            return;
        }

        $balance = $this->getBalance( $userId );
        update_user_meta( $userId, '_bp_terra_wallet_balance', $balance + $amount );
    }

    public function registerHooks( callable $handler ): void {
        if ( has_action( 'terra_wallet_deposit_completed' ) ) {
            add_action( 'terra_wallet_deposit_completed', $handler, 10, 2 );
        }
    }
}
