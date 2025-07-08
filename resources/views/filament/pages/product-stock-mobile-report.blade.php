<x-filament::page>
    <div class="space-y-4">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
            <h1 class="text-xl font-bold text-primary-700 dark:text-primary-300">Product Stock Report <span class="font-normal text-lg">as of {{ $this->getFormattedAsOfDate() }}</span></h1>

            <div class="flex flex-col sm:flex-row gap-3">
                <div class="mt-2 md:mt-0">
                    {{ $this->form }}
                </div>

                <x-filament::button
                    type="button"
                    color="success"
                    icon="heroicon-o-printer"
                    class="mt-3 print-button"
                    tag="a"
                    :href="route('product-stock.print', ['as_of_date' => $this->asOfDate])"
                    target="_blank">
                    Print Report
                </x-filament::button>
            </div>
        </div>

        <div class="print-header" style="display: none;">
            <h1 style="font-size: 20px; font-weight: bold; text-align: center; margin-bottom: 5px;">Inventory Management System</h1>
            <h2 style="font-size: 16px; font-weight: normal; text-align: center; margin-bottom: 20px;">Product Stock Report as of {{ $this->getFormattedAsOfDate() }}</h2>
        </div>

        <div class="flex flex-col gap-4">
            @php $products = $this->getProducts(); @endphp
            @foreach ($products as $product)
                <div class="rounded-lg shadow bg-white dark:bg-gray-900 p-4 flex flex-col gap-2">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                        <div>
                            <div class="text-base font-semibold text-gray-900 dark:text-gray-100">{{ $product->name }}</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">SKU: {{ $product->sku }}</div>
                        </div>
                        <div class="flex flex-wrap gap-2 mt-2 sm:mt-0">
                            @foreach ($warehouses as $warehouse)
                                <div class="flex flex-col items-center bg-gray-50 dark:bg-gray-800 rounded px-3 py-1 min-w-[80px]">
                                    <span class="text-xs text-gray-500 dark:text-gray-400">{{ $warehouse->name }}</span>
                                    <span class="text-lg font-bold {{ ($this->getStock($product->id, $warehouse->id) ?? 0) > 0 ? 'text-green-600 dark:text-green-400' : 'text-gray-400 dark:text-gray-500' }}">
                                        {{ $this->getStock($product->id, $warehouse->id) ?? 0 }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-4 no-print">
            {{ $products->links() }}
        </div>

        <div class="print-footer" style="display: none;">
            <p style="text-align: center; font-size: 12px; margin-top: 30px;">
                This report was generated on {{ now()->format('F d, Y h:i A') }}.<br>
                This is an official document for COA compliance.
            </p>
        </div>
    </div>

    <style>
        @media print {
            body {
                padding: 20px;
                font-family: Arial, sans-serif;
            }
            .print-header, .print-footer {
                display: block !important;
            }
            .no-print, .print-button, .filament-sidebar, .filament-topbar, .filament-main-topbar,
            form, [wire\:submit], .filament-button, nav, footer {
                display: none !important;
            }
            .filament-main {
                padding: 0 !important;
                margin: 0 !important;
            }
            .rounded-lg {
                border: 1px solid #eee !important;
                margin-bottom: 10px !important;
                page-break-inside: avoid !important;
            }
            .bg-gray-50 {
                background-color: #f9f9f9 !important;
                print-color-adjust: exact !important;
                -webkit-print-color-adjust: exact !important;
            }
            .text-green-600 {
                color: #059669 !important;
                print-color-adjust: exact !important;
                -webkit-print-color-adjust: exact !important;
            }
            .text-gray-400 {
                color: #9ca3af !important;
                print-color-adjust: exact !important;
                -webkit-print-color-adjust: exact !important;
            }
            .shadow {
                box-shadow: none !important;
            }
            @page {
                size: portrait;
                margin: 1.5cm;
            }
        }
    </style>
</x-filament::page>
