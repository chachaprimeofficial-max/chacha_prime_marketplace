<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use RuntimeException;

class CheckoutService
{
    public function calculate(array $items, float $shipping = 0, float $discount = 0, float $wallet = 0): array
    {
        $subtotal = collect($items)->sum(fn ($item) => (float) $item['price'] * (int) $item['quantity']);
        $wallet = min(max(0, $wallet), max(0, $subtotal + $shipping - $discount));
        $total = max(0, $subtotal + $shipping - $discount - $wallet);
        return compact('subtotal', 'shipping', 'discount', 'wallet', 'total');
    }

    public function createOrder(int $userId, array $items, array $totals, string $type = 'retail', ?string $shippingAddress = null): int
    {
        return DB::transaction(function () use ($userId, $items, $totals, $type, $shippingAddress) {
            if (!$items) throw new RuntimeException('Cart is empty.');

            $number = 'CP-' . now()->format('YmdHis') . '-' . random_int(100, 999);
            $orderId = DB::table('orders')->insertGetId([
                'order_number' => $number,
                'user_id' => $userId,
                'status' => 'pending',
                'subtotal' => $totals['subtotal'],
                'shipping_amount' => $totals['shipping'],
                'discount_amount' => $totals['discount'],
                'total_amount' => $totals['total'],
                'currency' => config('chacha.brand.default_currency', 'USD'),
                'payment_status' => $totals['total'] <= 0 ? 'paid' : 'pending',
                'shipping_address' => $shippingAddress,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            foreach ($items as $item) {
                DB::table('order_items')->insert([
                    'order_id' => $orderId,
                    'product_id' => $item['product_id'],
                    'variation_id' => $item['variant_id'] ?? $item['variation_id'] ?? null,
                    'sku' => $item['sku'] ?? '',
                    'title' => $item['title'] ?? 'Product',
                    'quantity' => (int) $item['quantity'],
                    'unit_price' => (float) $item['price'],
                    'total_price' => (float) $item['price'] * (int) $item['quantity'],
                    'created_at' => now(),
                ]);
            }

            DB::table('order_tracking_events')->insert([
                'order_id' => $orderId,
                'status' => 'pending',
                'note' => 'Order created.',
                'created_at' => now(),
            ]);

            return $orderId;
        });
    }
}
