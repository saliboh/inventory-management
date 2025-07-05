<?php

namespace App\Filament\Resources\ProductMovementResource\Pages;

use App\Filament\Resources\ProductMovementResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\View\View;

class ListProductMovements extends ListRecords
{
    protected static string $resource = ProductMovementResource::class;


    public function getTitle(): string
    {
        return 'Product Movements';
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getSubheading(): string
    {
        return 'From here you can manage the in and out of products from your warehouses.';
    }
}
