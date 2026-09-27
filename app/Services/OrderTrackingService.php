<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use RuntimeException;

class OrderTrackingService
{
    private const STATUSES = ['placed','confirmed','packing','shipped','in_transit','delivered'];

    public function update(int $orderId, string $status, ?string $courier = null, ?string $trackingNumber = null, ?string $note = null): void
    {
        if (!in_array($status, self::STATUSES, true)) throw new RuntimeException('Invalid order status.');
        DB::transaction(function () use ($orderId, $status, $courier, $trackingNumber, $note) {
            $order = DB::table('orders')->where('id', $orderId)->lockForUpdate()->first();
            if (!$order) throw new RuntimeException('Order not found.');
            $data = ['status' => $status, 'updated_at' => now()];
            if ($courier) $data['courier'] = $courier;
            if ($trackingNumber) $data['tracking_number'] = $trackingNumber;
            if ($status === 'delivered') $data['delivered_at'] = now();
            DB::table('orders')->where('id', $orderId)->update($data);
            DB::table('order_tracking_events')->insert([
                'order_id' => $orderId,
                'status' => $status,
                'courier' => $courier,
                'tracking_number' => $trackingNumber,
                'note' => $note,
                'created_at' => now(),
            ]);
        });
    }
}
