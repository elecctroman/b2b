<?php
namespace BP\DealerSuite\Domain\Entity;

class CommissionRule {
    public function __construct(
        private int $id,
        private int $productId,
        private ?float $supplierCost,
        private ?float $commissionRate,
        private ?float $minMarginRate,
        private ?float $lastCalculatedSalePrice
    ) {
    }

    public function productId(): int {
        return $this->productId;
    }

    public function supplierCost(): ?float {
        return $this->supplierCost;
    }

    public function commissionRate(): ?float {
        return $this->commissionRate;
    }

    public function minMarginRate(): ?float {
        return $this->minMarginRate;
    }

    public function lastCalculatedSalePrice(): ?float {
        return $this->lastCalculatedSalePrice;
    }
}
