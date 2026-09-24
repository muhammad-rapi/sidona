<?php

namespace App\Livewire\Campaigns;

use App\Models\Campaign;
use App\Services\AuditLogger;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class CampaignIndex extends Component
{
    use WithPagination;

    public function delete(Campaign $campaign, AuditLogger $logger): void
    {
        Gate::authorize('delete', $campaign);

        $before = $campaign->only(['name', 'description', 'target_amount', 'starts_on', 'ends_on', 'status']);
        $campaign->delete();
        $logger->log('campaign.deleted', auth()->user(), $campaign, $before, []);

        session()->flash('status', 'Program donasi dihapus.');
    }

    public function render()
    {
        return view('livewire.campaigns.campaign-index', [
            'campaigns' => Campaign::query()->latest()->paginate(10),
        ]);
    }
}
