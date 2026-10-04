<?php

namespace App\Livewire\Public;

use App\Enums\TicketCategory;
use App\Enums\TicketStatus;
use App\Mail\TicketMail;
use App\Models\Ticket;
use App\Services\AuditLogger;
use App\Services\TicketAutoReply;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.public', ['title' => 'Hubungi kami — SIDONA'])]
class TicketCreate extends Component
{
    public string $name = '';

    public string $email = '';

    public string $category = 'donation';

    public string $related_code = '';

    public string $subject = '';

    public string $message = '';

    /** Honeypot: manusia tidak mengisinya. */
    public string $website = '';

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:100'],
            'email' => ['required', 'email', 'max:255'],
            'category' => ['required', Rule::enum(TicketCategory::class)],
            'related_code' => ['nullable', 'string', 'max:30', 'regex:/^(DON|PRG)-[A-Z0-9]{8}$/i'],
            'subject' => ['required', 'string', 'min:5', 'max:120'],
            'message' => ['required', 'string', 'min:20', 'max:3000'],
        ];
    }

    protected function messages(): array
    {
        return [
            'related_code.regex' => 'Kode harus berbentuk DON-XXXXXXXX atau PRG-XXXXXXXX. Kosongkan kalau tidak ada.',
            'subject.min' => 'Judul minimal 5 karakter.',
            'message.min' => 'Ceritakan masalahnya minimal 20 karakter supaya kami bisa membantu.',
        ];
    }

    public function submit(AuditLogger $logger)
    {
        if ($this->website !== '') {
            return null;
        }

        $key = 'ticket-create:'.request()->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $minutes = (int) ceil(RateLimiter::availableIn($key) / 60);
            $this->addError('subject', "Terlalu banyak pesan dari perangkat ini. Coba lagi dalam {$minutes} menit.");

            return null;
        }

        $data = $this->validate();
        RateLimiter::hit($key, 3600);

        $ticket = Ticket::create([
            'code' => Ticket::generateCode(),
            'token' => Str::random(40),
            'name' => trim($data['name']),
            'email' => strtolower(trim($data['email'])),
            'category' => $data['category'],
            'related_code' => filled($data['related_code']) ? strtoupper(trim($data['related_code'])) : null,
            'subject' => trim($data['subject']),
            'status' => TicketStatus::Open,
            'last_activity_at' => now(),
        ]);

        $ticket->messages()->create(['author_name' => $ticket->name, 'is_staff' => false, 'body' => trim($data['message'])]);

        $logger->log('ticket.created', null, $ticket, [], $ticket->only(['code', 'category', 'subject']));
        app(TicketAutoReply::class)->post($ticket);
        Mail::to($ticket->email)->send(new TicketMail($ticket, 'received'));

        return $this->redirectRoute('support.thread', $ticket->token, navigate: true);
    }

    public function render()
    {
        return view('livewire.public.ticket-create', ['categories' => TicketCategory::cases()]);
    }
}
