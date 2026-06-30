<?php

namespace App\Domain\Product\Services;

use App\Domain\Product\Enums\ProductStatus;
use App\Domain\Product\Exceptions\InvalidProductStateException;
use App\Domain\Product\Models\Product;

class ProductStateMachine
{
    private const ALLOWED = [
        'draft' => ['active', 'disabled', 'archived'],
        'active' => ['disabled', 'maintenance', 'deprecated', 'archived'],
        'disabled' => ['active', 'archived'],
        'maintenance' => ['active', 'disabled', 'archived'],
        'deprecated' => ['disabled', 'archived'],
        'archived' => [],
    ];

    public function transition(Product $product, ProductStatus $to): Product
    {
        $from = $product->status->value;

        if ($from === $to->value) {
            return $product;
        }

        if (! in_array($to->value, self::ALLOWED[$from] ?? [], true)) {
            throw new InvalidProductStateException("Invalid product transition from {$from} to {$to->value}.");
        }

        $product->forceFill(['status' => $to])->save();

        return $product->refresh();
    }
}
