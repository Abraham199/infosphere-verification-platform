<?php

namespace App\Domain\Product\Events;

use App\Domain\Product\Models\Product;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ProductUpdated { use Dispatchable, SerializesModels; public function __construct(public readonly Product $product) {} }
