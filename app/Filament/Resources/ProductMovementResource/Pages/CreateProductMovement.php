<?php

namespace App\Filament\Resources\ProductMovementResource\Pages;

use App\Filament\Resources\ProductMovementResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateProductMovement extends CreateRecord
{
    protected static string $resource = ProductMovementResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function handleRecordCreation(array $data): Model
    {
        // Set the current user as the creator of the movement
        $data['user_id'] = auth()->id();

        if ($data['movement_type'] === 'transfer') {
            // Create exit movement from source warehouse
            $exitData = [
                'product_id' => $data['product_id'],
                'warehouse_id' => $data['from_warehouse_id'],
                'quantity' => -abs($data['quantity']),
                'movement_type' => 'transfer',
                'notes' => ($data['notes'] ?? '') . ' (Transfer out) [from_warehouse_id:' . $data['from_warehouse_id'] . ']',
                'user_id' => $data['user_id'],
            ];

            // Create an exit movement
            $exitMovement = static::getModel()::create($exitData);

            // Create entry movement to destination warehouse
            $entryData = [
                'product_id' => $data['product_id'],
                'warehouse_id' => $data['to_warehouse_id'],
                'quantity' => abs($data['quantity']),
                'movement_type' => 'transfer',
                'price_reference' => $data['price_reference'] ?? null,
                'notes' => ($data['notes'] ?? '') . ' (Transfer in) [from_warehouse_id:' . $data['from_warehouse_id'] . ']',
                'user_id' => $data['user_id'],
            ];

            // Create entry movement
            return static::getModel()::create($entryData);
        }

        // Adjust the quantity based on movement type if needed
        if ($data['movement_type'] === 'exit' && $data['quantity'] > 0) {
            $data['quantity'] = -$data['quantity'];
        }

        return static::getModel()::create($data);
    }
}
