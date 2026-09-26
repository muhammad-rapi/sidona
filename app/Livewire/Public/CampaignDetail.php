<?php

namespace App\Livewire\Public;

use App\Enums\CampaignStatus;
use App\Enums\DonationStatus;
use App\Models\Campaign;
use App\Models\Donation;
use App\Services\AuditLogger;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('components.layouts.public')]
class CampaignDetail extends Component
{
    use WithFileUploads;

    public Campaign $campaign;

    public string $donor_name = '';

    public string $donor_contact = '';

    public int $amount = 0;

    public string $transferred_at = '';

    public $proof;

    public ?string $referenceCode = null;

    public function mount(Campaign $campaign): void
    {
        abort_unless(
            $campaign->status === CampaignStatus::Active
                && ! $campaign->ends_on->isPast()
                && ! $campaign->starts_on->isFuture(),
            403
        );

        $this->campaign = $campaign;
    }

    protected function rules(): array
    {
        return [
            'donor_name' => ['required', 'string', 'max:255'],
            'donor_contact' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'integer', 'min:10000'],
            'transferred_at' => ['required', 'date_format:Y-m-d\TH:i'],
            'proof' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
        ];
    }

    public function submit(AuditLogger $logger): void
    {
        $data = $this->validate();

        $transferredAt = Carbon::createFromFormat('Y-m-d\TH:i', $data['transferred_at'], 'Asia/Jakarta')->utc();

        if ($transferredAt->isFuture()) {
            $this->addError('transferred_at', 'Waktu transfer tidak boleh di masa depan.');

            return;
        }

        $path = $this->proof->store('donation-proofs', 'public');

        $donation = Donation::create([
            'campaign_id' => $this->campaign->id,
            'donor_name' => $data['donor_name'],
            'donor_contact' => $data['donor_contact'],
            'amount' => $data['amount'],
            'transferred_at' => $transferredAt,
            'proof_path' => $path,
            'status' => DonationStatus::Pending,
        ]);

        $logger->log('donation.created', null, $donation, [], $donation->only([
            'campaign_id', 'donor_name', 'donor_contact', 'amount', 'transferred_at', 'status',
        ]));

        $this->referenceCode = $donation->reference_code;
        $this->reset(['donor_name', 'donor_contact', 'amount', 'transferred_at', 'proof']);
    }

    public function render()
    {
        return view('livewire.public.campaign-detail');
    }
}
