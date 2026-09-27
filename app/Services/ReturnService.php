<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use RuntimeException;

class ReturnService
{
    public function create(int $userId, int $orderId, int $orderItemId, string $type, string $reason, array $media = [], ?string $shippingLabel = null): int
    {
        if (!in_array($type, ['refund', 'replacement'], true)) throw new RuntimeException('Invalid return type.');
        if (!$reason) throw new RuntimeException('Return reason is required.');

        $item = DB::table('order_items as oi')->join('orders as o', 'o.id', '=', 'oi.order_id')
            ->where('oi.id', $orderItemId)->where('oi.order_id', $orderId)->where('o.user_id', $userId)->first();
        if (!$item) throw new RuntimeException('Order item not found.');

        $order = DB::table('orders')->where('id', $orderId)->first();
        if (!$order || !$order->delivered_at || now()->diffInDays($order->delivered_at) > 7) throw new RuntimeException('Return window has expired.');

        return DB::transaction(function () use ($userId, $orderId, $orderItemId, $type, $reason, $media, $shippingLabel) {
            return DB::table('returns')->insertGetId([
                'user_id' => $userId, 'order_id' => $orderId, 'order_item_id' => $orderItemId,
                'type' => $type, 'reason' => $reason, 'status' => 'requested',
                'media_json' => json_encode($media), 'shipping_label_path' => $shippingLabel,
                'created_at' => now(), 'updated_at' => now(),
            ]);
        });
    }
}
