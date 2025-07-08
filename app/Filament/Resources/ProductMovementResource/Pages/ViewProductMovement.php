<?php

namespace App\Filament\Resources\ProductMovementResource\Pages;

use App\Filament\Resources\ProductMovementResource;
use App\Models\ProductExitBatch;
use App\Models\Warehouse;
use Filament\Actions;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewProductMovement extends ViewRecord
{
    protected static string $resource = ProductMovementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('print')
                ->label('Print Details')
                ->icon('heroicon-o-printer')
                ->color('success')
                ->url(fn ($record) => route('product-movements.print', $record))
                ->openUrlInNewTab(),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Movement Details')
                    ->schema([
                        TextEntry::make('product.name')
                            ->label('Product'),
                        TextEntry::make('movement_type')
                            ->label('Movement Type')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'entry' => 'success',
                                'exit' => 'danger',
                                'transfer' => 'warning',
                                'adjustment' => 'info',
                                default => 'gray',
                            }),
                        TextEntry::make('quantity')
                            ->label('Quantity')
                            ->getStateUsing(function ($record) {
                                // For exit movements, display the absolute value (positive number)
                                if ($record->movement_type === 'exit') {
                                    return abs($record->quantity);
                                }

                                return $record->quantity;
                            }),
                        TextEntry::make('created_at')
                            ->label('Date & Time')
                            ->dateTime(),
                        TextEntry::make('user.name')
                            ->label('Created By'),
                        TextEntry::make('supplier.name')
                            ->label('Supplier')
                            ->visible(fn ($record) => $record->movement_type === 'entry'),
                    ])
                    ->columns(2),

                Section::make('Warehouse Information')
                    ->schema([
                        TextEntry::make('warehouse.name')
                            ->label(fn ($record) => $record->movement_type === 'transfer' ? 'To Warehouse' : 'Warehouse'),
                        TextEntry::make('from_warehouse')
                            ->label('From Warehouse')
                            ->getStateUsing(function ($record) {
                                if ($record->movement_type === 'transfer') {
                                    // Try to get from_warehouse_id from notes
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
                            ->visible(fn ($record) => $record->movement_type === 'transfer'),
                    ])
                    ->columns(2),

                Section::make('Price Information')
                    ->schema([
                        TextEntry::make('unit_price')
                            ->label('Unit Price')
                            ->money('PHP'),
                        TextEntry::make('total_price')
                            ->label('Total Price')
                            ->money('PHP'),
                        TextEntry::make('price_reference')
                            ->label('Price Reference/Invoice'),
                    ])
                    ->columns(3),

                Section::make('COA Price Details')
                    ->schema([
                        TextEntry::make('calculated_unit_price')
                            ->label('Calculated Unit Price (FIFO)')
                            ->money('PHP')
                            ->getStateUsing(function ($record) {
                                if ($record->movement_type === 'transfer') {
                                    // For transfers, get the average price from related batches
                                    $exitBatches = ProductExitBatch::where('product_movement_id', $record->id)->get();
                                    if ($exitBatches->count() > 0) {
                                        $totalCost = $exitBatches->sum('total_price');
                                        $totalQuantity = $exitBatches->sum('quantity_taken');
                                        if ($totalQuantity > 0) {
                                            return $totalCost / $totalQuantity;
                                        }
                                    }
                                }
                                return $record->unit_price;
                            }),
                        TextEntry::make('calculated_total_price')
                            ->label('Calculated Total Price')
                            ->money('PHP')
                            ->getStateUsing(function ($record) {
                                if ($record->movement_type === 'transfer') {
                                    // For transfers, get the total price from related batches
                                    $exitBatches = ProductExitBatch::where('product_movement_id', $record->id)->get();
                                    if ($exitBatches->count() > 0) {
                                        return $exitBatches->sum('total_price');
                                    }
                                }
                                return $record->total_price;
                            }),
                    ])
                    ->columns(2)
                    ->visible(fn ($record) => in_array($record->movement_type, ['transfer', 'exit'])),

                Section::make('Batch Details')
                    ->schema([
                        TextEntry::make('batch_breakdown')
                            ->label('FIFO Batch Breakdown')
                            ->html()
                            ->getStateUsing(function ($record) {
                                if (in_array($record->movement_type, ['transfer', 'exit'])) {
                                    $exitBatches = ProductExitBatch::where('product_movement_id', $record->id)
                                        ->with('productBatch')
                                        ->get();

                                    if ($exitBatches->isEmpty()) {
                                        return '<p>No batch information available.</p>';
                                    }

                                    $html = '<div style="overflow-x: auto;">
                                        <table class="w-full text-sm" style="min-width: 800px;">
                                            <thead>
                                                <tr class="border-b">
                                                    <th class="text-left py-2 px-3" style="width: 15%">Batch</th>
                                                    <th class="text-left py-2 px-3" style="width: 20%">Date</th>
                                                    <th class="text-right py-2 px-3" style="width: 15%">Quantity</th>
                                                    <th class="text-right py-2 px-3" style="width: 25%">Unit Price (PHP)</th>
                                                    <th class="text-right py-2 px-3" style="width: 25%">Total (PHP)</th>
                                                </tr>
                                            </thead>
                                            <tbody>';

                                    foreach ($exitBatches as $batch) {
                                        $html .= '<tr class="border-b">
                                            <td class="py-2 px-3">#' . $batch->productBatch?->id . '</td>
                                            <td class="py-2 px-3">' . $batch->productBatch?->created_at->format('M d, Y') . '</td>
                                            <td class="py-2 px-3 text-right">' . number_format($batch->quantity_taken) . '</td>
                                            <td class="py-2 px-3 text-right">' . number_format($batch->unit_price, 2) . '</td>
                                            <td class="py-2 px-3 text-right">' . number_format($batch->total_price, 2) . '</td>
                                        </tr>';
                                    }

                                    $html .= '</tbody></table></div>';
                                    return $html;
                                }

                                return null;
                            }),
                    ])
                    ->visible(fn ($record) => in_array($record->movement_type, ['transfer', 'exit'])),

                Section::make('Notes')
                    ->schema([
                        TextEntry::make('notes')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
