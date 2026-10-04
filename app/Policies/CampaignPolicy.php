<?php

namespace App\Policies;

use App\Enums\CampaignStatus;
use App\Models\Campaign;
use App\Models\User;

class CampaignPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->canManageCampaigns();
    }

    public function update(User $user, Campaign $campaign): bool
    {
        return $user->canManageCampaigns();
    }

    public function review(User $user, Campaign $campaign): bool
    {
        return $this->reject($user, $campaign) && ! $campaign->proposerNeedsVerification();
    }

    public function reject(User $user, Campaign $campaign): bool
    {
        return $user->hasAdminPowers() && $campaign->status === CampaignStatus::Pending;
    }

    public function delete(User $user, Campaign $campaign): bool
    {
        return $user->hasAdminPowers()
            && ! $campaign->donations()->exists()
            && ! $campaign->disbursements()->exists();
    }
}
