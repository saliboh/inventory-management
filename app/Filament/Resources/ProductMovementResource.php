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
use App\Models\Supplier;

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
                    ])
                    ->required()
                    ->reactive(),
                Forms\Components\Select::make('warehouse_id')
                    ->label('Warehouse')
                    ->options(Warehouse::all()->pluck('name', 'id'))
                    ->searchable()
                    ->required(),
                Forms\Components\TextInput::make('quantity')
                    ->required()
                    ->integer()
                    ->helperText('Use positive values for entries, negative values for exits.')
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
                    ->visible(fn (callable $get) => $get('movement_type') === 'entry')
                    ->placeholder('e.g., INV-12345'),
                Forms\Components\Select::make('supplier_id')
                    ->label('Supplier')
                    ->relationship('supplier', 'name')
                    ->searchable()
                    ->required(fn (callable $get) => $get('movement_type') === 'entry')
                    ->visible(fn (callable $get) => $get('movement_type') === 'entry')
                    ->rules(['exists:suppliers,id']),
                Forms\Components\Select::make('requested_by')
                    ->label('Requested By')
                    ->options(Supplier::pluck('name', 'id'))
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
                    ->searchable(),
                Tables\Columns\TextColumn::make('quantity')
                    ->sortable(),
                Tables\Columns\TextColumn::make('movement_type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'entry' => 'success',
                        'exit' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('unit_price')
                    ->money('PHP')
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_price')
                    ->money('PHP')
                    ->sortable(),
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
