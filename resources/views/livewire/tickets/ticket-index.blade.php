<div wire:poll.visible.10s>
    <div class="page-head">
        <div>
            <h1 class="page-title">Tiket bantuan</h1>
            <p class="page-sub">Pesan dari donatur dan pengaju program. Yang paling lama menunggu ada di atas.</p>
        </div>
    </div>

    <div class="mb-4 flex flex-wrap items-end justify-between gap-4">
        <div class="flex flex-wrap gap-2" role="group" aria-label="Filter status">
            @foreach (['active' => 'Perlu ditangani', 'open' => 'Terbuka', 'waiting' => 'Menunggu pengaju', 'closed' => 'Selesai', 'all' => 'Semua'] as $value => $label)
                <button type="button" wire:click="$set('status', '{{ $value }}')" aria-pressed="{{ $status === $value ? 'true' : 'false' }}" class="btn btn-sm {{ $status === $value ? 'btn-ink' : 'btn-line' }}">{{ $label }}</button>
            @endforeach
        </div>
        <div class="w-full sm:w-72">
            <label for="t-search" class="sr-only">Cari tiket</label>
            <input id="t-search" type="search" wire:model.live.debounce.300ms="search" placeholder="Cari judul, kode, nama, atau email" class="field field-sm">
        </div>
    </div>

    <div class="panel overflow-x-auto">
        <table class="ledger">
            <thead>
                <tr>
                    <th>Tiket</th>
                    <th>Pengirim</th>
                    <th>Status</th>
                    <th>Aktivitas terakhir</th>
                    <th class="sticky-col"><span class="sr-only">Aksi</span></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($tickets as $ticket)
                    @php
                        $badge = match ($ticket->status->value) { 'closed' => 'badge-paid', 'waiting' => 'badge-wait', default => 'badge-fail' };
                    @endphp
                    <tr>
                        <td>
                            <a href="{{ route('tickets.show', $ticket) }}" wire:navigate class="font-bold hover:text-paint-dark">{{ $ticket->subject }}</a>
                            <p class="text-xs text-ink-soft"><span class="font-mono">{{ $ticket->code }}</span> &middot; {{ $ticket->category->label() }}@if ($ticket->related_code) &middot; <span class="font-mono">{{ $ticket->related_code }}</span>@endif</p>
                        </td>
                        <td>
                            <p>{{ $ticket->name }}</p>
                            <p class="text-xs text-ink-soft">{{ $ticket->email }}</p>
                        </td>
                        <td>
                            <span class="badge {{ $badge }}">{{ $ticket->status->label() }}</span>
                            @if ($ticket->needsStaffReply())
                                <p class="mt-1 text-xs font-bold text-paint-dark">Belum dibalas</p>
                            @endif
                            @if ($ticket->assignee)
                                <p class="mt-1 text-xs text-ink-soft">Ditangani {{ $ticket->assignee->name }}</p>
                            @endif
                        </td>
                        <td class="whitespace-nowrap text-sm text-ink-soft">{{ $ticket->last_activity_at?->locale('id')->diffForHumans() ?? '-' }}</td>
                        <td class="sticky-col">
                            <x-action icon="detail" label="Buka tiket" :href="route('tickets.show', $ticket)" wire:navigate />
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="py-12 text-center text-ink-soft">Tidak ada tiket yang cocok.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $tickets->links() }}</div>
</div>
