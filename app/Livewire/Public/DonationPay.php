<?php

namespace App\Livewire\Public;

use App\Models\Donation;
use App\Services\DonationPayment;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.public')]
class DonationPay extends Component
{
    public Donation $donation;

    public function mount(string $reference): void
    {
        $donation = Donation::query()
            ->with('campaign')
            ->where('reference_code', strtoupper($reference))
            ->firstOrFail();

        if ($donation->isPaid()) {
            $this->redirectRoute('donations.receipt', $donation->reference_code, navigate: true);
        }

        $this->donation = $donation;
    }

    /**
     * Simulator: menggantikan notifikasi pembayaran dari bank/e-wallet.
     */
    public function simulatePayment(DonationPayment $payment)
    {
        $payment->confirm($this->donation);

        return $this->redirectRoute('donations.receipt', $this->donation->reference_code, navigate: true);
    }

    /**
     * Dipanggil berkala: kalau pembayaran sudah terkonfirmasi (misalnya dari notifikasi gateway),
     * donatur langsung dibawa ke kuitansi tanpa perlu menekan apa pun.
     */
    public function checkPaid()
    {
        $this->donation->refresh();

        if ($this->donation->isPaid()) {
            return $this->redirectRoute('donations.receipt', $this->donation->reference_code, navigate: true);
        }

        return null;
    }

    public function render()
    {
        return view('livewire.public.donation-pay');
    }
}
