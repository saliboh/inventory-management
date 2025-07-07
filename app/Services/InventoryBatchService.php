<?php

namespace App\Services;

use App\Models\ProductBatch;
use App\Models\ProductExitBatch;
use App\Models\ProductMovement;
use Illuminate\Support\Facades\DB;

class InventoryBatchService
{
    /**
     * Create a product batch when a new entry is made.
     *
     * @param ProductMovement $movement
     * @return ProductBatch
     */
    public function createBatchFromEntry(ProductMovement $movement)
    {
        // Only create batches for entry movements
        if ($movement->quantity <= 0 || $movement->movement_type !== 'entry') {
            return null;
        }

        return ProductBatch::create([
            'product_id' => $movement->product_id,
            'warehouse_id' => $movement->warehouse_id,
            'product_movement_id' => $movement->id,
            'quantity_remaining' => $movement->quantity,
            'unit_price' => $movement->unit_price,
        ]);
    }

    /**
     * Process an exit movement by finding and consuming batches in FIFO order.
     *
     * @param ProductMovement $movement
     * @return array ProductExitBatch[]
     */
    public function processExitWithFIFO(ProductMovement $movement)
    {
        // Only process exits
        if ($movement->quantity >= 0 || $movement->movement_type !== 'exit') {
            return [];
        }

        $quantityToExit = abs($movement->quantity);
        $totalCost = 0;
        $exitBatches = [];

        // Find available batches in FIFO order (oldest first)
        $availableBatches = ProductBatch::where('product_id', $movement->product_id)
            ->where('warehouse_id', $movement->warehouse_id)
            ->where('quantity_remaining', '>', 0)
            ->orderBy('created_at')
            ->get();

        // Use DB transaction to ensure consistency
        return DB::transaction(function () use ($movement, $quantityToExit, $availableBatches, &$totalCost) {
            $exitBatches = [];
            $remainingQuantity = $quantityToExit;
            
            foreach ($availableBatches as $batch) {
                if ($remainingQuantity <= 0) {
                    break;
                }

                $quantityFromBatch = min($remainingQuantity, $batch->quantity_remaining);
                $costFromBatch = $quantityFromBatch * $batch->unit_price;
                
                // Create a record of which batch was used for this exit
                $exitBatch = ProductExitBatch::create([
                    'product_batch_id' => $batch->id,
                    'product_movement_id' => $movement->id,
                    'quantity_taken' => $quantityFromBatch,
                    'unit_price' => $batch->unit_price,
                    'total_price' => $costFromBatch,
                ]);
                
                // Update the remaining quantity in the batch
                $batch->quantity_remaining -= $quantityFromBatch;
                $batch->save();
                
                $totalCost += $costFromBatch;
                $remainingQuantity -= $quantityFromBatch;
                $exitBatches[] = $exitBatch;
            }
            
            // Update the movement with the calculated total cost
            $movement->update([
                'unit_price' => $quantityToExit > 0 ? $totalCost / $quantityToExit : 0,
                'total_price' => $totalCost,
            ]);
            
            return $exitBatches;
        });
    }
    
    /**
     * Process a transfer movement (creates an exit and entry in different warehouses).
     *
     * @param ProductMovement $movement
     * @param int $fromWarehouseId
     * @param int $toWarehouseId
     * @return array [exitBatches, entryBatch]
     */
    public function processTransfer(ProductMovement $movement, int $fromWarehouseId, int $toWarehouseId)
    {
        // Only process transfers
        if ($movement->movement_type !== 'transfer') {
            return [[], null];
        }

        $quantity = abs($movement->quantity);
        $exitBatches = [];
        $totalCost = 0;
        $avgUnitCost = 0;

        // Find available batches in FIFO order for the source warehouse
        $availableBatches = ProductBatch::where('product_id', $movement->product_id)
            ->where('warehouse_id', $fromWarehouseId)
            ->where('quantity_remaining', '>', 0)
            ->orderBy('created_at')
            ->get();

        // Use DB transaction to ensure consistency
        return DB::transaction(function () use ($movement, $quantity, $fromWarehouseId, $toWarehouseId, $availableBatches, &$totalCost) {
            $exitBatches = [];
            $remainingQuantity = $quantity;

            // Process exit batches from source warehouse
            foreach ($availableBatches as $batch) {
                if ($remainingQuantity <= 0) {
                    break;
                }

                $quantityFromBatch = min($remainingQuantity, $batch->quantity_remaining);
                $costFromBatch = $quantityFromBatch * $batch->unit_price;

                // Create a record of which batch was used for this transfer
                $exitBatch = ProductExitBatch::create([
                    'product_batch_id' => $batch->id,
                    'product_movement_id' => $movement->id,
                    'quantity_taken' => $quantityFromBatch,
                    'unit_price' => $batch->unit_price,
                    'total_price' => $costFromBatch,
                ]);

                // Update the remaining quantity in the batch
                $batch->quantity_remaining -= $quantityFromBatch;
                $batch->save();

                $totalCost += $costFromBatch;
                $remainingQuantity -= $quantityFromBatch;
                $exitBatches[] = $exitBatch;
            }

            $avgUnitCost = $quantity > 0 ? $totalCost / $quantity : 0;

            // Create a new batch in the destination warehouse with the same cost basis
            $entryBatch = ProductBatch::create([
                'product_id' => $movement->product_id,
                'warehouse_id' => $toWarehouseId,
                'product_movement_id' => $movement->id,
                'quantity_remaining' => $quantity,
                'unit_price' => $avgUnitCost,
            ]);

            // Update the movement with the cost information
            $movement->update([
                'unit_price' => $avgUnitCost,
                'total_price' => $totalCost,
            ]);

            return [$exitBatches, $entryBatch];
        });
    }
}
