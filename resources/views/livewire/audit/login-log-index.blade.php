<div>
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-xl font-semibold">Log Login</h1>
        <button type="button" wire:click="exportPdf" class="rounded-full bg-coral-pulse px-6 py-3 text-white text-sm font-medium transition-all duration-200 ease-out hover:scale-[1.03] hover:bg-coral-pulse-dark active:scale-95">Unduh PDF</button>
    </div>

    <form class="flex flex-wrap gap-3 mb-4 bg-white p-4 rounded-2xl border border-frost-gray">
        <input type="text" wire:model.live="email" placeholder="Email" class="rounded border border-frost-gray px-3 py-2 text-sm">
        <select wire:model.live="status" class="rounded border border-frost-gray px-3 py-2 text-sm">
            <option value="">Semua Status</option>
            <option value="success">Berhasil</option>
            <option value="failed">Gagal</option>
        </select>
        <input type="date" wire:model.live="from" class="rounded border border-frost-gray px-3 py-2 text-sm">
        <input type="date" wire:model.live="to" class="rounded border border-frost-gray px-3 py-2 text-sm">
    </form>

    <table class="w-full text-sm">
        <thead>
            <tr>
                <th class="border-b-2 border-ink-black px-4 py-3 text-left font-medium text-graphite">Waktu</th>
                <th class="border-b-2 border-ink-black px-4 py-3 text-left font-medium text-graphite">Email</th>
                <th class="border-b-2 border-ink-black px-4 py-3 text-left font-medium text-graphite">Alamat IP</th>
                <th class="border-b-2 border-ink-black px-4 py-3 text-left font-medium text-graphite">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($entries as $entry)
                <tr>
                    <td class="border-b border-frost-gray px-4 py-3 font-mono">{{ $entry->created_at->format('d/m/Y H:i') }}</td>
                    <td class="border-b border-frost-gray px-4 py-3">{{ $entry->email }}</td>
                    <td class="border-b border-frost-gray px-4 py-3 font-mono">{{ $entry->ip_address }}</td>
                    <td class="border-b border-frost-gray px-4 py-3">{{ $entry->status === 'success' ? 'Berhasil' : 'Gagal' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="border-b border-frost-gray px-4 py-6 text-center text-graphite">Tidak ada catatan login.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="mt-4">{{ $entries->links() }}</div>
</div>
