<div>
    <div class="page-head">
        <div>
            <a href="{{ route('tickets.index') }}" wire:navigate class="mb-2 inline-flex items-center gap-1.5 text-sm font-semibold text-ink-soft hover:text-ink"><x-icon name="send" class="h-4 w-4 rotate-180" />Semua tiket</a>
            <h1 class="page-title">{{ $ticket->subject }}</h1>
            <p class="page-sub"><span class="font-mono font-bold text-ink">{{ $ticket->code }}</span> &middot; {{ $ticket->category->label() }} &middot; {{ $ticket->status->label() }}</p>
        </div>
        @if ($canHandle)
            <div class="flex flex-wrap gap-2">
                <button type="button" wire:click="assignToMe" class="btn btn-line btn-sm">{{ $ticket->assigned_to === auth()->id() ? 'Lepas tangani' : 'Saya tangani' }}</button>
                @if ($ticket->status->value !== 'closed')
                    <button type="button" wire:click="setStatus('closed')" class="btn btn-ink btn-sm">Tandai selesai</button>
                @else
                    <button type="button" wire:click="setStatus('open')" class="btn btn-line btn-sm">Buka lagi</button>
                @endif
            </div>
        @endif
    </div>

    <div class="grid gap-x-12 gap-y-8 lg:grid-cols-[1fr_18rem]">
        <div>
            <ol class="space-y-4" aria-label="Percakapan">
                @foreach ($messages as $message)
                    <li @class(['max-w-2xl border p-4', 'ml-auto border-ink bg-paper' => $message->is_staff, 'border-rule bg-board-wash/50' => ! $message->is_staff])>
                        <p class="flex flex-wrap items-baseline justify-between gap-x-4 text-sm">
                            <span class="font-bold">{{ $message->author_name }}@if ($message->is_staff) <span class="font-normal text-ink-soft">(staf)</span>@endif</span>
                            <span class="text-ink-soft">{{ $message->created_at->timezone('Asia/Jakarta')->locale('id')->translatedFormat('j M Y, H:i') }}</span>
                        </p>
                        <p class="mt-2 whitespace-pre-line">{{ $message->body }}</p>
                    </li>
                @endforeach
            </ol>

            @if ($canHandle)
                <form wire:submit="reply" class="mt-6 space-y-3" novalidate>
                    <label for="s-reply" class="label">Balas ke {{ $ticket->email }}</label>
                    <textarea id="s-reply" wire:model="body" rows="5" class="field @error('body') field-error @enderror"></textarea>
                    @error('body') <p class="error-text" role="alert">{{ $message }}</p> @enderror
                    <div class="flex flex-wrap items-center gap-3">
                        <label for="s-status" class="text-sm font-semibold">Setelah dibalas, status:</label>
                        <select id="s-status" wire:model="newStatus" class="field field-sm w-auto">
                            <option value="waiting">Menunggu pengaju</option>
                            <option value="closed">Selesai</option>
                            <option value="open">Tetap terbuka</option>
                        </select>
                        <button type="submit" class="btn btn-paint" wire:loading.attr="disabled" wire:target="reply"><x-icon name="send" />Kirim balasan</button>
                    </div>
                </form>
            @else
                <p class="mt-6 text-sm text-ink-soft">Peran Anda hanya bisa melihat tiket. Balasan dikirim oleh bendahara, admin, atau super admin.</p>
            @endif
        </div>

        <aside class="text-sm">
            <dl class="space-y-2">
                <div><dt class="font-bold text-ink-soft">Pengirim</dt><dd>{{ $ticket->name }}<br><span class="text-ink-soft">{{ $ticket->email }}</span></dd></div>
                @if ($ticket->related_code)
                    <div><dt class="font-bold text-ink-soft">Kode terkait</dt><dd class="font-mono">{{ $ticket->related_code }}</dd></div>
                @endif
                <div><dt class="font-bold text-ink-soft">Ditangani</dt><dd>{{ $ticket->assignee?->name ?? 'Belum ada' }}</dd></div>
                <div><dt class="font-bold text-ink-soft">Dibuat</dt><dd>{{ $ticket->created_at->timezone('Asia/Jakarta')->format('d/m/Y H:i') }} WIB</dd></div>
            </dl>

            <p class="mb-2 mt-6 font-bold text-ink-soft">Jejak audit</p>
            @forelse ($trail as $entry)
                <p class="border-b border-rule py-1.5"><span class="font-semibold">{{ \App\Support\ActivityLabels::label($entry->action) }}</span><br><span class="text-xs text-ink-soft">{{ $entry->user?->name ?? 'Pengirim' }}, {{ $entry->created_at->timezone('Asia/Jakarta')->format('d/m H:i') }}</span></p>
            @empty
                <p class="text-ink-soft">Belum ada.</p>
            @endforelse
        </aside>
    </div>
</div>
