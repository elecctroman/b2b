<?php
namespace BP\DealerSuite\Application\UseCase;

use BP\DealerSuite\Integration\TerraWallet\TerraWalletAdapter;
use BP\DealerSuite\Persistence\WPDB\BonusTierRepository;
use BP\DealerSuite\Persistence\WPDB\TransactionRepository;
use BP\DealerSuite\Support\OptionRepository;

class ApplyDepositBonus {
    public function __construct(
        private TerraWalletAdapter $wallet,
        private BonusTierRepository $tiers,
        private TransactionRepository $transactions,
        private OptionRepository $options
    ) {
    }

    public function execute( int $userId, float $amount, string $reference ): void {
        if ( $this->transactions->existsByReference( $reference ) ) {
            return;
        }

        $tier = $this->tiers->matchDeposit( $amount );

        if ( ! $tier ) {
            return;
        }

        $bonusAmount = $amount * $tier->bonusRate();

        $this->transactions->createBonus( $userId, $bonusAmount, [
            'reference' => $reference,
            'deposit'   => $amount,
        ] );

        $this->wallet->credit( $userId, $bonusAmount, __( 'Deposit bonus', 'bp-dealer-suite' ) );

        /**
         * Fires after the deposit bonus is applied.
         *
         * @param int   $userId
         * @param float $bonusAmount
         */
        do_action( 'bp_dealer_after_deposit_bonus_applied', $userId, $bonusAmount, $tier );
    }
}
