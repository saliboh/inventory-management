<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class CustomDashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.custom-dashboard';

    protected static ?string $navigationLabel = 'Dashboard';

    public function getTitle(): string
    {
        return 'LOWA Inventory Stock Tracker';
    }
}
