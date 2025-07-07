<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductMovementResource\Pages;
use App\Models\Product;
use App\Models\ProductMovement;
use App\Models\Warehouse;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class ProductMovementResource extends Resource
{
    protected static ?string $model = ProductMovement::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrows-right-left';

    protected static ?string $navigationGroup = 'Inventory Management';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('product_id')
                    ->label('Product')
                    ->options(Product::all()->pluck('name', 'id'))
                    ->searchable()
                    ->required(),
                Forms\Components\Select::make('movement_type')
                    ->options([
                        'entry' => 'Entry',
                        'exit' => 'Exit',
                        'transfer' => 'Transfer',
                    ])
                    ->required()
                    ->reactive(),
                Forms\Components\Select::make('warehouse_id')
                    ->label('Warehouse')
                    ->options(Warehouse::all()->pluck('name', 'id'))
                    ->searchable()
                    ->required()
                    ->visible(fn (callable $get) => in_array($get('movement_type'), ['entry', 'exit'])),
                Forms\Components\Select::make('from_warehouse_id')
                    ->label('From Warehouse')
                    ->options(Warehouse::all()->pluck('name', 'id'))
                    ->searchable()
                    ->required()
                    ->visible(fn (callable $get) => $get('movement_type') === 'transfer')
                    ->reactive(),
                Forms\Components\Select::make('to_warehouse_id')
                    ->label('To Warehouse')
                    ->options(function (callable $get) {
                        $from = $get('from_warehouse_id');
                        $warehouses = Warehouse::all();
                        if ($from) {
                            return $warehouses->where('id', '!=', $from)->pluck('name', 'id');
                        }
                        return $warehouses->pluck('name', 'id');
                    })
                    ->searchable()
                    ->required()
                    ->visible(fn (callable $get) => $get('movement_type') === 'transfer')
                    ->different('from_warehouse_id')
                    ->reactive(),
                Forms\Components\TextInput::make('quantity')
                    ->required()
                    ->integer()
                    ->helperText('Use positive values for entries, negative values for exits. For transfer, this is the amount to move.')
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $get, callable $set) {
                        $unitPrice = $get('unit_price');
                        if ($state && $unitPrice) {
                            $set('total_price', $state * $unitPrice);
                        }
                    })
                    ->rules(function ($state, callable $get) {
                        $movementType = $get('movement_type');
                        $productId = $get('product_id');
                        $warehouseId = $get('warehouse_id');
                        $fromWarehouseId = $get('from_warehouse_id');
                        if ($movementType === 'exit' && $productId && $warehouseId) {
                            $stock = \App\Models\ProductMovement::where('product_id', $productId)
                                ->where('warehouse_id', $warehouseId)
                                ->sum('quantity');
                            return [
                                function ($attribute, $value, $fail) use ($stock) {
                                    if ($value > $stock) {
                                        $fail('The product quantity to be taken, exceeds the current total stock in the warehouse');
                                    }
                                }
                            ];
                        }
                        if ($movementType === 'transfer' && $productId && $fromWarehouseId) {
                            $stock = \App\Models\ProductMovement::where('product_id', $productId)
                                ->where('warehouse_id', $fromWarehouseId)
                                ->sum('quantity');
                            return [
                                function ($attribute, $value, $fail) use ($stock) {
                                    if ($value > $stock) {
                                        $fail('The transfer quantity exceeds the available stock in the source warehouse. Available: ' . $stock);
                                    }
                                }
                            ];
                        }
                        return [];
                    }),
                Forms\Components\TextInput::make('unit_price')
                    ->label('Unit Price')
                    ->numeric()
                    ->prefix('PHP')
                    ->step(0.01)
                    ->visible(fn (callable $get) => $get('movement_type') === 'entry')
                    ->reactive()
                    ->debounce('500ms') // Add debounce to wait until typing stops
                    ->afterStateUpdated(function ($state, callable $get, callable $set) {
                        if ($state && $get('quantity')) {
                            $set('total_price', $state * $get('quantity'));
                        }
                    }),
                Forms\Components\TextInput::make('total_price')
                    ->label('Total Price')
                    ->numeric()
                    ->prefix('PHP')
                    ->step(0.01)
                    ->visible(fn (callable $get) => $get('movement_type') === 'entry')
                    ->disabled()
                    ->dehydrated(),
                Forms\Components\TextInput::make('price_reference')
                    ->label('Price Reference/Invoice')
                    ->visible(fn (callable $get) => in_array($get('movement_type'), ['entry', 'transfer']))
                    ->placeholder('e.g., INV-12345 or TR-12345'),
                Forms\Components\Placeholder::make('transfer_price_note')
                    ->label('Price Calculation Note')
                    ->content('Prices for transfers are automatically calculated using FIFO method from source warehouse stock.')
                    ->visible(fn (callable $get) => $get('movement_type') === 'transfer'),
                Forms\Components\Select::make('requested_by')
                    ->label('Requested By')
                    ->relationship('requestedBy', 'full_name')
                    ->searchable()
                    ->preload()
                    ->visible(fn (callable $get) => $get('movement_type') === 'exit')
                    ->placeholder('Select employee who requested this exit'),
                Forms\Components\Textarea::make('notes')
                    ->maxLength(65535)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('product.name')
                    ->label('Product')
                    ->searchable(),
                Tables\Columns\TextColumn::make('warehouse.name')
                    ->label('Warehouse')
                    ->description(fn (ProductMovement $record) => $record->movement_type === 'transfer' ? 'To Warehouse' : '')
                    ->searchable(),
                Tables\Columns\TextColumn::make('from_warehouse')
                    ->label('From Warehouse')
                    ->getStateUsing(function (ProductMovement $record) {
                        if ($record->movement_type === 'transfer') {
                            // Try to get from_warehouse_id from meta or notes
                            // This is a workaround since we don't have a dedicated column
                            if ($record->notes && str_contains($record->notes, 'from_warehouse_id:')) {
                                preg_match('/from_warehouse_id:(\d+)/', $record->notes, $matches);
                                if (isset($matches[1])) {
                                    $fromWarehouseId = $matches[1];
                                    return Warehouse::find($fromWarehouseId)?->name;
                                }
                            }
                        }
                        return null;
                    })
                    ->visible(fn (?ProductMovement $record) => $record?->movement_type === 'transfer'),
                Tables\Columns\TextColumn::make('quantity')
                    ->sortable(),
                Tables\Columns\TextColumn::make('movement_type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'entry' => 'success',
                        'exit' => 'danger',
                        'transfer' => 'warning',
                        'adjustment' => 'info',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('unit_price')
                    ->money('PHP')
                    ->sortable()
                    ->getStateUsing(function (ProductMovement $record) {
                        if ($record->movement_type === 'transfer') {
                            // For transfers, we need to calculate the average price from batches
                            $exitBatches = $record->productExitBatches;
                            if ($exitBatches && $exitBatches->count() > 0) {
                                $totalQuantity = $exitBatches->sum('quantity_taken');
                                $totalPrice = $exitBatches->sum('total_price');

                                if ($totalQuantity > 0) {
                                    return $totalPrice / $totalQuantity;
                                }
                            }
                        }
                        return $record->unit_price;
                    }),
                Tables\Columns\TextColumn::make('total_price')
                    ->money('PHP')
                    ->sortable()
                    ->getStateUsing(function (ProductMovement $record) {
                        if ($record->movement_type === 'transfer') {
                            // For transfers, we need to calculate the total price from batches
                            $exitBatches = $record->productExitBatches;
                            if ($exitBatches && $exitBatches->count() > 0) {
                                return $exitBatches->sum('total_price');
                            }
                        }
                        return $record->total_price;
                    }),
                Tables\Columns\TextColumn::make('price_reference')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created at')
                    ->formatStateUsing(fn ($state): string => Carbon::parse($state)->format('M d, Y H:i'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Created By')
                    ->searchable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('movement_type')
                    ->options([
                        'entry' => 'Entry',
                        'exit' => 'Exit',
                        'transfer' => 'Transfer',
                    ]),
                Tables\Filters\SelectFilter::make('warehouse_id')
                    ->label('Warehouse')
                    ->options(Warehouse::all()->pluck('name', 'id')),
                Tables\Filters\SelectFilter::make('product_id')
                    ->label('Product')
                    ->options(Product::all()->pluck('name', 'id')),
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        DatePicker::make('created_from')->label('Created From'),
                        DatePicker::make('created_until')->label('Created Until'),
                    ])
                    ->query(function (Builder $query, array $data) {
                        return $query
                            ->when($data['created_from'], fn ($q) => $q->whereDate('created_at', '>=', $data['created_from']))
                            ->when($data['created_until'], fn ($q) => $q->whereDate('created_at', '<=', $data['created_until']));
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProductMovements::route('/'),
            'create' => Pages\CreateProductMovement::route('/create'),
            'view' => Pages\ViewProductMovement::route('/{record}'),
        ];
    }
}
