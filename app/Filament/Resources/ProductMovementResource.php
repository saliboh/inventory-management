<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductMovementResource\Pages;
use App\Models\Product;
use App\Models\ProductMovement;
use App\Models\Warehouse;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Validation\Rule;

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
                    ->searchable(),
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
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Created By')
                    ->searchable(),
            ])
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
                        \Filament\Forms\Components\DatePicker::make('created_from')->label('Created From'),
                        \Filament\Forms\Components\DatePicker::make('created_until')->label('Created Until'),
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
