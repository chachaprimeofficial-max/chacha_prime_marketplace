<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use RuntimeException;

class GroupBuyingService
{
    public function join(int $userId, int $campaignId, float $amount): void
    {
        DB::transaction(function () use ($userId, $campaignId, $amount) {
            $campaign = DB::table('group_buying_campaigns')->where('id', $campaignId)->lockForUpdate()->first();
            if (!$campaign || $campaign->status !== 'active' || now()->lt($campaign->starts_at) || now()->gt($campaign->expires_at)) {
                throw new RuntimeException('Group buying campaign is not available.');
            }
            if ($campaign->current_buyers >= $campaign->required_buyers) {
                throw new RuntimeException('Group buying target has already been reached.');
            }
            DB::table('group_buying_orders')->insert([
                'campaign_id' => $campaignId,
                'user_id' => $userId,
                'amount' => $amount,
                'status' => 'pending',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            DB::table('group_buying_campaigns')->where('id', $campaignId)->increment('current_buyers');
        });
    }

    public function finalize(int $campaignId): string
    {
        return DB::transaction(function () use ($campaignId) {
            $campaign = DB::table('group_buying_campaigns')->where('id', $campaignId)->lockForUpdate()->first();
            if (!$campaign || $campaign->status !== 'active') throw new RuntimeException('Campaign is not active.');
            $met = $campaign->current_buyers >= $campaign->required_buyers;
            $participants = DB::table('group_buying_orders')->where('campaign_id', $campaignId)->where('status', 'pending')->lockForUpdate()->get();
            if ($met) {
                DB::table('group_buying_campaigns')->where('id', $campaignId)->update(['status' => 'successful', 'updated_at' => now()]);
                foreach ($participants as $participant) {
                    DB::table('group_buying_orders')->where('id', $participant->id)->update(['status' => 'confirmed', 'updated_at' => now()]);
                }
                return 'successful';
            }
            $wallet = app(WalletService::class);
            foreach ($participants as $participant) {
                $wallet->credit((int)$participant->user_id, (float)$participant->amount, 'group_refund', 'Group buying campaign #'.$campaignId.' did not reach its required buyer condition.');
                DB::table('group_buying_orders')->where('id', $participant->id)->update(['status' => 'refunded', 'updated_at' => now()]);
            }
            DB::table('group_buying_campaigns')->where('id', $campaignId)->update(['status' => 'failed', 'updated_at' => now()]);
            return 'refunded';
        });
    }

    public function failCampaign(int $campaignId): void
    {
        $this->finalize($campaignId);
    }
}
