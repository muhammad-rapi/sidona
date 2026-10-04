@php
    $tone = match ($ticket->status->value) {
        'closed' => 'text-paid',
        'waiting' => 'text-ink',
        default => 'text-paint-dark',
    };
@endphp

<div class="mx-auto max-w-3xl px-5 py-14" wire:poll.visible.4s x-data="{ count: {{ $messages->count() }} }" x-effect="if ($el.dataset.count && Number($el.dataset.count) < {{ $messages->count() }}) { $nextTick(() => $refs.end?.scrollIntoView({ behavior: 'smooth', block: 'nearest' })) }; $el.dataset.count = {{ $messages->count() }}">
    @if (session('status'))
        <div class="flash" role="status">{{ session('status') }}</div>
    @endif

    <h1 class="paint-type text-4xl text-ink sm:text-5xl">{{ $ticket->subject }}</h1>
    <p class="mt-3 text-sm text-ink-soft">
        <span class="font-mono font-bold text-ink">{{ $ticket->code }}</span> &middot; {{ $ticket->category->label() }}
        @if ($ticket->related_code) &middot; <span class="font-mono">{{ $ticket->related_code }}</span>@endif
        &middot; <span class="font-bold {{ $tone }}">{{ $ticket->status->label() }}</span>
    </p>
    <p class="mt-1 text-xs text-ink-faint">Halaman ini pribadi. Jangan bagikan tautannya. Kehilangan tautan? Gunakan <a href="{{ route('support.track') }}" wire:navigate class="underline underline-offset-2">Lacak tiket</a>.</p>

    <ol class="mt-8 space-y-5" aria-label="Percakapan">
        @foreach ($messages as $message)
            <li @class(['max-w-xl border-2 border-ink p-4', 'ml-auto bg-board-wash' => ! $message->is_staff, 'bg-paper' => $message->is_staff])>
                <p class="flex flex-wrap items-baseline justify-between gap-x-4 text-sm">
                    <span class="font-bold">{{ $message->is_auto ? $message->author_name : ($message->is_staff ? $message->author_name.' (tim SIDONA)' : 'Anda') }}</span>
                    <span class="text-ink-soft">{{ $message->created_at->timezone('Asia/Jakarta')->locale('id')->translatedFormat('j M Y, H:i') }}</span>
                </p>
                <p class="mt-2 whitespace-pre-line">{{ $message->body }}</p>
            </li>
        @endforeach
    </ol>

    <div x-ref="end"></div>

    <form wire:submit="reply" class="mt-8" novalidate>
        <label for="t-reply" class="label">{{ $ticket->status->value === 'closed' ? 'Masih ada yang mau ditanyakan? Balasan Anda membuka kembali tiket ini.' : 'Balas' }}</label>
        <textarea id="t-reply" wire:model="body" rows="4" class="field @error('body') field-error @enderror"></textarea>
        @error('body') <p class="error-text" role="alert">{{ $message }}</p> @enderror
        <button type="submit" class="btn btn-ink mt-3" wire:loading.attr="disabled" wire:target="reply">Kirim balasan</button>
    </form>
</div>
