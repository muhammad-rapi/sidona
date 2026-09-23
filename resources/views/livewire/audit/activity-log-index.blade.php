<div>
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-xl font-semibold">Log Aktivitas</h1>
        <button type="button" wire:click="exportPdf" class="rounded bg-slate-900 px-4 py-2 text-white text-sm">Unduh PDF</button>
    </div>

    <form class="flex flex-wrap gap-3 mb-4 bg-white p-4 rounded border border-slate-300">
        <input type="text" wire:model.live="action" placeholder="Jenis aksi" class="rounded border border-slate-300 px-3 py-2 text-sm">
        <input type="date" wire:model.live="from" class="rounded border border-slate-300 px-3 py-2 text-sm">
        <input type="date" wire:model.live="to" class="rounded border border-slate-300 px-3 py-2 text-sm">
    </form>

    <table class="w-full border border-slate-300 text-sm bg-white">
        <thead class="bg-slate-200">
            <tr>
                <th class="border border-slate-300 px-3 py-2 text-left">Waktu</th>
                <th class="border border-slate-300 px-3 py-2 text-left">Pengguna</th>
                <th class="border border-slate-300 px-3 py-2 text-left">Aksi</th>
                <th class="border border-slate-300 px-3 py-2 text-left">Objek</th>
                <th class="border border-slate-300 px-3 py-2 text-left"></th>
            </tr>
        </thead>
        <tbody>
            @forelse ($entries as $entry)
                <tr>
                    <td class="border border-slate-300 px-3 py-2 font-mono">{{ $entry->created_at->format('d/m/Y H:i') }}</td>
                    <td class="border border-slate-300 px-3 py-2">{{ $entry->user?->name ?? 'Tamu' }}</td>
                    <td class="border border-slate-300 px-3 py-2 font-mono">{{ $entry->action }}</td>
                    <td class="border border-slate-300 px-3 py-2">{{ $entry->subject_type ? class_basename($entry->subject_type).' #'.$entry->subject_id : '-' }}</td>
                    <td class="border border-slate-300 px-3 py-2">
                        <button type="button" wire:click="toggle({{ $entry->id }})">Detail</button>
                    </td>
                </tr>
                @if ($expandedId === $entry->id)
                    <tr>
                        <td colspan="5" class="border border-slate-300 px-3 py-3 bg-slate-50">
                            <dl class="text-sm space-y-1">
                                @foreach (array_unique(array_merge(array_keys($entry->before ?? []), array_keys($entry->after ?? []))) as $key)
                                    @php($beforeValue = data_get($entry->before, $key))
                                    @php($afterValue = data_get($entry->after, $key))
                                    @if ($beforeValue !== $afterValue)
                                        <div class="flex gap-2">
                                            <dt class="font-medium w-32">{{ $key }}</dt>
                                            <dd>{{ is_scalar($beforeValue) ? $beforeValue : json_encode($beforeValue) }} &rarr; {{ is_scalar($afterValue) ? $afterValue : json_encode($afterValue) }}</dd>
                                        </div>
                                    @endif
                                @endforeach
                            </dl>
                        </td>
                    </tr>
                @endif
            @empty
                <tr>
                    <td colspan="5" class="border border-slate-300 px-3 py-6 text-center text-slate-500">Tidak ada catatan aktivitas.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="mt-4">{{ $entries->links() }}</div>
</div>
