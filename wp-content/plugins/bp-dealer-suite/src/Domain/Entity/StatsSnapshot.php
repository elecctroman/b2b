<?php
namespace BP\DealerSuite\Domain\Entity;

class StatsSnapshot {
    public function __construct(
        private string $periodStart,
        private string $periodType,
        private ?int $userId,
        private int $orders,
        private float $revenue,
        private float $discountTotal,
        private float $bonusTotal,
        private float $grossMargin,
        private array $payload
    ) {
    }

    public function periodStart(): string {
        return $this->periodStart;
    }

    public function periodType(): string {
        return $this->periodType;
    }

    public function userId(): ?int {
        return $this->userId;
    }

    public function orders(): int {
        return $this->orders;
    }

    public function revenue(): float {
        return $this->revenue;
    }

    public function discountTotal(): float {
        return $this->discountTotal;
    }

    public function bonusTotal(): float {
        return $this->bonusTotal;
    }

    public function grossMargin(): float {
        return $this->grossMargin;
    }

    public function payload(): array {
        return $this->payload;
    }
}
