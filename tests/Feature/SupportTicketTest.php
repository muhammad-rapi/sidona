<?php

use App\Enums\TicketStatus;
use App\Enums\UserRole;
use App\Livewire\Public\TicketCreate;
use App\Livewire\Public\TicketThread;
use App\Livewire\Tickets\TicketIndex;
use App\Livewire\Tickets\TicketShow;
use App\Mail\TicketMail;
use App\Models\ActivityLog;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Livewire;

beforeEach(fn () => RateLimiter::clear('ticket-create:127.0.0.1'));

function fillTicket($component)
{
    return $component
        ->set('name', 'Budi Santoso')
        ->set('email', 'budi@example.com')
        ->set('category', 'payment')
        ->set('related_code', 'DON-ABCD1234')
        ->set('subject', 'Pembayaran saya belum tercatat')
        ->set('message', 'Saya sudah bayar lewat QRIS tapi donasi masih menunggu pembayaran.');
}

it('lets a guest open a ticket, emails a private link and logs it', function () {
    Mail::fake();

    fillTicket(Livewire::test(TicketCreate::class))->call('submit')->assertHasNoErrors();

    $ticket = Ticket::first();
    expect($ticket->code)->toStartWith('TKT-');
    expect($ticket->status)->toBe(TicketStatus::Open);
    expect($ticket->messages)->toHaveCount(1);
    expect(ActivityLog::where('action', 'ticket.created')->whereNull('user_id')->count())->toBe(1);
    Mail::assertSent(TicketMail::class, fn ($m) => $m->hasTo('budi@example.com') && $m->kind === 'received');

    $this->get(route('support.thread', $ticket->token))->assertOk()->assertSee('Pembayaran saya belum tercatat');
    $this->get(route('support.thread', 'token-salah'))->assertNotFound();
});

it('validates the ticket form, drops honeypot posts and rate limits', function () {
    foreach ([['name', ''], ['email', 'x'], ['subject', 'abc'], ['message', 'terlalu pendek'], ['related_code', 'XYZ-1']] as [$field, $value]) {
        fillTicket(Livewire::test(TicketCreate::class))->set($field, $value)->call('submit')->assertHasErrors($field);
    }
    expect(Ticket::count())->toBe(0);

    fillTicket(Livewire::test(TicketCreate::class))->set('website', 'spam')->call('submit');
    expect(Ticket::count())->toBe(0);

    foreach (range(1, 5) as $i) {
        fillTicket(Livewire::test(TicketCreate::class))->call('submit');
    }
    fillTicket(Livewire::test(TicketCreate::class))->call('submit')->assertHasErrors('subject');
    expect(Ticket::count())->toBe(5);
});

it('lets staff who handle tickets reply, email the requester and change status', function () {
    Mail::fake();
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    fillTicket(Livewire::test(TicketCreate::class))->call('submit');
    $ticket = Ticket::first();

    Livewire::actingAs($admin)->test(TicketShow::class, ['ticket' => $ticket])
        ->set('body', 'Terima kasih, sudah kami cek dan donasi Anda sudah masuk.')
        ->set('newStatus', 'closed')
        ->call('reply')
        ->assertHasNoErrors();

    $ticket->refresh();
    expect($ticket->status)->toBe(TicketStatus::Closed);
    expect($ticket->assigned_to)->toBe($admin->id);
    expect($ticket->messages)->toHaveCount(2);
    expect($ticket->messages->last()->is_staff)->toBeTrue();
    Mail::assertSent(TicketMail::class, fn ($m) => $m->kind === 'reply' && $m->hasTo('budi@example.com'));
    expect(ActivityLog::where('action', 'ticket.replied')->count())->toBe(1);
    expect(ActivityLog::where('action', 'ticket.status_changed')->count())->toBe(1);
});

it('lets the requester reply and reopens a closed ticket', function () {
    Mail::fake();
    fillTicket(Livewire::test(TicketCreate::class))->call('submit');
    $ticket = Ticket::first();
    $ticket->update(['status' => TicketStatus::Closed]);

    Livewire::test(TicketThread::class, ['token' => $ticket->token])
        ->set('body', '')->call('reply')->assertHasErrors('body')
        ->set('body', 'Masih belum masuk, mohon dicek lagi.')->call('reply')->assertHasNoErrors();

    expect($ticket->fresh()->status)->toBe(TicketStatus::Open);
    expect($ticket->fresh()->messages)->toHaveCount(2);
});

it('lets auditors read tickets but not reply or change them', function () {
    Mail::fake();
    fillTicket(Livewire::test(TicketCreate::class))->call('submit');
    $ticket = Ticket::first();
    $auditor = User::factory()->create(['role' => UserRole::Auditor]);

    $this->actingAs($auditor)->get(route('tickets.show', $ticket))->assertOk()->assertSee('hanya bisa melihat tiket');

    Livewire::actingAs($auditor)->test(TicketShow::class, ['ticket' => $ticket])
        ->set('body', 'Coba balas')->call('reply')->assertForbidden();
    Livewire::actingAs($auditor)->test(TicketShow::class, ['ticket' => $ticket])->call('setStatus', 'closed')->assertForbidden();
});

it('keeps the staff inbox behind login and filters by status', function () {
    $this->get(route('tickets.index'))->assertRedirect(route('login'));

    $admin = User::factory()->create(['role' => UserRole::Admin]);
    Ticket::create(['code' => 'TKT-AAAA1111', 'token' => 'a', 'name' => 'A', 'email' => 'a@x.com', 'category' => 'other', 'subject' => 'Masih terbuka', 'status' => 'open', 'last_activity_at' => now()]);
    Ticket::create(['code' => 'TKT-BBBB2222', 'token' => 'b', 'name' => 'B', 'email' => 'b@x.com', 'category' => 'other', 'subject' => 'Sudah selesai', 'status' => 'closed', 'last_activity_at' => now()]);

    Livewire::actingAs($admin)->test(TicketIndex::class)
        ->assertSee('Masih terbuka')->assertDontSee('Sudah selesai')
        ->set('status', 'closed')->assertSee('Sudah selesai')->assertDontSee('Masih terbuka')
        ->set('status', 'all')->set('search', 'BBBB')->assertSee('Sudah selesai')->assertDontSee('Masih terbuka');

    $this->actingAs($admin)->get(route('dashboard'))->assertSee('Tiket bantuan');
});
