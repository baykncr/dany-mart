<?php

namespace App\DTOs;

class CartItemDTO
{
    public function __construct(
        public readonly int $productId,
        public readonly int $quantity,
        public readonly int $unitPrice,
        public readonly int $subtotal,
    ) {}

    /**
     * Buat dari raw array request.
     */
    public static function fromArray(array $data): self
    {
        return new self(
            productId: (int) $data['product_id'],
            quantity:  (int) $data['quantity'],
            unitPrice: (int) $data['unit_price'],
            subtotal:  (int) $data['subtotal'],
        );
    }

    /**
     * Validasi konsistensi data: subtotal harus == qty * unit_price.
     */
    public function isConsistent(): bool
    {
        return $this->subtotal === $this->quantity * $this->unitPrice;
    }
}