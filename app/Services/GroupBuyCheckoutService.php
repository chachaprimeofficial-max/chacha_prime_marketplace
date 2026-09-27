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
            if (now()->greaterThan($campaign->ends_at)) throw new RuntimeException('Group buying campaign has ended.');
            if ($quantity < 1) throw new RuntimeException('Invalid quantity.');
            $existing = DB::table('group_buying_orders')->where('campaign_id', $campaignId)->where('user_id', $userId)->whereIn('status', ['pending', 'paid'])->first();
            if ($existing) throw new RuntimeException('You have already joined this group.');
            $amount = (float) $campaign->group_price * $quantity;
            return DB::table('group_buying_orders')->insertGetId([
                'campaign_id' => $campaignId,
                'user_id' => $userId,
                'quantity' => $quantity,
                'amount' => $amount,
                'status' => 'pending',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });
    }

    public function finalize(int $campaignId): void
    {
        DB::transaction(function () use ($campaignId) {
            $campaign = DB::table('group_buying_campaigns')->where('id', $campaignId)->lockForUpdate()->first();
            if (!$campaign) return;
            $buyers = (int) DB::table('group_buying_orders')->where('campaign_id', $campaignId)->whereIn('status', ['pending', 'paid'])->sum('quantity');
            $reached = $buyers >= (int) $campaign->required_buyers;
            DB::table('group_buying_campaigns')->where('id', $campaignId)->update(['status' => $reached ? 'successful' : 'failed', 'updated_at' => now()]);
            if ($reached) {
                DB::table('group_buying_orders')->where('campaign_id', $campaignId)->where('status', 'pending')->update(['status' => 'paid', 'updated_at' => now()]);
                return;
            }
            $wallet = app(WalletService::class);
            $orders = DB::table('group_buying_orders')->where('campaign_id', $campaignId)->where('status', 'pending')->get();
            foreach ($orders as $order) {
                $wallet->credit((int) $order->user_id, (float) $order->amount, 'group_refund', 'Automatic refund for failed group campaign #' . $campaignId);
                DB::table('group_buying_orders')->where('id', $order->id)->update(['status' => 'refunded', 'updated_at' => now()]);
            }
        });
    }
}
