<?php
namespace App\Services;

use Illuminate\Support\Facades\DB;
use RuntimeException;

class ShipmentService
{
    private array $transitions = [
        'pending' => ['packed', 'cancelled'],
        'packed' => ['dispatched', 'exception'],
        'dispatched' => ['in_transit', 'out_for_delivery', 'exception'],
        'in_transit' => ['out_for_delivery', 'delivered', 'exception'],
        'out_for_delivery' => ['delivered', 'exception'],
        'delivered' => ['returned'],
        'returned' => [],
        'exception' => ['pending', 'packed', 'dispatched', 'in_transit', 'out_for_delivery'],
    ];

    public function create(int $orderId, array $data): int
    {
        return DB::transaction(function () use ($orderId, $data) {
            $order = DB::table('orders')->where('id', $orderId)->lockForUpdate()->firstOrFail();

            if (in_array($order->status, ['cancelled', 'returned', 'refunded'], true)) {
                throw new RuntimeException('A shipment cannot be created for a closed order.');
            }

            $existing = DB::table('shipments')
                ->where('order_id', $orderId)
                ->whereNotIn('status', ['returned'])
                ->first();

            if ($existing) return (int) $existing->id;

            $id = DB::table('shipments')->insertGetId([
                'order_id' => $orderId,
                'carrier' => $data['carrier'] ?? null,
                'service' => $data['service'] ?? null,
                'tracking_number' => $data['tracking_number'] ?? null,
                'status' => 'pending',
                'estimated_delivery_date' => $data['estimated_delivery_date'] ?? null,
                'notes' => $data['notes'] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $this->event($id, 'pending', null, 'Shipment created');
            return $id;
        });
    }

    public function updateStatus(int $shipmentId, string $status, ?string $location = null, ?string $message = null): void
    {
        DB::transaction(function () use ($shipmentId, $status, $location, $message) {
            $s = DB::table('shipments')->where('id', $shipmentId)->lockForUpdate()->firstOrFail();

            if (!array_key_exists($status, array_flip([
                'pending', 'packed', 'dispatched', 'in_transit',
                'out_for_delivery', 'delivered', 'returned', 'exception'
            ]))) {
                throw new RuntimeException('Invalid shipment status.');
            }

            if ($status !== $s->status && !in_array($status, $this->transitions[$s->status] ?? [], true)) {
                throw new RuntimeException('Invalid shipment status transition from ' . $s->status . ' to ' . $status . '.');
            }

            $update = ['status' => $status, 'updated_at' => now()];
            if ($status === 'dispatched' && !$s->shipped_at) $update['shipped_at'] = now();
            if ($status === 'delivered' && !$s->delivered_at) $update['delivered_at'] = now();

            DB::table('shipments')->where('id', $shipmentId)->update($update);

            $order = DB::table('orders')->where('id', $s->order_id)->lockForUpdate()->firstOrFail();
            $orderStatus = match ($status) {
                'packed' => 'packing',
                'dispatched' => 'shipped',
                'in_transit' => 'in_transit',
                'out_for_delivery' => 'out_for_delivery',
                'delivered' => 'delivered',
                'returned' => 'returned',
                default => null,
            };

            if ($orderStatus && $order->status !== $orderStatus) {
                DB::table('orders')->where('id', $order->id)->update([
                    'status' => $orderStatus,
                    'delivered_at' => $status === 'delivered' ? ($update['delivered_at'] ?? now()) : $order->delivered_at,
                    'updated_at' => now(),
                ]);

                DB::table('order_status_history')->insert([
                    'order_id' => $order->id,
                    'status' => $orderStatus,
                    'note' => 'Synchronized from shipment status.',
                    'created_at' => now(),
                ]);
            }

            $this->event($shipmentId, $status, $location, $message);
        });
    }

    private function event(int $shipmentId, string $status, ?string $location, ?string $message): void
    {
        DB::table('shipment_events')->insert([
            'shipment_id' => $shipmentId,
            'status' => $status,
            'location' => $location,
            'message' => $message,
            'event_at' => now(),
            'created_at' => now(),
        ]);
    }
}
