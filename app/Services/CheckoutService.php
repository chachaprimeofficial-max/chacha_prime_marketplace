<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use RuntimeException;

class CheckoutService
{
    public function calculate(array $items, float $shipping = 0, float $discount = 0, float $wallet = 0): array
    {
        $subtotal = collect($items)->sum(fn ($item) => (float) $item['price'] * (int) $item['quantity']);
        $wallet = min(max(0, $wallet), $subtotal + $shipping - $discount);
        $total = max(0, $subtotal + $shipping - $discount - $wallet);
        return compact('subtotal', 'shipping', 'discount', 'wallet', 'total');
    }

    public function createOrder(int $userId, array $items, array $totals, string $type = 'retail'): int
    {
        return DB::transaction(function () use ($userId, $items, $totals, $type) {
            if (!$items) throw new RuntimeException('Cart is empty.');
            $number = 'CP-' . now()->format('YmdHis') . '-' . random_int(100, 999);
            $orderId = DB::table('orders')->insertGetId([
                'order_number' => $number,
                'user_id' => $userId,
                'order_type' => $type,
                'status' => 'pending',
                'subtotal' => $totals['subtotal'],
                'shipping_amount' => $totals['shipping'],
                'discount_amount' => $totals['discount'],
                'wallet_amount' => $totals['wallet'],
                'total_amount' => $totals['total'],
                'currency' => config('chacha.brand.default_currency', 'USD'),
                'placed_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            foreach ($items as $item) {
                DB::table('order_items')->insert([
                    'order_id' => $orderId,
                    'product_id' => $item['product_id'],
                    'variant_id' => $item['variant_id'] ?? null,
                    'sku' => $item['sku'] ?? '',
                    'product_title' => $item['title'] ?? 'Product',
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['price'],
                    'total_price' => $item['price'] * $item['quantity'],
                    'created_at' => now(),
                ]);
            }
            return $orderId;
        });
    }
}
