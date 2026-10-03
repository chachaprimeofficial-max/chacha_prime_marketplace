<?php
namespace App\Services;

use Illuminate\Support\Facades\DB;
use RuntimeException;

class ReturnService
{
    public function create(int $userId, int $orderId, int $orderItemId, string $type, string $reason, array $media = [], ?string $shippingLabel = null): int
    {
        if (!in_array($type, ['refund', 'replacement'], true)) {
            throw new RuntimeException('Invalid return type.');
        }

        return DB::transaction(function () use ($userId, $orderId, $orderItemId, $type, $reason, $media, $shippingLabel) {
            $item = DB::table('order_items as oi')
                ->join('orders as o', 'o.id', '=', 'oi.order_id')
                ->where('oi.id', $orderItemId)
                ->where('oi.order_id', $orderId)
                ->where('o.user_id', $userId)
                ->lockForUpdate()
                ->first();

            if (!$item) throw new RuntimeException('Order item not found.');

            $order = DB::table('orders')->where('id', $orderId)->first();
            if (!$order || $order->status !== 'delivered' || !$order->delivered_at) {
                throw new RuntimeException('Order is not eligible for return.');
            }

            $deadline = \Carbon\Carbon::parse($order->delivered_at)->addWeekdays(7)->endOfDay();
            if (now()->greaterThan($deadline)) throw new RuntimeException('Return window has expired.');

            $alreadyReturned = (float) DB::table('return_items as ri')
                ->join('returns as r', 'r.id', '=', 'ri.return_id')
                ->where('ri.order_item_id', $orderItemId)
                ->whereIn('r.status', ['received', 'inspected', 'refunded', 'closed'])
                ->sum('ri.quantity');

            if ($alreadyReturned + (float) $item->quantity > (float) $item->quantity) {
                throw new RuntimeException('The requested quantity has already been returned.');
            }

            $active = DB::table('returns')
                ->where('order_id', $orderId)
                ->whereIn('status', ['requested', 'approved', 'received', 'inspected'])
                ->whereExists(function ($q) use ($orderItemId) {
                    $q->select(DB::raw(1))->from('return_items')
                        ->whereColumn('return_items.return_id', 'returns.id')
                        ->where('return_items.order_item_id', $orderItemId);
                })->exists();

            if ($active) throw new RuntimeException('A return is already in progress for this item.');

            $returnId = DB::table('returns')->insertGetId([
                'user_id' => $userId,
                'type' => $type,
                'order_id' => $orderId,
                'reason' => $reason,
                'description' => $reason,
                'evidence' => json_encode(['media' => $media, 'shipping_label' => $shippingLabel]),
                'status' => 'requested',
                'refund_method' => $type === 'refund' ? 'wallet' : null,
                'refund_amount' => $type === 'refund' ? (float) $item->total_price : 0,
                'requested_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('return_items')->insert([
                'return_id' => $returnId,
                'order_item_id' => $orderItemId,
                'quantity' => $item->quantity,
                'reason' => $reason,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return $returnId;
        });
    }

    public function approve(int $returnId, string $refundMethod = 'wallet'): void
    {
        if (!in_array($refundMethod, ['wallet', 'original_payment'], true)) {
            throw new RuntimeException('Invalid refund method.');
        }

        DB::transaction(function () use ($returnId, $refundMethod) {
            $r = DB::table('returns')->where('id', $returnId)->lockForUpdate()->firstOrFail();
            if ($r->status !== 'requested') throw new RuntimeException('Return is not awaiting approval.');

            DB::table('returns')->where('id', $returnId)->update([
                'status' => 'approved',
                'refund_method' => $refundMethod,
                'approved_at' => now(),
                'updated_at' => now(),
            ]);
        });
    }

    public function reject(int $returnId, string $reason = ''): void
    {
        DB::table('returns')->where('id', $returnId)->where('status', 'requested')->update([
            'status' => 'rejected',
            'description' => $reason,
            'updated_at' => now(),
        ]);
    }

    public function markReceived(int $returnId): void
    {
        DB::transaction(function () use ($returnId) {
            $r = DB::table('returns')->where('id', $returnId)->lockForUpdate()->firstOrFail();
            if ($r->status !== 'approved') {
                throw new RuntimeException('Only approved returns can be marked received.');
            }
            DB::table('returns')->where('id', $returnId)->update([
                'status' => 'received',
                'updated_at' => now(),
            ]);
        });
    }

    public function refund(int $returnId): void
    {
        DB::transaction(function () use ($returnId) {
            $r = DB::table('returns')->where('id', $returnId)->lockForUpdate()->firstOrFail();

            // Physical stock is restored only after the returned item is received.
            if (!in_array($r->status, ['received', 'inspected'], true) || (float) $r->refund_amount <= 0) {
                throw new RuntimeException('Return must be received/inspected before refund.');
            }

            $processed = DB::table('refund_transactions')
                ->where('return_id', $returnId)
                ->where('status', 'processed')
                ->exists();

            if ($processed) return;

            $items = DB::table('return_items as ri')
                ->join('order_items as oi', 'oi.id', '=', 'ri.order_item_id')
                ->where('ri.return_id', $returnId)
                ->get(['oi.product_id', 'oi.variant_id', 'ri.quantity']);

            $inventory = app(InventoryService::class);
            foreach ($items as $item) {
                $restored = DB::table('inventory_movements')
                    ->where('reference_type', 'return')
                    ->where('reference_id', $returnId)
                    ->where('product_id', $item->product_id)
                    ->where(function ($q) use ($item) {
                        $q->where('variant_id', $item->variant_id);
                        if ($item->variant_id === null) $q->orWhereNull('variant_id');
                    })->exists();

                if (!$restored) {
                    $inventory->restore(
                        (int) $item->product_id,
                        $item->variant_id ? (int) $item->variant_id : null,
                        (int) $item->quantity,
                        'return',
                        'return',
                        $returnId
                    );
                }
            }

            $method = $r->refund_method ?: 'wallet';
            $tx = DB::table('refund_transactions')->insertGetId([
                'return_id' => $returnId,
                'method' => $method,
                'amount' => $r->refund_amount,
                'status' => 'pending',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            try {
                if ($method === 'wallet') {
                    app(WalletService::class)->credit(
                        (int) $r->user_id,
                        (float) $r->refund_amount,
                        'refund',
                        'Return refund',
                        'return',
                        $returnId
                    );
                } else {
                    $payment = DB::table('payments')
                        ->where('order_id', $r->order_id)
                        ->whereIn('status', ['paid', 'partially_refunded'])
                        ->latest('id')
                        ->lockForUpdate()
                        ->first();

                    if (!$payment) throw new RuntimeException('No refundable payment was found for this order.');

                    app(PaymentService::class)->refund(
                        (int) $payment->id,
                        (float) $r->refund_amount,
                        'Return refund #' . $returnId
                    );
                }

                DB::table('refund_transactions')->where('id', $tx)->update([
                    'status' => 'processed',
                    'processed_at' => now(),
                    'updated_at' => now(),
                ]);

                DB::table('returns')->where('id', $returnId)->update([
                    'status' => 'refunded',
                    'refunded_at' => now(),
                    'updated_at' => now(),
                ]);

                // Mark the order returned only when all of its items have been
                // fully covered by completed return quantities.
                $ordered = (float) DB::table('order_items')->where('order_id', $r->order_id)->sum('quantity');
                $returned = (float) DB::table('return_items as ri')
                    ->join('returns as rr', 'rr.id', '=', 'ri.return_id')
                    ->join('order_items as oi', 'oi.id', '=', 'ri.order_item_id')
                    ->where('oi.order_id', $r->order_id)
                    ->whereIn('rr.status', ['refunded', 'closed'])
                    ->sum('ri.quantity');

                if ($ordered > 0 && $returned >= $ordered) {
                    DB::table('orders')->where('id', $r->order_id)->update([
                        'status' => 'refunded',
                        'updated_at' => now(),
                    ]);
                } else {
                    DB::table('orders')->where('id', $r->order_id)->where('status', 'delivered')->update([
                        'status' => 'returned',
                        'updated_at' => now(),
                    ]);
                }
            } catch (\Throwable $e) {
                DB::table('refund_transactions')->where('id', $tx)->update([
                    'status' => 'failed',
                    'updated_at' => now(),
                ]);
                throw $e;
            }
        });
    }
}
