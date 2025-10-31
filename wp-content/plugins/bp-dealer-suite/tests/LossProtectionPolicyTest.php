<?php
use PHPUnit\Framework\TestCase;
use BP\DealerSuite\Domain\Service\LossProtectionPolicy;
use BP\DealerSuite\Support\OptionRepository;

class LossProtectionPolicyTest extends TestCase {
    public function testCostFromSaleAndCommission(): void {
        $options = $this->createMock( OptionRepository::class );
        $policy  = new LossProtectionPolicy( $options );

        $this->assertEquals( 100.0, $policy->costFromSaleAndCommission( 150.0, 0.5 ) );
    }

    public function testEffectivePrice(): void {
        $options = $this->createMock( OptionRepository::class );
        $policy  = new LossProtectionPolicy( $options );

        $this->assertEquals( 90.0, $policy->effectivePriceAfterDiscount( 100.0, 0.1 ) );
    }

    public function testAutoTrimDiscount(): void {
        $options = $this->createMock( OptionRepository::class );
        $policy  = new LossProtectionPolicy( $options );

        $rate = $policy->autoTrimDiscountRate( 150.0, 100.0, 0.1 );

        $this->assertEquals( 0.2666666, $rate, '', 0.0001 );
    }
}
