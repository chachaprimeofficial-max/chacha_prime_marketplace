<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use RuntimeException;

class GroupBuyCheckoutService
{
    public function join(int $userId, int $campaignId, int $quantity = 1): int
    {
        return DB::transaction(function () use ($userId, $campaignId, $quantity) {
            $campaign = DB::table('group_buying_campaigns')->where('id', $campaignId)->lockForUpdate()->first();
            if (!$campaign || $campaign->status !== 'active') throw new RuntimeException('Group buying campaign is not active.');
            if ($campaign->expires_at && now()->greaterThan($campaign->expires_at)) throw new RuntimeException('Group buying campaign has ended.');
            if ($quantity < 1) throw new RuntimeException('Invalid quantity.');

            $existing = DB::table('group_buying_orders')->where('campaign_id', $campaignId)->where('user_id', $userId)->whereIn('status', ['pending', 'paid'])->first();
            if ($existing) throw new RuntimeException('You have already joined this group.');

            $amount = (float) $campaign->price * $quantity;

            app(WalletService::class)->debit(
                $userId,
                $amount,
                'group_purchase',
                'Immediate payment for group campaign #' . $campaignId,
                'group_buying_campaign',
                $campaignId
            );

            $id = DB::table('group_buying_orders')->insertGetId([
                'campaign_id' => $campaignId,
                'user_id' => $userId,
                'quantity' => $quantity,
                'amount' => $amount,
                'status' => 'paid',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('group_buying_campaigns')->where('id', $campaignId)->update([
                'current_buyers' => DB::raw('current_buyers + ' . (int) $quantity),
                'updated_at' => now(),
            ]);

            return $id;
        });
    }

    public function finalize(int $campaignId): void
    {
        DB::transaction(function () use ($campaignId) {
            $campaign = DB::table('group_buying_campaigns')->where('id', $campaignId)->lockForUpdate()->first();
            if (!$campaign) return;

            $buyers = (int) DB::table('group_buying_orders')
                ->where('campaign_id', $campaignId)
                ->where('status', 'paid')
                ->sum('quantity');

            $reached = $buyers >= (int) $campaign->required_buyers;

            if ($reached) {
                DB::table('group_buying_campaigns')->where('id', $campaignId)->update([
                    'current_buyers' => $buyers,
                    'status' => 'successful',
                    'updated_at' => now(),
                ]);
                return;
            }

            $orders = DB::table('group_buying_orders')->where('campaign_id', $campaignId)->where('status', 'paid')->get();
            $wallet = app(WalletService::class);

            foreach ($orders as $order) {
                $wallet->credit(
                    (int) $order->user_id,
                    (float) $order->amount,
                    'group_refund',
                    'Automatic refund for failed group campaign #' . $campaignId,
                    'group_buying_campaign',
                    $campaignId
                );

                DB::table('group_buying_orders')->where('id', $order->id)->update([
                    'status' => 'refunded',
                    'updated_at' => now(),
                ]);
            }

            DB::table('group_buying_campaigns')->where('id', $campaignId)->update([
                'status' => 'failed',
                'updated_at' => now(),
            ]);
        });
    }
}
