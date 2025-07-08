<?php

namespace App\Observers;

use App\Models\ProductMovement;
use App\Services\InventoryBatchService;

class ProductMovementObserver
{
    protected $batchService;

    public function __construct(InventoryBatchService $batchService)
    {
        $this->batchService = $batchService;
    }

    /**
     * Handle the ProductMovement "created" event.
     */
    public function created(ProductMovement $productMovement): void
    {
        if ($productMovement->movement_type === 'entry') {
            // Create a new batch for entry movements
            $this->batchService->createBatchFromEntry($productMovement);
        } elseif ($productMovement->movement_type === 'exit') {
            // Process FIFO for exit movements
            $this->batchService->processExitWithFIFO($productMovement);
        }
    }
}
