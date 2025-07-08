<?php

namespace App\Providers;

use App\Models\ProductMovement;
use App\Observers\ProductMovementObserver;
use App\Services\InventoryBatchService;
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
        if (env('APP_ENV') != 'local') {
            URL::forceHttps('https');
        }


        ProductMovement::observe(ProductMovementObserver::class);
    }
}
