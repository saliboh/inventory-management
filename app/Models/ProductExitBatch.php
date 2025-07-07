<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductExitBatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_batch_id',
        'product_movement_id',
        'quantity_taken',
        'unit_price',
        'total_price',
    ];

    protected $casts = [
        'quantity_taken' => 'integer',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
    ];

    /**
     * Get the product batch that was used.
     */
    public function productBatch(): BelongsTo
    {
        return $this->belongsTo(ProductBatch::class);
    }

    /**
     * Get the product movement (exit) this record belongs to.
     */
    public function productMovement(): BelongsTo
    {
        return $this->belongsTo(ProductMovement::class);
    }
}
