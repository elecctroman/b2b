<?php
namespace BP\DealerSuite\Domain\Entity;

class BonusTier {
    public function __construct(
        private int $id,
        private float $minDeposit,
        private float $bonusRate,
        private bool $active
    ) {
    }

    public function id(): int {
        return $this->id;
    }

    public function minDeposit(): float {
        return $this->minDeposit;
    }

    public function bonusRate(): float {
        return $this->bonusRate;
    }

    public function isActive(): bool {
        return $this->active;
    }
}
