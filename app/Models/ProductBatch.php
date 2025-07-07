<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductBatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'warehouse_id',
        'product_movement_id',
        'quantity_remaining',
        'unit_price',
    ];

    protected $casts = [
        'quantity_remaining' => 'integer',
        'unit_price' => 'decimal:2',
    ];

    /**
     * Get the product for this batch.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the warehouse for this batch.
     */
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    /**
     * Get the movement that created this batch.
     */
    public function productMovement(): BelongsTo
    {
        return $this->belongsTo(ProductMovement::class);
    }

    /**
     * Get the exits that have used this batch.
     */
    public function exitBatches(): HasMany
    {
        return $this->hasMany(ProductExitBatch::class);
    }
}
