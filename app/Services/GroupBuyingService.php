<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class GroupBuyingService
{
    public function failCampaign(int $campaignId): void
    {
        DB::transaction(function () use ($campaignId) {
            $campaign = DB::table('group_buying_campaigns')->where('id', $campaignId)->lockForUpdate()->first();
            if (!$campaign || !in_array($campaign->status, ['active', 'scheduled'], true)) {
                return;
            }

            DB::table('group_buying_campaigns')->where('id', $campaignId)->update([
                'status' => 'failed',
                'updated_at' => now(),
            ]);

            $wallet = app(WalletService::class);
            $participants = DB::table('group_buying_orders')->where('campaign_id', $campaignId)->where('status', 'pending')->get();
            foreach ($participants as $participant) {
                $wallet->credit(
                    (int) $participant->user_id,
                    (float) $participant->amount,
                    'group_refund',
                    'Group buying campaign #' . $campaignId . ' did not reach its required buyer condition.'
                );
                DB::table('group_buying_orders')->where('id', $participant->id)->update(['status' => 'refunded']);
            }
        });
    }
}
