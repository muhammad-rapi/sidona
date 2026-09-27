<div x-data="{ confirmId: null, confirmName: '' }">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-xl font-semibold">Program Donasi</h1>
        @can('create', App\Models\Campaign::class)
            <a href="{{ route('campaigns.create') }}" wire:navigate class="rounded-full bg-coral-pulse px-6 py-3 text-white text-sm font-medium transition-all duration-200 ease-out hover:scale-[1.03] hover:bg-coral-pulse-dark active:scale-95">Tambah Program</a>
        @endcan
    </div>

    <div class="bg-white rounded-3xl border border-frost-gray overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr>
                        <th class="border-b-2 border-ink-black px-4 py-3 text-left font-medium text-graphite">Nama</th>
                        <th class="border-b-2 border-ink-black px-4 py-3 text-left font-medium text-graphite">Target</th>
                        <th class="border-b-2 border-ink-black px-4 py-3 text-left font-medium text-graphite">Rekening Tujuan</th>
                        <th class="border-b-2 border-ink-black px-4 py-3 text-left font-medium text-graphite">Periode</th>
                        <th class="border-b-2 border-ink-black px-4 py-3 text-left font-medium text-graphite">Status</th>
                        <th class="border-b-2 border-ink-black px-4 py-3 text-left font-medium text-graphite">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($campaigns as $campaign)
                        @php
                            $statusBadge = match ($campaign->status->value) {
                                'active' => ['bg-mint-wash', 'text-canopy-green'],
                                'completed' => ['bg-sky-wash', 'text-canopy-green'],
                                default => ['bg-sand', 'text-graphite'],
                            };
                        @endphp
                        <tr class="hover:bg-mint-wash/40 transition-colors duration-150">
                            <td class="border-b border-frost-gray px-4 py-3 font-medium">{{ $campaign->name }}</td>
                            <td class="border-b border-frost-gray px-4 py-3 font-mono whitespace-nowrap">Rp {{ number_format($campaign->target_amount, 0, ',', '.') }}</td>
                            <td class="border-b border-frost-gray px-4 py-3 whitespace-nowrap">
                                @if ($campaign->bank_name && $campaign->account_number)
                                    <span class="text-graphite">{{ $campaign->bank_name }}</span>
                                    <span class="font-mono">{{ $campaign->account_number }}</span>
                                @else
                                    <span class="text-flag-red text-xs">Belum diisi</span>
                                @endif
                            </td>
                            <td class="border-b border-frost-gray px-4 py-3 whitespace-nowrap text-graphite">{{ $campaign->starts_on->format('d/m/Y') }} &ndash; {{ $campaign->ends_on->format('d/m/Y') }}</td>
                            <td class="border-b border-frost-gray px-4 py-3 whitespace-nowrap">
                                <span class="inline-flex items-center rounded-full {{ $statusBadge[0] }} {{ $statusBadge[1] }} px-3 py-1 text-xs font-medium">{{ $campaign->status->label() }}</span>
                            </td>
                            <td class="border-b border-frost-gray px-4 py-3 whitespace-nowrap space-x-2">
                                @can('update', $campaign)
                                    <a href="{{ route('campaigns.edit', $campaign) }}" wire:navigate class="inline-flex items-center rounded-full border border-frost-gray px-3 py-1 text-xs font-medium text-graphite transition-colors duration-200 hover:border-coral-pulse hover:text-coral-pulse">Ubah</a>
                                @endcan
                                @can('create', App\Models\Disbursement::class)
                                    <a href="{{ route('disbursements.create', $campaign) }}" wire:navigate class="inline-flex items-center rounded-full bg-mint-wash px-3 py-1 text-xs font-medium text-canopy-green transition-colors duration-200 hover:bg-sky-wash">Ajukan Penyaluran</a>
                                @endcan
                                @can('delete', $campaign)
                                    <button type="button" @click="confirmId = {{ $campaign->id }}; confirmName = '{{ addslashes($campaign->name) }}'" class="inline-flex items-center rounded-full border border-flag-red/30 px-3 py-1 text-xs font-medium text-flag-red transition-all duration-200 hover:bg-flag-red/10 active:scale-95">Hapus</button>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="border-b border-frost-gray px-4 py-6 text-center text-graphite">Belum ada program donasi.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">{{ $campaigns->links() }}</div>

    <div
        x-show="confirmId !== null"
        x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center bg-ink-black/30 backdrop-blur-sm px-4"
        style="display: none"
    >
        <div
            x-show="confirmId !== null"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-90"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-90"
            @click.outside="confirmId = null"
            class="w-full max-w-sm rounded-3xl bg-white p-6 shadow-xl"
        >
            <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-flag-red/10 text-flag-red">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2m3 0-1 14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2L4 6"/></svg>
            </div>
            <h2 class="mt-4 text-center font-semibold text-ink-black">Hapus Program Donasi?</h2>
            <p class="mt-1 text-center text-sm text-graphite">
                <span class="font-medium" x-text="confirmName"></span> akan dihapus permanen. Tindakan ini tidak dapat dibatalkan.
            </p>
            <div class="mt-6 flex gap-3">
                <button type="button" @click="confirmId = null" class="flex-1 rounded-full border border-frost-gray px-4 py-2 text-sm font-medium text-graphite transition-all duration-200 ease-out hover:bg-cloud-gray active:scale-95">Batal</button>
                <button type="button" @click="$wire.delete(confirmId); confirmId = null" class="flex-1 rounded-full bg-flag-red px-4 py-2 text-sm font-medium text-white transition-all duration-200 ease-out hover:scale-[1.03] active:scale-95">Ya, Hapus</button>
            </div>
        </div>
    </div>
</div>
