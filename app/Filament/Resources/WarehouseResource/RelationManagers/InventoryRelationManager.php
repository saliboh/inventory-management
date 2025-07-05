<?php

namespace App\Filament\Resources\WarehouseResource\RelationManagers;

use App\Models\Product;
use Filament\Forms\Components\DatePicker;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class InventoryRelationManager extends RelationManager
{
    protected static string $relationship = 'productMovements';

    protected static ?string $recordTitleAttribute = 'product.name';

    protected static ?string $title = 'Warehouse Inventory';

    public function table(Table $table): Table
    {
        return $table
            ->heading('Available Products')
            ->description('All stocks that are currently available in this warehouse. Stock quantities will be adjusted automatically from their movements on the Product Movement Page.')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Product Name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('sku')
                    ->label('SKU')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('price')
                    ->label('Price')
                    ->money('USD')
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_quantity')
                    ->label('Available Quantity')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date / Time of Transaction')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\Filter::make('created_at_period')
                    ->form([
                        DatePicker::make('from')->label('From'),
                        DatePicker::make('until')->label('Until'),
                    ])
                    ->query(function (Builder $query, array $data) {
                        return $query
                            ->when($data['from'], fn ($q) => $q->whereDate('product_movements.created_at', '>=', $data['from']))
                            ->when($data['until'], fn ($q) => $q->whereDate('product_movements.created_at', '<=', $data['until']));
                    }),
            ])
            ->headerActions([
                // No actions needed for inventory view
            ])
            ->actions([
                // No actions needed for inventory items
            ])
            ->bulkActions([
                // No bulk actions needed
            ]);
    }

    public function getTableQuery(): Builder
    {
        // Build from Product, join product_movements, sum quantity, group by product.id
        return Product::query()
            ->select('products.*')
            ->selectRaw('COALESCE(SUM(product_movements.quantity), 0) as total_quantity')
            ->leftJoin('product_movements', function ($join) {
                $join->on('products.id', '=', 'product_movements.product_id')
                    ->where('product_movements.warehouse_id', '=', $this->getOwnerRecord()->id);
            })
            ->groupBy('products.id')
            ->having('total_quantity', '>', 0)
            ->orderByDesc('total_quantity');
    }
}
