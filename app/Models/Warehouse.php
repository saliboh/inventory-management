<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Warehouse extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'location',
        'description',
    ];

    /**
     * Get the product movements for the warehouse.
     */
    public function productMovements(): HasMany
    {
        return $this->hasMany(ProductMovement::class);
    }

    /**
     * Get the current inventory of products in this warehouse.
     */
    public function inventory()
    {
        return $this->productMovements()
            ->selectRaw('product_id, SUM(quantity) as total_quantity')
            ->groupBy('product_id')
            ->having('total_quantity', '>', 0)
            ->with('product');
    }
}
