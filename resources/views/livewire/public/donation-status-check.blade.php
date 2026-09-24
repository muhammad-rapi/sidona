<div class="max-w-md mx-auto">
    <a href="{{ route('program.index') }}" wire:navigate class="inline-flex items-center gap-1 text-sm text-graphite transition-colors duration-200 hover:text-coral-pulse">
        &larr; Kembali ke Program Donasi
    </a>

    <h1 class="text-xl font-semibold mt-4 mb-6 text-center animate-fade-in-up">Cek Status Donasi</h1>

    <form wire:submit="check" class="space-y-4 bg-white p-6 rounded-2xl border border-frost-gray animate-fade-in-up" style="animation-delay: 60ms">
        <div>
            <label class="block text-sm font-medium mb-1">Kode Referensi</label>
            <div class="relative">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-graphite">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                </span>
                <input type="text" wire:model="reference_code" placeholder="DON-XXXXXXXX" class="w-full rounded border border-frost-gray pl-9 pr-3 py-2 font-mono transition-all duration-200 outline-none focus:border-coral-pulse focus:ring-2 focus:ring-coral-pulse/20">
            </div>
            @error('reference_code') <p class="text-sm text-red-700 mt-1">{{ $message }}</p> @enderror
        </div>

        <button type="submit" class="w-full rounded-full bg-coral-pulse px-6 py-3 text-white text-sm font-medium transition-all duration-200 ease-out hover:scale-[1.03] hover:bg-coral-pulse-dark active:scale-95">Cek Status</button>
    </form>

    @if ($searched)
        <div class="mt-6 bg-white p-6 rounded-2xl border border-frost-gray animate-fade-in-up">
            @if ($result)
                @php
                    $badge = match ($result->status) {
                        \App\Enums\DonationStatus::Verified => ['bg-mint-wash', 'text-canopy-green'],
                        \App\Enums\DonationStatus::Rejected => ['bg-flag-red/10', 'text-flag-red'],
                        default => ['bg-sand', 'text-graphite'],
                    };
                @endphp
                <div class="flex items-center justify-between">
                    <span class="font-mono text-sm text-graphite">{{ $result->reference_code }}</span>
                    <span class="inline-flex items-center rounded-full {{ $badge[0] }} {{ $badge[1] }} px-3 py-1 text-xs font-medium">{{ $result->status->label() }}</span>
                </div>
                <dl class="mt-4 space-y-2 text-sm">
                    <div class="flex justify-between">
                        <dt class="text-graphite">Program</dt>
                        <dd>{{ $result->campaign->name }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-graphite">Nominal</dt>
                        <dd class="font-medium">Rp {{ number_format($result->amount, 0, ',', '.') }}</dd>
                    </div>
                    @if ($result->rejection_reason)
                        <div class="flex justify-between gap-4">
                            <dt class="text-graphite shrink-0">Alasan</dt>
                            <dd class="text-right">{{ $result->rejection_reason }}</dd>
                        </div>
                    @endif
                </dl>
            @else
                <div class="text-center py-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 mx-auto text-graphite" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><path d="M12 17h.01"/></svg>
                    <p class="text-sm text-graphite mt-2">Kode referensi tidak ditemukan.</p>
                </div>
            @endif
        </div>
    @endif
</div>
