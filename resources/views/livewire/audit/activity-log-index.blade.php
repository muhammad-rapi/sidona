<div>
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-xl font-semibold">Log Aktivitas</h1>
        <button type="button" wire:click="exportPdf" class="rounded-full bg-coral-pulse px-6 py-3 text-white text-sm font-medium transition-colors duration-200 hover:bg-coral-pulse-dark">Unduh PDF</button>
    </div>

    <form class="flex flex-wrap gap-3 mb-4 bg-white p-4 rounded-2xl border border-frost-gray">
        <input type="text" wire:model.live="action" placeholder="Jenis aksi" class="rounded border border-frost-gray px-3 py-2 text-sm">
        <input type="date" wire:model.live="from" class="rounded border border-frost-gray px-3 py-2 text-sm">
        <input type="date" wire:model.live="to" class="rounded border border-frost-gray px-3 py-2 text-sm">
    </form>

    <table class="w-full text-sm">
        <thead>
            <tr>
                <th class="border-b-2 border-ink-black px-4 py-3 text-left font-medium text-graphite">Waktu</th>
                <th class="border-b-2 border-ink-black px-4 py-3 text-left font-medium text-graphite">Pengguna</th>
                <th class="border-b-2 border-ink-black px-4 py-3 text-left font-medium text-graphite">Aksi</th>
                <th class="border-b-2 border-ink-black px-4 py-3 text-left font-medium text-graphite">Objek</th>
                <th class="border-b-2 border-ink-black px-4 py-3 text-left font-medium text-graphite"></th>
            </tr>
        </thead>
        <tbody>
            @forelse ($entries as $entry)
                <tr>
                    <td class="border-b border-frost-gray px-4 py-3 font-mono">{{ $entry->created_at->format('d/m/Y H:i') }}</td>
                    <td class="border-b border-frost-gray px-4 py-3">{{ $entry->user?->name ?? 'Tamu' }}</td>
                    <td class="border-b border-frost-gray px-4 py-3 font-mono">{{ $entry->action }}</td>
                    <td class="border-b border-frost-gray px-4 py-3">{{ $entry->subject_type ? class_basename($entry->subject_type).' #'.$entry->subject_id : '-' }}</td>
                    <td class="border-b border-frost-gray px-4 py-3">
                        <button type="button" wire:click="toggle({{ $entry->id }})">Detail</button>
                    </td>
                </tr>
                @if ($expandedId === $entry->id)
                    <tr>
                        <td colspan="5" class="border-b border-frost-gray px-4 py-3 bg-cloud-gray">
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
                    <td colspan="5" class="border-b border-frost-gray px-4 py-6 text-center text-graphite">Tidak ada catatan aktivitas.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="mt-4">{{ $entries->links() }}</div>
</div>
