<?php

namespace App\Livewire\Public;

use App\Enums\CampaignStatus;
use App\Models\Campaign;
use App\Services\AuditLogger;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('components.layouts.public')]
class CampaignSubmit extends Component
{
    use WithFileUploads;

    public string $name = '';

    public string $description = '';

    public int $target_amount = 0;

    public int $duration_days = 30;

    public string $proposer_name = '';

    public string $proposer_contact = '';

    public string $bank_name = '';

    public string $account_number = '';

    public string $account_holder = '';

    public $cover_image_upload;

    /** Honeypot: manusia tidak mengisinya. */
    public string $website = '';

    public bool $submitted = false;

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:5', 'max:120'],
            'description' => ['required', 'string', 'min:50', 'max:5000'],
            'target_amount' => ['required', 'integer', 'min:100000', 'max:100000000000'],
            'duration_days' => ['required', 'integer', 'in:14,30,60,90'],
            'proposer_name' => ['required', 'string', 'min:2', 'max:100', "regex:/^[\\p{L}\\p{M}][\\p{L}\\p{M}\\s.'\\-]*$/u"],
            'proposer_contact' => ['required', 'string', 'max:255', function (string $attribute, mixed $value, \Closure $fail) {
                $value = trim($value);
                $isEmail = filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
                $isPhone = preg_match('/^(\\+62|62|0)8[0-9]{8,12}$/', preg_replace('/[\\s-]/', '', $value)) === 1;

                if (! $isEmail && ! $isPhone) {
                    $fail('Isi email yang valid atau nomor WhatsApp, contoh 08123456789.');
                }
            }],
            'bank_name' => ['required', 'string', 'max:100'],
            'account_number' => ['required', 'string', 'regex:/^[0-9][0-9\\s-]{4,29}$/'],
            'account_holder' => ['required', 'string', 'max:255'],
            'cover_image_upload' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
        ];
    }

    protected function messages(): array
    {
        return [
            'name.min' => 'Judul program minimal 5 karakter.',
            'description.min' => 'Ceritakan program minimal 50 karakter agar tim bisa menilainya.',
            'target_amount.min' => 'Target dana minimal Rp 100.000.',
            'proposer_name.regex' => 'Nama hanya boleh berisi huruf, spasi, titik, atau tanda hubung.',
            'account_number.regex' => 'Nomor rekening hanya boleh berisi angka (5 sampai 30 digit).',
        ];
    }

    public function updated(string $property): void
    {
        if (in_array($property, ['proposer_name', 'proposer_contact', 'account_number'], true)) {
            $this->validateOnly($property);
        }
    }

    public function submit(AuditLogger $logger): void
    {
        $key = 'campaign-submit:'.request()->ip();

        if ($this->website !== '') {
            $this->submitted = true;

            return;
        }

        if (RateLimiter::tooManyAttempts($key, 3)) {
            $minutes = (int) ceil(RateLimiter::availableIn($key) / 60);
            $this->addError('name', "Terlalu banyak pengajuan dari perangkat ini. Coba lagi dalam {$minutes} menit.");

            return;
        }

        $data = $this->validate();
        RateLimiter::hit($key, 3600);

        $campaign = Campaign::create([
            'name' => trim($data['name']),
            'description' => trim($data['description']),
            'target_amount' => $data['target_amount'],
            'bank_name' => trim($data['bank_name']),
            'account_number' => trim($data['account_number']),
            'account_holder' => trim($data['account_holder']),
            'proposer_name' => trim($data['proposer_name']),
            'proposer_contact' => trim($data['proposer_contact']),
            'starts_on' => today(),
            'ends_on' => today()->addDays($data['duration_days']),
            'cover_image' => $this->cover_image_upload?->store('campaign-covers', 'public'),
            'status' => CampaignStatus::Pending,
        ]);

        $logger->log('campaign.proposed', null, $campaign, [], $campaign->only([
            'name', 'target_amount', 'proposer_name', 'proposer_contact', 'status',
        ]));

        $this->submitted = true;
    }

    public function render()
    {
        return view('livewire.public.campaign-submit');
    }
}
