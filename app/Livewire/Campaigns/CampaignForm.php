<?php

namespace App\Livewire\Campaigns;

use App\Enums\CampaignStatus;
use App\Models\Campaign;
use App\Services\AuditLogger;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class CampaignForm extends Component
{
    public ?Campaign $campaign = null;

    public string $name = '';

    public string $description = '';

    public int $target_amount = 0;

    public string $bank_name = '';

    public string $account_number = '';

    public string $account_holder = '';

    public string $starts_on = '';

    public string $ends_on = '';

    public function mount(?Campaign $campaign = null): void
    {
        Gate::authorize($campaign ? 'update' : 'create', $campaign ?? Campaign::class);

        $this->campaign = $campaign;

        if ($campaign) {
            $this->name = $campaign->name;
            $this->description = (string) $campaign->description;
            $this->target_amount = $campaign->target_amount;
            $this->bank_name = (string) $campaign->bank_name;
            $this->account_number = (string) $campaign->account_number;
            $this->account_holder = (string) $campaign->account_holder;
            $this->starts_on = $campaign->starts_on->format('Y-m-d');
            $this->ends_on = $campaign->ends_on->format('Y-m-d');
        }
    }

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'target_amount' => ['required', 'integer', 'min:10000'],
            'bank_name' => ['required', 'string', 'max:255'],
            'account_number' => ['required', 'string', 'max:255'],
            'account_holder' => ['required', 'string', 'max:255'],
            'starts_on' => ['required', 'date'],
            'ends_on' => ['required', 'date', 'after:starts_on'],
        ];
    }

    public function save(AuditLogger $logger): void
    {
        Gate::authorize($this->campaign ? 'update' : 'create', $this->campaign ?? Campaign::class);

        $data = $this->validate();

        if ($this->campaign) {
            $before = collect(array_keys($data))->mapWithKeys(function (string $key) {
                $value = $this->campaign->{$key};

                return [$key => $value instanceof Carbon ? $value->format('Y-m-d') : $value];
            })->all();
            $this->campaign->update($data);
            $logger->log('campaign.updated', auth()->user(), $this->campaign, $before, $data);
        } else {
            $data['status'] = CampaignStatus::Active;
            $campaign = Campaign::create($data);
            $logger->log('campaign.created', auth()->user(), $campaign, [], $data);
        }

        session()->flash('status', 'Program donasi berhasil disimpan.');

        $this->redirectRoute('campaigns.index', navigate: true);
    }

    public function render()
    {
        return view('livewire.campaigns.campaign-form');
    }
}
