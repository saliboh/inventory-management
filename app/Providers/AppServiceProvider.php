<?php

namespace App\Providers;

use App\Models\ProductMovement;
use App\Observers\ProductMovementObserver;
use App\Services\InventoryBatchService;
use Filament\Support\Facades\FilamentView;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(InventoryBatchService::class, function ($app) {
            return new InventoryBatchService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (env('FORCE_HTTPS') == true) {
            URL::forceHttps('https');
        }

        ProductMovement::observe(ProductMovementObserver::class);

        FilamentView::registerRenderHook(
            'panels::footer',
            fn (): string => Blade::render('<x-footer />'),
        );
    }
}
