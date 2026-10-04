@props(['donation', 'trail', 'colspan'])

<tr>
    <td colspan="{{ $colspan }}" class="!bg-desk !p-0">
        <div class="grid gap-x-10 gap-y-6 px-5 py-5 md:grid-cols-2">
            <dl class="space-y-2 text-sm">
                <div class="flex gap-4"><dt class="w-32 shrink-0 font-bold text-ink-soft">Donatur</dt><dd>{{ $donation->donor_name }}@if ($donation->is_anonymous) (tampil anonim di publik)@endif</dd></div>
                <div class="flex gap-4"><dt class="w-32 shrink-0 font-bold text-ink-soft">Kontak</dt><dd class="break-all">{{ $donation->donor_contact }}</dd></div>
                <div class="flex gap-4"><dt class="w-32 shrink-0 font-bold text-ink-soft">Metode bayar</dt><dd>{{ $donation->payment_method?->label() ?? '-' }}</dd></div>
                <div class="flex gap-4"><dt class="w-32 shrink-0 font-bold text-ink-soft">Dibuat</dt><dd>{{ $donation->created_at->timezone('Asia/Jakarta')->format('d/m/Y H:i') }} WIB</dd></div>
                <div class="flex gap-4"><dt class="w-32 shrink-0 font-bold text-ink-soft">Dibayar</dt><dd>{{ $donation->paid_at ? $donation->paid_at->timezone('Asia/Jakarta')->format('d/m/Y H:i').' WIB' : '-' }}</dd></div>
                <div class="pt-2"><a href="{{ route('donations.receipt', $donation->reference_code) }}" target="_blank" class="text-sm font-bold uppercase tracking-wider underline underline-offset-4 hover:text-paint-dark">Buka kuitansi</a></div>
            </dl>

            <div>
                <p class="mb-2 text-sm font-bold text-ink-soft">Jejak audit donasi ini</p>
                @forelse ($trail as $entry)
                    <div class="flex items-baseline justify-between gap-4 border-b border-rule py-1.5 text-sm">
                        <span><span class="font-mono text-xs">{{ $entry->action }}</span> oleh {{ $entry->user?->name ?? 'Sistem' }}</span>
                        <span class="shrink-0 text-ink-soft">{{ $entry->created_at->timezone('Asia/Jakarta')->format('d/m/Y H:i') }}</span>
                    </div>
                @empty
                    <p class="text-sm text-ink-soft">Belum ada catatan audit.</p>
                @endforelse
            </div>
        </div>
    </td>
</tr>
