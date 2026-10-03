<?php

namespace App\Services;

class GroupBuyCheckoutService
{
    public function __construct(private GroupBuyingService $groups)
    {
    }

    /**
     * Canonical group-buy checkout delegates to GroupBuyingService.
     * The current schema supports one unit per participant.
     */
    public function join(int $userId, int $campaignId, int $quantity = 1): int
    {
        if ($quantity !== 1) {
            throw new \RuntimeException('The current group-buy campaign supports one unit per participant.');
        }

        return $this->groups->join($userId, $campaignId);
    }

    public function finalize(int $campaignId): void
    {
        $this->groups->finalize($campaignId);
    }
}
