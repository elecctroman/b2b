<?php
namespace BP\DealerSuite\Domain\Entity;

class DealerProfile {
    public function __construct(
        private int $userId,
        private string $levelCode,
        private ?float $customDiscountRate,
        private float $lifetimeSpend,
        private int $lifetimeOrders,
        private float $walletBalanceSnapshot
    ) {
    }

    public function userId(): int {
        return $this->userId;
    }

    public function levelCode(): string {
        return $this->levelCode;
    }

    public function customDiscountRate(): ?float {
        return $this->customDiscountRate;
    }

    public function lifetimeSpend(): float {
        return $this->lifetimeSpend;
    }

    public function lifetimeOrders(): int {
        return $this->lifetimeOrders;
    }

    public function walletBalanceSnapshot(): float {
        return $this->walletBalanceSnapshot;
    }
}
