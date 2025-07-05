<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'sku',
        'description',
        'price',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'price' => 'decimal:2',
    ];

    /**
     * Get the product movements for the product.
     */
    public function productMovements(): HasMany
    {
        return $this->hasMany(ProductMovement::class);
    }

    /**
     * Get the current inventory of this product across all warehouses.
     */
    public function inventory()
    {
        return $this->productMovements()
            ->selectRaw('warehouse_id, SUM(quantity) as total_quantity')
            ->groupBy('warehouse_id')
            ->having('total_quantity', '>', 0)
            ->with('warehouse');
    }
}
