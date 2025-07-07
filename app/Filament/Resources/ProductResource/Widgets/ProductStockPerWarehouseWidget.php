<?php

namespace App\Filament\Resources\ProductResource\Widgets;

use App\Models\ProductMovement;
use App\Models\Warehouse;
use Filament\Widgets\Widget;

class ProductStockPerWarehouseWidget extends Widget
{
    public $record;

    protected static string $view = 'filament.resources.product-resource.widgets.product-stock-per-warehouse-widget';

    public function getWarehousesWithStock()
    {
        if (!$this->record) {
            return collect();
        }
        $productId = $this->record->id;
        return Warehouse::all()->map(function ($warehouse) use ($productId) {
            $stock = ProductMovement::where('product_id', $productId)
                ->where('warehouse_id', $warehouse->id)
                ->sum('quantity');
            return [
                'name' => $warehouse->name,
                'stock' => $stock,
            ];
        });
    }
}

