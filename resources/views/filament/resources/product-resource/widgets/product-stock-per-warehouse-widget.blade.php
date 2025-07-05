<x-filament::widget>
    <div class="space-y-4">
        <h2 class="text-lg font-bold">Stock Per Warehouse</h2>
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-900 rounded shadow">
            <thead class="bg-gray-50 dark:bg-gray-800">
                <tr>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-700 dark:text-gray-200 uppercase">Warehouse</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-700 dark:text-gray-200 uppercase">Current Stock</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($this->getWarehousesWithStock() as $warehouse)
                    <tr>
                        <td class="px-4 py-2 text-sm text-gray-900 dark:text-gray-100">{{ $warehouse['name'] }}</td>
                        <td class="px-4 py-2 text-sm font-semibold text-green-700 dark:text-green-400">{{ $warehouse['stock'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</x-filament::widget>

