<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductMovement extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'product_id',
        'warehouse_id',
        'user_id',
        'requested_by',
        'quantity',
        'unit_price',
        'total_price',
        'price_reference',
        'movement_type',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
    ];

    /**
     * Get the product that owns the movement.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the warehouse that owns the movement.
     */
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    /**
     * Get the user that created the movement.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the employee who requested the movement.
     */
    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'requested_by');
    }

    /**
     * Get the product batch that was created by this entry movement.
     */
    public function productBatch()
    {
        return $this->hasOne(ProductBatch::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * Get the product exit batches that were created by this exit movement.
     */
    public function productExitBatches()
    {
        return $this->hasMany(ProductExitBatch::class);
    }

    /**
     * Scope a query to only include entries (positive quantity).
     */
    public function scopeEntries($query)
    {
        return $query->where('quantity', '>', 0);
    }

    /**
     * Scope a query to only include exits (negative quantity).
     */
    public function scopeExits($query)
    {
        return $query->where('quantity', '<', 0);
    }
}
