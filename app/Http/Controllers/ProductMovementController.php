<?php

namespace App\Http\Controllers;

use App\Models\ProductExitBatch;
use App\Models\ProductMovement;
use App\Models\Warehouse;
use Illuminate\Http\Request;

class ProductMovementController extends Controller
{
    /**
     * Display a print-friendly version of the product movement.
     *
     * @param ProductMovement $productMovement
     * @return \Illuminate\View\View
     */
    public function printView(ProductMovement $productMovement)
    {
        // Get source warehouse for transfers
        $sourceWarehouse = null;
        if ($productMovement->movement_type === 'transfer') {
            if ($productMovement->notes && str_contains($productMovement->notes, 'from_warehouse_id:')) {
                preg_match('/from_warehouse_id:(\d+)/', $productMovement->notes, $matches);
                if (isset($matches[1])) {
                    $fromWarehouseId = $matches[1];
                    $sourceWarehouse = Warehouse::find($fromWarehouseId);
                }
            }
        }
        
        // Get batch information for transfers and exits
        $batches = [];
        $calculatedUnitPrice = $productMovement->unit_price;
        $calculatedTotalPrice = $productMovement->total_price;
        
        if (in_array($productMovement->movement_type, ['transfer', 'exit'])) {
            $exitBatches = ProductExitBatch::where('product_movement_id', $productMovement->id)
                ->with('productBatch')
                ->get();
                
            if ($exitBatches->count() > 0) {
                $batches = $exitBatches;
                
                // Calculate prices based on batches
                $totalCost = $exitBatches->sum('total_price');
                $totalQuantity = $exitBatches->sum('quantity_taken');
                
                if ($totalQuantity > 0) {
                    $calculatedUnitPrice = $totalCost / $totalQuantity;
                }
                
                $calculatedTotalPrice = $totalCost;
            }
        }
        
        return view('product-movement.print', [
            'movement' => $productMovement,
            'sourceWarehouse' => $sourceWarehouse,
            'batches' => $batches,
            'calculatedUnitPrice' => $calculatedUnitPrice,
            'calculatedTotalPrice' => $calculatedTotalPrice,
        ]);
    }
}
