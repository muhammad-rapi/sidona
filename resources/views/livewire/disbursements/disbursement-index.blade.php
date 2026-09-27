<div x-data="{ confirmId: null, confirmProgram: '' }">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-xl font-semibold">Penyaluran Dana</h1>
        <select wire:model.live="status" class="rounded-full border border-frost-gray px-4 py-2 text-sm outline-none transition-all duration-200 focus:border-coral-pulse focus:ring-2 focus:ring-coral-pulse/20">
            <option value="submitted">Diajukan</option>
            <option value="approved">Disetujui</option>
            <option value="rejected">Ditolak</option>
            <option value="all">Semua</option>
        </select>
    </div>

    @error('approve') <p class="text-sm text-red-700 mb-4">{{ $message }}</p> @enderror

    <div class="bg-white rounded-3xl border border-frost-gray overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr>
                        <th class="border-b-2 border-ink-black px-4 py-3 text-left font-medium text-graphite">Program</th>
                        <th class="border-b-2 border-ink-black px-4 py-3 text-left font-medium text-graphite">Jumlah</th>
                        <th class="border-b-2 border-ink-black px-4 py-3 text-left font-medium text-graphite">Keterangan</th>
                        <th class="border-b-2 border-ink-black px-4 py-3 text-left font-medium text-graphite">Diajukan Oleh</th>
                        <th class="border-b-2 border-ink-black px-4 py-3 text-left font-medium text-graphite">Status</th>
                        <th class="border-b-2 border-ink-black px-4 py-3 text-left font-medium text-graphite">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($disbursements as $disbursement)
                        @php
                            $statusBadge = match ($disbursement->status->value) {
                                'approved' => ['bg-mint-wash', 'text-canopy-green'],
                                'rejected' => ['bg-flag-red/10', 'text-flag-red'],
                                default => ['bg-sand', 'text-graphite'],
                            };
                        @endphp
                        <tr class="hover:bg-mint-wash/40 transition-colors duration-150">
                            <td class="border-b border-frost-gray px-4 py-3 font-medium">
                                {{ $disbursement->campaign->name }}
                                @if ($disbursement->campaign->bank_name && $disbursement->campaign->account_number)
                                    <p class="text-xs font-normal text-graphite">{{ $disbursement->campaign->bank_name }} <span class="font-mono">{{ $disbursement->campaign->account_number }}</span></p>
                                @endif
                            </td>
                            <td class="border-b border-frost-gray px-4 py-3 font-mono whitespace-nowrap">Rp {{ number_format($disbursement->amount, 0, ',', '.') }}</td>
                            <td class="border-b border-frost-gray px-4 py-3">{{ $disbursement->description }}</td>
                            <td class="border-b border-frost-gray px-4 py-3 whitespace-nowrap">{{ $disbursement->submitter->name }}</td>
                            <td class="border-b border-frost-gray px-4 py-3 whitespace-nowrap">
                                <span class="inline-flex items-center rounded-full {{ $statusBadge[0] }} {{ $statusBadge[1] }} px-3 py-1 text-xs font-medium">{{ $disbursement->status->label() }}</span>
                            </td>
                            <td class="border-b border-frost-gray px-4 py-3 whitespace-nowrap space-x-2">
                                @can('approve', $disbursement)
                                    <button type="button" @click="confirmId = {{ $disbursement->id }}; confirmProgram = '{{ addslashes($disbursement->campaign->name) }}'" class="inline-flex items-center rounded-full bg-mint-wash px-3 py-1 text-xs font-medium text-canopy-green transition-all duration-200 hover:bg-sky-wash active:scale-95">Setujui</button>
                                @endcan
                                @can('reject', $disbursement)
                                    <button type="button" wire:click="startReject({{ $disbursement->id }})" class="inline-flex items-center rounded-full border border-flag-red/30 px-3 py-1 text-xs font-medium text-flag-red transition-colors duration-200 hover:bg-flag-red/10">Tolak</button>
                                @endcan
                            </td>
                        </tr>
                        @if ($rejectingId === $disbursement->id)
                            <tr>
                                <td colspan="6" class="border-b border-frost-gray px-4 py-3 bg-cloud-gray">
                                    <form wire:submit="confirmReject" class="flex items-start gap-3">
                                        <div class="flex-1">
                                            <textarea wire:model="rejectionReason" class="w-full rounded border border-frost-gray px-3 py-2 text-sm outline-none transition-all duration-200 focus:border-coral-pulse focus:ring-2 focus:ring-coral-pulse/20" placeholder="Alasan penolakan"></textarea>
                                            @error('rejectionReason') <p class="text-sm text-red-700 mt-1">{{ $message }}</p> @enderror
                                        </div>
                                        <button type="submit" class="rounded-full bg-coral-pulse px-5 py-2 text-white text-sm font-medium transition-all duration-200 ease-out hover:scale-[1.03] hover:bg-coral-pulse-dark active:scale-95">Kirim</button>
                                        <button type="button" wire:click="cancelReject" class="rounded-full border border-ink-black px-5 py-2 text-sm transition-all duration-200 ease-out hover:scale-[1.03] hover:bg-ink-black hover:text-white active:scale-95">Batal</button>
                                    </form>
                                </td>
                            </tr>
                        @endif
                    @empty
                        <tr>
                            <td colspan="6" class="border-b border-frost-gray px-4 py-6 text-center text-graphite">Tidak ada penyaluran.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">{{ $disbursements->links() }}</div>

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
            <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-mint-wash text-canopy-green">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
            </div>
            <h2 class="mt-4 text-center font-semibold text-ink-black">Setujui Penyaluran?</h2>
            <p class="mt-1 text-center text-sm text-graphite">
                Penyaluran untuk <span class="font-medium" x-text="confirmProgram"></span> akan ditandai sebagai disetujui.
            </p>
            <div class="mt-6 flex gap-3">
                <button type="button" @click="confirmId = null" class="flex-1 rounded-full border border-frost-gray px-4 py-2 text-sm font-medium text-graphite transition-all duration-200 ease-out hover:bg-cloud-gray active:scale-95">Batal</button>
                <button type="button" @click="$wire.approve(confirmId); confirmId = null" class="flex-1 rounded-full bg-coral-pulse px-4 py-2 text-sm font-medium text-white transition-all duration-200 ease-out hover:scale-[1.03] hover:bg-coral-pulse-dark active:scale-95">Ya, Setujui</button>
            </div>
        </div>
    </div>
</div>
