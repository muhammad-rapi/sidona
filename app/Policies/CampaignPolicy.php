<?php

namespace App\Policies;

use App\Enums\CampaignStatus;
use App\Enums\UserRole;
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
        return in_array($user->role, [UserRole::Bendahara, UserRole::Admin], true);
    }

    public function update(User $user, Campaign $campaign): bool
    {
        return in_array($user->role, [UserRole::Bendahara, UserRole::Admin], true);
    }

    public function review(User $user, Campaign $campaign): bool
    {
        return $user->role === UserRole::Admin && $campaign->status === CampaignStatus::Pending;
    }

    public function delete(User $user, Campaign $campaign): bool
    {
        return $user->role === UserRole::Admin
            && ! $campaign->donations()->exists()
            && ! $campaign->disbursements()->exists();
    }
}
