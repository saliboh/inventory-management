<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Warehouse;
use App\Models\ProductMovement;
use App\Models\ProductBatch;
use App\Models\ProductExitBatch;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ProductStockReportController extends Controller
{
    /**
     * Display a print-friendly version of the product stock report.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function printView(Request $request)
    {
        // Get the as-of date from the request, default to today
        $asOfDate = $request->input('as_of_date', now()->format('Y-m-d'));
        $asOfDateObj = Carbon::parse($asOfDate)->endOfDay();

        // Get all warehouses
        $warehouses = Warehouse::all();

        // Get all products
        $products = Product::all();

        // Calculate stock and financial data for each product and warehouse
        $stockData = [];
        $totalInventoryValue = 0;

        foreach ($products as $product) {
            $productTotalStock = 0;
            $productTotalValue = 0;
            $productAvgUnitPrice = 0;

            $stockData[$product->id] = [
                'product' => $product,
                'warehouses' => [],
                'total_stock' => 0,
                'avg_unit_price' => 0,
                'total_value' => 0
            ];

            foreach ($warehouses as $warehouse) {
                // Get stock quantity
                $stock = ProductMovement::where('product_id', $product->id)
                    ->where('warehouse_id', $warehouse->id)
                    ->where('created_at', '<=', $asOfDateObj)
                    ->sum('quantity');

                // Calculate unit price and total value using remaining batches
                $batchesValue = 0;
                $avgUnitPrice = 0;

                if ($stock > 0) {
                    // Get active batches for this product in this warehouse
                    $batches = ProductBatch::where('product_id', $product->id)
                        ->where('warehouse_id', $warehouse->id)
                        ->where('created_at', '<=', $asOfDateObj)
                        ->get();

                    // Calculate consumed quantities for each batch
                    foreach ($batches as $batch) {
                        // Calculate consumed quantity up to the as-of date
                        $consumedQty = ProductExitBatch::where('product_batch_id', $batch->id)
                            ->whereHas('productMovement', function($q) use ($asOfDateObj) {
                                $q->where('created_at', '<=', $asOfDateObj);
                            })
                            ->sum('quantity_taken');

                        // Calculate remaining quantity
                        $remainingQty = max(0, $batch->quantity_remaining);

                        if ($remainingQty > 0) {
                            $batchesValue += $remainingQty * $batch->unit_price;
                        }
                    }

                    // Calculate average unit price
                    $avgUnitPrice = $stock > 0 ? $batchesValue / $stock : 0;
                }

                // If no batches found or calculation failed, fall back to the most recent entry
                if ($avgUnitPrice == 0 && $stock > 0) {
                    $latestEntry = ProductMovement::where('product_id', $product->id)
                        ->where('movement_type', 'entry')
                        ->where('created_at', '<=', $asOfDateObj)
                        ->orderBy('created_at', 'desc')
                        ->first();

                    $avgUnitPrice = $latestEntry ? $latestEntry->unit_price : 0;
                    $batchesValue = $stock * $avgUnitPrice;
                }

                $stockData[$product->id]['warehouses'][$warehouse->id] = [
                    'warehouse' => $warehouse,
                    'stock' => $stock,
                    'unit_price' => $avgUnitPrice,
                    'value' => $batchesValue
                ];

                $productTotalStock += $stock;
                $productTotalValue += $batchesValue;
            }

            // Calculate product average unit price across all warehouses
            $productAvgUnitPrice = $productTotalStock > 0 ? $productTotalValue / $productTotalStock : 0;

            $stockData[$product->id]['total_stock'] = $productTotalStock;
            $stockData[$product->id]['avg_unit_price'] = $productAvgUnitPrice;
            $stockData[$product->id]['total_value'] = $productTotalValue;

            $totalInventoryValue += $productTotalValue;
        }

        return view('product-stock.print', [
            'asOfDate' => Carbon::parse($asOfDate)->format('F d, Y'),
            'warehouses' => $warehouses,
            'stockData' => $stockData,
            'totalInventoryValue' => $totalInventoryValue,
            'generatedAt' => now()->format('F d, Y h:i A')
        ]);
    }
}
