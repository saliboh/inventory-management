<x-filament::page>
    <div class="space-y-4">
        <h1 class="text-xl font-bold text-primary-700 dark:text-primary-300 mb-4">These are the updated product stock quantities per warehouse.</h1>
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
        <div class="mt-4">
            {{ $products->links() }}
        </div>
    </div>
</x-filament::page>
