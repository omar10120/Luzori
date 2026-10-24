<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\Center;
use App\Models\CenterUser;

class SupportAccessService
{
    public function centerChatEnabled(Center $center): bool
    {
        return (bool) ($center->support_chat_enabled ?? true);
    }

    public function centerTicketsEnabled(Center $center): bool
    {
        return (bool) ($center->support_tickets_enabled ?? true);
    }

    public function branchChatEnabled(?Branch $branch): bool
    {
        if (!$branch) {
            return true;
        }

        return (bool) ($branch->support_chat_enabled ?? true);
    }

    public function branchTicketsEnabled(?Branch $branch): bool
    {
        if (!$branch) {
            return true;
        }

        return (bool) ($branch->support_tickets_enabled ?? true);
    }

    /**
     * Center staff can use chat if center is enabled AND (no branch OR branch enabled).
     */
    public function canCenterUserUseChat(Center $center, ?CenterUser $user): bool
    {
        if (!$this->centerChatEnabled($center)) {
            return false;
        }

        if (!$user || !$user->branch_id) {
            return true;
        }

        $branch = $user->relationLoaded('branch') ? $user->branch : Branch::find($user->branch_id);

        return $this->branchChatEnabled($branch);
    }

    public function canCenterUserUseTickets(Center $center, ?CenterUser $user): bool
    {
        if (!$this->centerTicketsEnabled($center)) {
            return false;
        }

        if (!$user || !$user->branch_id) {
            return true;
        }

        $branch = $user->relationLoaded('branch') ? $user->branch : Branch::find($user->branch_id);

        return $this->branchTicketsEnabled($branch);
    }
}
