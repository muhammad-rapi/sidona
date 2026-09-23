<div>
    <h1 class="text-xl font-semibold mb-6">Log Login</h1>

    <form class="flex flex-wrap gap-3 mb-4 bg-white p-4 rounded border border-slate-300">
        <input type="text" wire:model.live="email" placeholder="Email" class="rounded border border-slate-300 px-3 py-2 text-sm">
        <select wire:model.live="status" class="rounded border border-slate-300 px-3 py-2 text-sm">
            <option value="">Semua Status</option>
            <option value="success">Berhasil</option>
            <option value="failed">Gagal</option>
        </select>
        <input type="date" wire:model.live="from" class="rounded border border-slate-300 px-3 py-2 text-sm">
        <input type="date" wire:model.live="to" class="rounded border border-slate-300 px-3 py-2 text-sm">
    </form>

    <table class="w-full border border-slate-300 text-sm bg-white">
        <thead class="bg-slate-200">
            <tr>
                <th class="border border-slate-300 px-3 py-2 text-left">Waktu</th>
                <th class="border border-slate-300 px-3 py-2 text-left">Email</th>
                <th class="border border-slate-300 px-3 py-2 text-left">Alamat IP</th>
                <th class="border border-slate-300 px-3 py-2 text-left">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($entries as $entry)
                <tr>
                    <td class="border border-slate-300 px-3 py-2 font-mono">{{ $entry->created_at->format('d/m/Y H:i') }}</td>
                    <td class="border border-slate-300 px-3 py-2">{{ $entry->email }}</td>
                    <td class="border border-slate-300 px-3 py-2 font-mono">{{ $entry->ip_address }}</td>
                    <td class="border border-slate-300 px-3 py-2">{{ $entry->status === 'success' ? 'Berhasil' : 'Gagal' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="border border-slate-300 px-3 py-6 text-center text-slate-500">Tidak ada catatan login.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="mt-4">{{ $entries->links() }}</div>
</div>
