<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Movement #{{ $movement->id }}</title>
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
        .movement-id {
            font-size: 18px;
            color: #666;
        }
        .print-date {
            color: #888;
            font-size: 14px;
        }
        .section {
            margin-bottom: 30px;
            page-break-inside: avoid;
        }
        .section-title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 15px;
            border-bottom: 1px solid #eee;
            padding-bottom: 5px;
        }
        .field {
            margin-bottom: 10px;
        }
        .field-label {
            font-weight: bold;
            width: 180px;
            display: inline-block;
        }
        .columns {
            display: flex;
            flex-wrap: wrap;
        }
        .column {
            flex: 1;
            min-width: 300px;
            padding-right: 20px;
        }
        .badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 15px;
            font-size: 14px;
            font-weight: bold;
            color: white;
        }
        .badge-success { background-color: #10b981; }
        .badge-danger { background-color: #ef4444; }
        .badge-warning { background-color: #f59e0b; }
        .badge-info { background-color: #3b82f6; }
        .badge-gray { background-color: #6b7280; }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table th, table td {
            padding: 8px 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        table th {
            background-color: #f9fafb;
            font-weight: bold;
        }
        .text-right {
            text-align: right;
        }

        .footer {
            margin-top: 50px;
            text-align: center;
            font-size: 14px;
            color: #888;
            border-top: 1px solid #ddd;
            padding-top: 20px;
        }

        @media print {
            body {
                padding: 0;
                font-size: 12px;
            }
            .no-print {
                display: none;
            }
            .section {
                page-break-inside: avoid;
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
        <div class="movement-id">Product Movement #{{ $movement->id }}</div>
        <div class="print-date">Printed on: {{ now()->format('F d, Y h:i A') }}</div>
    </div>

    <div class="section">
        <div class="section-title">Movement Details</div>
        <div class="columns">
            <div class="column">
                <div class="field">
                    <span class="field-label">Product:</span>
                    <span>{{ $movement->product->name }}</span>
                </div>
                <div class="field">
                    <span class="field-label">Quantity:</span>
                    <span>{{ number_format(abs($displayQuantity)) }}</span>
                </div>
                <div class="field">
                    <span class="field-label">Created By:</span>
                    <span>{{ $movement->user->name }}</span>
                </div>

                @if ($movement->movement_type ==='entry')
                <div class="field">
                    <span class="field-label">Supplier:</span>
                    <span>{{ $movement->supplier?->name ?? 'N/A' }}</span>
                </div>
                @endif
            </div>
            <div class="column">
                <div class="field">
                    <span class="field-label">Movement Type:</span>
                    <span class="badge badge-{{ match($movement->movement_type) {
                        'entry' => 'success',
                        'exit' => 'danger',
                        'transfer' => 'warning',
                        'adjustment' => 'info',
                        default => 'gray',
                    } }}">{{ ucfirst($movement->movement_type) }}</span>
                </div>
                <div class="field">
                    <span class="field-label">Date & Time:</span>
                    <span>{{ $movement->created_at->format('M d, Y h:i A') }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Warehouse Information</div>
        <div class="columns">
            <div class="column">
                <div class="field">
                    <span class="field-label">{{ $movement->movement_type === 'transfer' ? 'To Warehouse:' : 'Warehouse:' }}</span>
                    <span>{{ $movement->warehouse->name }}</span>
                </div>
            </div>
            @if($movement->movement_type === 'transfer' && $sourceWarehouse)
            <div class="column">
                <div class="field">
                    <span class="field-label">From Warehouse:</span>
                    <span>{{ $sourceWarehouse->name }}</span>
                </div>
            </div>
            @endif
        </div>
    </div>

    <div class="section">
        <div class="section-title">Price Information</div>
        <div class="columns">
            <div class="column">
                <div class="field">
                    <span class="field-label">Unit Price:</span>
                    <span>PHP {{ number_format($movement->unit_price, 2) }}</span>
                </div>
                <div class="field">
                    <span class="field-label">Total Price:</span>
                    <span>PHP {{ number_format($movement->total_price, 2) }}</span>
                </div>
            </div>
            <div class="column">
                <div class="field">
                    <span class="field-label">Reference/Invoice #:</span>
                    <span>{{ $movement->price_reference ?? 'N/A' }}</span>
                </div>
            </div>
        </div>
    </div>

    @if(in_array($movement->movement_type, ['transfer', 'exit']) && $batches->count() > 0)
    <div class="section">
        <div class="section-title">COA Price Details</div>
        <div class="columns">
            <div class="column">
                <div class="field">
                    <span class="field-label">Calculated Unit Price (FIFO):</span>
                    <span>PHP {{ number_format($calculatedUnitPrice, 2) }}</span>
                </div>
            </div>
            <div class="column">
                <div class="field">
                    <span class="field-label">Calculated Total Price:</span>
                    <span>PHP {{ number_format($calculatedTotalPrice, 2) }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">FIFO Batch Breakdown</div>
        <table>
            <thead>
                <tr>
                    <th>Batch</th>
                    <th>Date</th>
                    <th class="text-right">Quantity</th>
                    <th class="text-right">Unit Price</th>
                    <th class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($batches as $batch)
                <tr>
                    <td>#{{ $batch->productBatch?->id }}</td>
                    <td>{{ $batch->productBatch?->created_at->format('M d, Y') }}</td>
                    <td class="text-right">{{ number_format($batch->quantity_taken) }}</td>
                    <td class="text-right">PHP {{ number_format($batch->unit_price, 2) }}</td>
                    <td class="text-right">PHP {{ number_format($batch->total_price, 2) }}</td>
                </tr>
                @endforeach
                <tr>
                    <th colspan="2" class="text-right">Total:</th>
                    <th class="text-right">{{ number_format($batches->sum('quantity_taken')) }}</th>
                    <th class="text-right"></th>
                    <th class="text-right">PHP {{ number_format($batches->sum('total_price'), 2) }}</th>
                </tr>
            </tbody>
        </table>
    </div>
    @endif

    @if($movement->notes)
    <div class="section">
        <div class="section-title">Notes</div>
        <p>{{ $movement->notes }}</p>
    </div>
    @endif

    <div class="footer">
        <p>This is an official document of the Inventory Management System.<br>Generated on {{ now()->format('F d, Y h:i:s A') }}</p>
        <p>Developed by Andrean Earl Erasmo</p>
        <p class="disclaimer">Disclaimer: This software has not been fully tested and should not be used as the sole basis for critical business decisions.</p>
    </div>

    <div class="no-print" style="text-align: center; margin-top: 30px;">
        <button onclick="window.print()" style="padding: 10px 20px; font-size: 16px; cursor: pointer;">Print this page</button>
    </div>

</body>
</html>

