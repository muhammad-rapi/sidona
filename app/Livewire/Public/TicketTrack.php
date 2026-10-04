<?php

namespace App\Livewire\Public;

use App\Mail\TicketMail;
use App\Models\Ticket;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.public', ['title' => 'Lacak tiket — SIDONA'])]
class TicketTrack extends Component
{
    public string $code = '';

    public string $email = '';

    public bool $sent = false;

    /**
     * Mengirim ulang tautan pribadi ke email yang terdaftar di tiket.
     * Jawabannya selalu sama, supaya kode atau email orang lain tidak bisa ditebak.
     */
    public function track(): void
    {
        $key = 'ticket-track:'.request()->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $this->addError('code', 'Terlalu banyak percobaan. Coba lagi beberapa menit lagi.');

            return;
        }

        $data = $this->validate([
            'code' => ['required', 'string', 'max:30'],
            'email' => ['required', 'email', 'max:255'],
        ], [
            'code.required' => 'Masukkan kode tiket, contoh TKT-XXXXXXXX.',
            'email.required' => 'Masukkan email yang Anda pakai saat menulis pesan.',
        ]);

        RateLimiter::hit($key, 900);

        $ticket = Ticket::query()
            ->where('code', strtoupper(trim($data['code'])))
            ->where('email', strtolower(trim($data['email'])))
            ->first();

        if ($ticket) {
            Mail::to($ticket->email)->send(new TicketMail($ticket, 'link'));
        }

        $this->sent = true;
    }

    public function render()
    {
        return view('livewire.public.ticket-track');
    }
}
