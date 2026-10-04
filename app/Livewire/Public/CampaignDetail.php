<?php

namespace App\Livewire\Public;

use App\Enums\CampaignStatus;
use App\Enums\DonationStatus;
use App\Enums\PaymentMethod;
use App\Models\Campaign;
use App\Models\Donation;
use App\Services\AuditLogger;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.public')]
class CampaignDetail extends Component
{
    public Campaign $campaign;

    public string $donor_name = '';

    public string $donor_contact = '';

    public bool $is_anonymous = false;

    public int $amount = 0;

    public string $payment_method = 'qris';

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
            'donor_name' => ['required', 'string', 'min:2', 'max:100', "regex:/^[\\p{L}\\p{M}][\\p{L}\\p{M}\\s.'\\-]*$/u"],
            'donor_contact' => ['required', 'string', 'max:255', function (string $attribute, mixed $value, \Closure $fail) {
                $value = trim($value);
                $isEmail = filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
                $isPhone = preg_match('/^(\\+62|62|0)8[0-9]{8,12}$/', preg_replace('/[\\s-]/', '', $value)) === 1;

                if (! $isEmail && ! $isPhone) {
                    $fail('Isi email yang valid atau nomor WhatsApp, contoh 08123456789.');
                }
            }],
            'is_anonymous' => ['boolean'],
            'amount' => ['required', 'integer', 'min:10000', 'max:1000000000'],
            'payment_method' => ['required', Rule::enum(PaymentMethod::class)],
        ];
    }

    protected function messages(): array
    {
        return [
            'amount.min' => 'Donasi minimal Rp 10.000.',
            'amount.max' => 'Donasi maksimal Rp 1.000.000.000 per transaksi.',
            'donor_name.min' => 'Nama minimal 2 huruf.',
            'donor_name.regex' => 'Nama hanya boleh berisi huruf, spasi, titik, atau tanda hubung.',
            'payment_method.required' => 'Pilih metode pembayaran.',
            'amount.required' => 'Pilih atau isi nominal donasi.',
            'donor_name.required' => 'Nama wajib diisi (boleh disamarkan di bawah).',
            'donor_contact.required' => 'Isi email atau nomor WhatsApp untuk kuitansi.',
        ];
    }

    public function updated(string $property): void
    {
        if (in_array($property, ['donor_name', 'donor_contact'], true)) {
            $this->validateOnly($property);
        }
    }

    public function submit(AuditLogger $logger)
    {
        abort_unless(
            $this->campaign->status === CampaignStatus::Active && ! $this->campaign->ends_on->isPast(),
            403
        );

        $data = $this->validate();
        $data['donor_name'] = trim($data['donor_name']);
        $data['donor_contact'] = trim($data['donor_contact']);

        $donation = Donation::create([
            'campaign_id' => $this->campaign->id,
            'donor_name' => $data['donor_name'],
            'donor_contact' => $data['donor_contact'],
            'is_anonymous' => $data['is_anonymous'],
            'amount' => $data['amount'],
            'payment_method' => $data['payment_method'],
            'status' => DonationStatus::Pending,
        ]);

        $logger->log('donation.created', null, $donation, [], $donation->only([
            'campaign_id', 'donor_name', 'donor_contact', 'amount', 'payment_method', 'status',
        ]));

        return $this->redirectRoute('donations.pay', $donation->reference_code, navigate: true);
    }

    public function render()
    {
        $recent = Donation::query()
            ->where('campaign_id', $this->campaign->id)
            ->where('status', DonationStatus::Verified)
            ->latest('paid_at')
            ->latest('id')
            ->limit(6)
            ->get();

        return view('livewire.public.campaign-detail', [
            'recentDonations' => $recent,
            'raised' => $this->campaign->verifiedDonationsTotal(),
            'donorCount' => $this->campaign->donations()->where('status', DonationStatus::Verified)->count(),
        ]);
    }
}
