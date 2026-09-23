<?php

namespace App\Policies;

use App\Enums\DisbursementStatus;
use App\Enums\UserRole;
use App\Models\Disbursement;
use App\Models\User;

class DisbursementPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->role === UserRole::Bendahara;
    }

    public function approve(User $user, Disbursement $disbursement): bool
    {
        return $user->role === UserRole::Admin
            && $disbursement->status === DisbursementStatus::Submitted
            && $disbursement->submitted_by !== $user->id;
    }

    public function reject(User $user, Disbursement $disbursement): bool
    {
        return $this->approve($user, $disbursement);
    }
}
