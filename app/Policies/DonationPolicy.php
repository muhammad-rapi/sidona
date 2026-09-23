<?php

namespace App\Policies;

use App\Enums\DonationStatus;
use App\Enums\UserRole;
use App\Models\Donation;
use App\Models\User;

class DonationPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function verify(User $user, Donation $donation): bool
    {
        return $user->role === UserRole::Bendahara && $donation->status === DonationStatus::Pending;
    }

    public function reject(User $user, Donation $donation): bool
    {
        return $user->role === UserRole::Bendahara && $donation->status === DonationStatus::Pending;
    }
}
