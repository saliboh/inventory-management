<?php

namespace App\Filament\Resources\WarehouseResource\Pages;

use App\Filament\Resources\WarehouseResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class Dashboard extends ListRecords
{
    protected static string $resource = WarehouseResource::class;
}
