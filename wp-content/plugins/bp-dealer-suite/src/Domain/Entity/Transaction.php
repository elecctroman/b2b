<?php
namespace BP\DealerSuite\Domain\Entity;

class Transaction {
    public function __construct(
        private int $id,
        private int $userId,
        private string $type,
        private float $amount,
        private array $meta,
        private string $status
    ) {
    }

    public function id(): int {
        return $this->id;
    }

    public function userId(): int {
        return $this->userId;
    }

    public function type(): string {
        return $this->type;
    }

    public function amount(): float {
        return $this->amount;
    }

    public function meta(): array {
        return $this->meta;
    }

    public function status(): string {
        return $this->status;
    }
}
