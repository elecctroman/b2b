<?php
namespace BP\DealerSuite\Domain\Entity;

class DealerLevel {
    public function __construct(
        private int $id,
        private string $code,
        private string $name,
        private float $minSpend,
        private int $minOrders,
        private float $discountRate,
        private bool $active,
        private int $priority
    ) {
    }

    public function id(): int {
        return $this->id;
    }

    public function code(): string {
        return $this->code;
    }

    public function name(): string {
        return $this->name;
    }

    public function minSpend(): float {
        return $this->minSpend;
    }

    public function minOrders(): int {
        return $this->minOrders;
    }

    public function discountRate(): float {
        return $this->discountRate;
    }

    public function isActive(): bool {
        return $this->active;
    }

    public function priority(): int {
        return $this->priority;
    }
}
