<?php
namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderWorkflowService
{
    public function create(int $userId, array $cart, array $shipping, array $payment): int
    {
        return DB::transaction(function () use ($userId, $cart, $shipping, $payment) {
            $subtotal = 0.0;
            $items = [];

            foreach ($cart as $row) {
                $quantity = (int) ($row['quantity'] ?? 0);
                if ($quantity < 1) throw new \RuntimeException('Invalid cart quantity.');

                $product = DB::table('products')->where('id', (int) $row['product_id'])->lockForUpdate()->first();
                if (!$product || $product->status !== 'active') throw new \RuntimeException('Product is unavailable.');

                $variantId = !empty($row['variant_id']) ? (int) $row['variant_id'] : null;
                $sku = $row['sku'] ?? $product->sku;

                if ($variantId) {
                    $variant = DB::table('product_variants')
                        ->where('id', $variantId)->where('product_id', $product->id)
                        ->lockForUpdate()->first();
                    if (!$variant) throw new \RuntimeException('Product variant is unavailable.');
                    $price = isset($row['unit_price']) ? (float) $row['unit_price'] : (float) ($variant->price ?? $product->retail_price);
                    $sku = $row['sku'] ?? $variant->sku;
                } else {
                    $price = isset($row['unit_price']) ? (float) $row['unit_price'] : (float) $product->retail_price;
                }

                $lineTotal = $price * $quantity;
                $subtotal += $lineTotal;
                $items[] = [$product, $variantId, $sku, $quantity, $price, $lineTotal];
            }

            $shippingAmount = (float) ($shipping['amount'] ?? 0);
            $total = $subtotal + $shippingAmount;
            $currency = $payment['currency'] ?? config('chacha.brand.default_currency', 'USD');

            $orderId = DB::table('orders')->insertGetId([
                'user_id' => $userId,
                'order_number' => 'CP-' . strtoupper(Str::random(12)),
                'status' => 'confirmed',
                'subtotal' => $subtotal,
                'shipping_amount' => $shippingAmount,
                'discount_amount' => 0,
                'tax_amount' => 0,
                'wallet_amount' => (float) ($payment['wallet_amount'] ?? 0),
                'total_amount' => $total,
                'currency' => $currency,
                'placed_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $inventory = app(InventoryService::class);
            foreach ($items as [$product, $variantId, $sku, $quantity, $price, $lineTotal]) {
                DB::table('order_items')->insert([
                    'order_id' => $orderId,
                    'product_id' => $product->id,
                    'variant_id' => $variantId,
                    'sku' => $sku,
                    'product_title' => $product->title,
                    'quantity' => $quantity,
                    'unit_price' => $price,
                    'total_price' => $lineTotal,
                    'created_at' => now(),
                ]);
                $inventory->deduct((int) $product->id, $variantId, $quantity, 'order', $orderId, $userId);
            }

            DB::table('order_status_history')->insert([
                'order_id' => $orderId,
                'status' => 'confirmed',
                'created_by' => $userId,
                'created_at' => now(),
            ]);

            DB::table('payments')->insert([
                'order_id' => $orderId,
                'provider' => $payment['provider'] ?? 'wallet',
                'method' => $payment['method'] ?? 'wallet',
                'provider_transaction_id' => $payment['reference'] ?? null,
                'amount' => $total,
                'currency' => $currency,
                'status' => 'paid',
                'paid_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return $orderId;
        });
    }
}
