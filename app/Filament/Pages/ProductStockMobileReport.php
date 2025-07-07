<?php

namespace App\Filament\Pages;

use App\Models\Product;
use App\Models\Warehouse;
use App\Models\ProductMovement;
use Filament\Pages\Page;

class ProductStockMobileReport extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-table-cells';
    protected static ?string $navigationLabel = 'Product Stock Reports';
    protected static ?string $title = 'Product Stock Report';
    protected static string $view = 'filament.pages.product-stock-mobile-report';

    public $warehouses;

    public function mount(): void
    {
        $this->warehouses = Warehouse::all();
    }

    public function getProducts()
    {
        return Product::query()->paginate(25);
    }

    public function getStock($productId, $warehouseId)
    {
        return ProductMovement::where('product_id', $productId)
            ->where('warehouse_id', $warehouseId)
            ->sum('quantity');
    }
}
