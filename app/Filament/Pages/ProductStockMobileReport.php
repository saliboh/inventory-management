<?php

namespace App\Filament\Pages;

use App\Models\Product;
use App\Models\Warehouse;
use App\Models\ProductMovement;
use Filament\Pages\Page;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Form;
use Filament\Forms\Concerns\InteractsWithForms;
use Illuminate\Support\Carbon;

class ProductStockMobileReport extends Page
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-table-cells';
    protected static ?string $navigationLabel = 'Product Stock Reports';
    protected static ?string $title = 'Product Stock Report';
    protected static string $view = 'filament.pages.product-stock-mobile-report';

    public $warehouses;
    public $asOfDate;
    public $data = [];

    public function mount(): void
    {
        $this->warehouses = Warehouse::all();
        $this->asOfDate = now()->format('Y-m-d');
        $this->form->fill([
            'asOfDate' => $this->asOfDate,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                DatePicker::make('asOfDate')
                    ->label('View Stock As Of Date')
                    ->default(now())
                    ->maxDate(now())
                    ->live()
                    ->afterStateUpdated(function ($state) {
                        $this->asOfDate = $state;
                    })
                    ->required(),
            ])
            ->statePath('data');
    }

    public function getProducts()
    {
        return Product::query()->paginate(25);
    }

    public function getStock($productId, $warehouseId)
    {
        return ProductMovement::where('product_id', $productId)
            ->where('warehouse_id', $warehouseId)
            ->where('created_at', '<=', Carbon::parse($this->asOfDate)->endOfDay())
            ->sum('quantity');
    }

    public function getFormattedAsOfDate()
    {
        return Carbon::parse($this->asOfDate)->format('F d, Y');
    }
}
