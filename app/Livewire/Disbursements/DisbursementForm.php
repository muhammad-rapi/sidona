<?php

namespace App\Livewire\Disbursements;

use App\Enums\CampaignStatus;
use App\Enums\DisbursementStatus;
use App\Models\Campaign;
use App\Models\Disbursement;
use App\Services\AuditLogger;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class DisbursementForm extends Component
{
    public Campaign $campaign;

    public int $amount = 0;

    public string $description = '';

    public int $availableBalance = 0;

    public function mount(Campaign $campaign): void
    {
        Gate::authorize('create', Disbursement::class);

        $availableBalance = $campaign->availableBalance();

        abort_unless($campaign->status === CampaignStatus::Active && $availableBalance > 0, 403);

        $this->campaign = $campaign;
        $this->availableBalance = $availableBalance;
    }

    protected function rules(): array
    {
        return [
            'amount' => ['required', 'integer', 'min:1', 'max:'.$this->availableBalance],
            'description' => ['required', 'string', 'min:10', 'max:1000'],
        ];
    }

    protected function messages(): array
    {
        return [
            'amount.min' => 'Jumlah penyaluran minimal Rp 1.',
            'amount.max' => 'Jumlah melebihi saldo program yang tersedia.',
            'description.min' => 'Jelaskan peruntukan dana minimal 10 karakter.',
        ];
    }

    public function submit(AuditLogger $logger): void
    {
        $data = $this->validate();

        DB::transaction(function () use ($data, $logger) {
            $campaign = Campaign::query()->lockForUpdate()->findOrFail($this->campaign->id);

            if ($data['amount'] > $campaign->availableBalance()) {
                throw ValidationException::withMessages([
                    'amount' => 'Jumlah melebihi saldo program yang tersedia.',
                ]);
            }

            $disbursement = Disbursement::create([
                'campaign_id' => $campaign->id,
                'amount' => $data['amount'],
                'description' => $data['description'],
                'status' => DisbursementStatus::Submitted,
                'submitted_by' => auth()->id(),
            ]);

            $logger->log('disbursement.submitted', auth()->user(), $disbursement, [], $disbursement->only([
                'campaign_id', 'amount', 'description', 'status', 'submitted_by',
            ]));
        });

        $this->redirectRoute('disbursements.index', navigate: true);
    }

    public function render()
    {
        return view('livewire.disbursements.disbursement-form');
    }
}
