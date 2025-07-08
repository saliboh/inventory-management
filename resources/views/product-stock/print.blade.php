<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Stock Report - {{ $asOfDate }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .company-name {
            font-weight: bold;
            font-size: 28px;
            margin-bottom: 5px;
        }
        .report-title {
            font-size: 18px;
            color: #666;
        }
        .print-date {
            color: #888;
            font-size: 14px;
            margin-top: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            padding: 8px 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f9fafb;
            font-weight: bold;
        }
        .warehouse-header {
            text-align: center;
            min-width: 80px;
        }
        .stock-cell {
            text-align: center;
        }
        .price-cell, .value-cell, .totals-cell {
            text-align: right;
            white-space: nowrap;
        }
        .totals-row {
            font-weight: bold;
            background-color: #f9fafb;
        }
        .positive-stock {
            color: #059669;
            font-weight: bold;
        }
        .zero-stock {
            color: #9ca3af;
        }
        .summary-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            margin-bottom: 30px;
        }
        .summary-box {
            border: 1px solid #ddd;
            border-radius: 6px;
            padding: 15px;
            min-width: 200px;
            margin-bottom: 10px;
            background-color: #f9fafb;
        }
        .summary-title {
            font-size: 14px;
            color: #666;
            margin-bottom: 5px;
        }
        .summary-value {
            font-size: 20px;
            font-weight: bold;
            color: #111;
        }
        .footer {
            text-align: center;
            margin-top: 50px;
            font-size: 14px;
            color: #888;
            border-top: 1px solid #ddd;
            padding-top: 20px;
        }
        .page-break {
            page-break-after: always;
        }
        .section-title {
            font-size: 18px;
            font-weight: bold;
            margin: 25px 0 15px 0;
        }

        @media print {
            body {
                padding: 0;
                font-size: 12px;
            }
            .no-print {
                display: none;
            }
            @page {
                margin: 1.5cm;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-name">Inventory Management System</div>
        <div class="report-title">Product Stock Report as of {{ $asOfDate }}</div>
        <div class="print-date">Printed on: {{ $generatedAt }}</div>
    </div>

    <div class="summary-container">
        <div class="summary-box">
            <div class="summary-title">Total Products</div>
            <div class="summary-value">{{ count($stockData) }}</div>
        </div>
        <div class="summary-box">
            <div class="summary-title">Total Stock (All Warehouses)</div>
            <div class="summary-value">{{ number_format(array_sum(array_column($stockData, 'total_stock'))) }}</div>
        </div>
        <div class="summary-box">
            <div class="summary-title">Total Inventory Value</div>
            <div class="summary-value">PHP {{ number_format($totalInventoryValue, 2) }}</div>
        </div>
    </div>

    <div class="section-title">Stock Quantities by Warehouse</div>
    <table>
        <thead>
            <tr>
                <th style="width: 40%;">Product</th>
                <th style="width: 15%;">SKU</th>
                @foreach($warehouses as $warehouse)
                    <th class="warehouse-header">{{ $warehouse->name }}</th>
                @endforeach
                <th class="warehouse-header">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($stockData as $productData)
                <tr>
                    <td>{{ $productData['product']->name }}</td>
                    <td>{{ $productData['product']->sku }}</td>
                    @foreach($warehouses as $warehouse)
                        <td class="stock-cell {{ $productData['warehouses'][$warehouse->id]['stock'] > 0 ? 'positive-stock' : 'zero-stock' }}">
                            {{ number_format($productData['warehouses'][$warehouse->id]['stock']) }}
                        </td>
                    @endforeach
                    <td class="stock-cell {{ $productData['total_stock'] > 0 ? 'positive-stock' : 'zero-stock' }}">
                        {{ number_format($productData['total_stock']) }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="section-title">Unit Prices (PHP)</div>
    <table>
        <thead>
            <tr>
                <th style="width: 40%;">Product</th>
                <th style="width: 15%;">SKU</th>
                @foreach($warehouses as $warehouse)
                    <th class="warehouse-header">{{ $warehouse->name }}</th>
                @endforeach
                <th class="warehouse-header">Avg. Unit Price</th>
            </tr>
        </thead>
        <tbody>
            @foreach($stockData as $productData)
                <tr>
                    <td>{{ $productData['product']->name }}</td>
                    <td>{{ $productData['product']->sku }}</td>
                    @foreach($warehouses as $warehouse)
                        <td class="price-cell">
                            @if($productData['warehouses'][$warehouse->id]['stock'] > 0)
                                {{ number_format($productData['warehouses'][$warehouse->id]['unit_price'], 2) }}
                            @else
                                -
                            @endif
                        </td>
                    @endforeach
                    <td class="price-cell">
                        @if($productData['total_stock'] > 0)
                            {{ number_format($productData['avg_unit_price'], 2) }}
                        @else
                            -
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="section-title">Inventory Values (PHP)</div>
    <table>
        <thead>
            <tr>
                <th style="width: 40%;">Product</th>
                <th style="width: 15%;">SKU</th>
                @foreach($warehouses as $warehouse)
                    <th class="warehouse-header">{{ $warehouse->name }}</th>
                @endforeach
                <th class="warehouse-header">Total Value</th>
            </tr>
        </thead>
        <tbody>
            @foreach($stockData as $productData)
                <tr>
                    <td>{{ $productData['product']->name }}</td>
                    <td>{{ $productData['product']->sku }}</td>
                    @foreach($warehouses as $warehouse)
                        <td class="value-cell">
                            @if($productData['warehouses'][$warehouse->id]['stock'] > 0)
                                {{ number_format($productData['warehouses'][$warehouse->id]['value'], 2) }}
                            @else
                                -
                            @endif
                        </td>
                    @endforeach
                    <td class="value-cell">
                        {{ number_format($productData['total_value'], 2) }}
                    </td>
                </tr>
            @endforeach
            <tr class="totals-row">
                <td colspan="{{ 2 + count($warehouses) }}" class="text-right">Total Inventory Value:</td>
                <td class="value-cell">{{ number_format($totalInventoryValue, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        <p>This is an official document of the Inventory Management System.<br>
        This report is generated for COA compliance purposes.<br>
        Values calculated using FIFO (First-In, First-Out) method.<br>
        Generated on {{ $generatedAt }}</p>
    </div>

    <div class="no-print" style="text-align: center; margin-top: 30px;">
        <button onclick="window.print()" style="padding: 10px 20px; font-size: 16px; cursor: pointer;">Print this report</button>
        <button onclick="window.close()" style="padding: 10px 20px; font-size: 16px; cursor: pointer; margin-left: 10px;">Close</button>
    </div>
</body>
</html>
