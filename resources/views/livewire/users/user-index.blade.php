<div>
    <div class="page-head">
        <div>
            <h1 class="page-title">Kelola pengguna</h1>
            <p class="page-sub">Akun staf: buat, ubah peran, atur ulang kata sandi, atau nonaktifkan. Akun tidak dihapus agar jejak audit tetap utuh.</p>
        </div>
        <button type="button" wire:click="startCreate" class="btn btn-paint"><x-icon name="plus" />Pengguna baru</button>
    </div>

    @if ($editingId !== null)
        <form wire:submit="save" class="panel mb-6 space-y-5 border-ink p-6" novalidate>
            <h2 class="text-lg font-extrabold">{{ $editingId === 0 ? 'Pengguna baru' : 'Ubah pengguna' }}</h2>
            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <label for="u-name" class="label">Nama</label>
                    <input id="u-name" type="text" wire:model="name" class="field @error('name') field-error @enderror">
                    @error('name') <p class="error-text" role="alert">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="u-email" class="label">Email</label>
                    <input id="u-email" type="email" wire:model="email" class="field @error('email') field-error @enderror">
                    @error('email') <p class="error-text" role="alert">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="u-role" class="label">Peran</label>
                    <select id="u-role" wire:model="userRole" class="field @error('userRole') field-error @enderror" @disabled($editingId === auth()->id())>
                        @foreach ($roles as $r)
                            <option value="{{ $r->value }}">{{ $r->label() }}</option>
                        @endforeach
                    </select>
                    @error('userRole') <p class="error-text" role="alert">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="u-password" class="label">{{ $editingId === 0 ? 'Kata sandi awal' : 'Atur ulang kata sandi (opsional)' }}</label>
                    <input id="u-password" type="password" wire:model="password" autocomplete="new-password" class="field @error('password') field-error @enderror">
                    <p class="hint">Minimal 8 karakter, memuat huruf dan angka. {{ $editingId === 0 ? 'Sampaikan ke pengguna, lalu minta diganti di Profil.' : 'Kosongkan jika tidak diganti.' }}</p>
                    @error('password') <p class="error-text" role="alert">{{ $message }}</p> @enderror
                </div>
            </div>
            <div class="flex flex-wrap gap-2">
                <button type="submit" class="btn btn-paint" wire:loading.attr="disabled" wire:target="save">Simpan</button>
                <button type="button" wire:click="cancelEdit" class="btn btn-line">Batal</button>
            </div>
        </form>
    @endif

    <div class="mb-4 flex flex-wrap items-end gap-3">
        <div class="w-full sm:w-72">
            <label for="u-search" class="sr-only">Cari pengguna</label>
            <input id="u-search" type="search" wire:model.live.debounce.300ms="search" placeholder="Cari nama atau email" class="field field-sm">
        </div>
        <div>
            <label for="u-filter" class="sr-only">Filter peran</label>
            <select id="u-filter" wire:model.live="role" class="field field-sm w-auto">
                <option value="">Semua peran</option>
                @foreach ($roles as $r)
                    <option value="{{ $r->value }}">{{ $r->label() }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="panel overflow-x-auto">
        <table class="ledger">
            <thead>
                <tr>
                    <th>Nama</th>
                    <th>Peran</th>
                    <th>Status</th>
                    <th>Dibuat</th>
                    <th class="sticky-col"><span class="sr-only">Aksi</span></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($users as $user)
                    <tr class="{{ $user->is_active ? '' : 'text-ink-faint' }}">
                        <td>
                            <p class="font-bold">{{ $user->name }}@if ($user->id === auth()->id()) <span class="badge badge-ink ml-1">Anda</span>@endif</p>
                            <p class="text-xs text-ink-soft">{{ $user->email }}</p>
                        </td>
                        <td>{{ $user->role->label() }}</td>
                        <td><span class="badge {{ $user->is_active ? 'badge-paid' : 'badge-fail' }}">{{ $user->is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
                        <td class="whitespace-nowrap">{{ $user->created_at->timezone('Asia/Jakarta')->format('d/m/Y') }}</td>
                        <td class="sticky-col whitespace-nowrap">
                            <div class="flex flex-nowrap items-center gap-1.5">
                                <x-action :icon="$detailId === $user->id ? 'close' : 'detail'" :label="$detailId === $user->id ? 'Tutup detail' : 'Lihat detail'" wire:click="toggleDetail({{ $user->id }})" aria-expanded="{{ $detailId === $user->id ? 'true' : 'false' }}" />
                                <x-action icon="edit" label="Ubah pengguna" wire:click="startEdit({{ $user->id }})" />
                                @if ($user->id !== auth()->id())
                                    <x-action :icon="$user->is_active ? 'reject' : 'approve'" :variant="$user->is_active ? 'danger' : 'ink'" :label="$user->is_active ? 'Nonaktifkan akun' : 'Aktifkan akun'" wire:click="toggleActive({{ $user->id }})" wire:confirm="{{ $user->is_active ? 'Nonaktifkan akun ini? Pengguna tidak bisa login lagi.' : 'Aktifkan kembali akun ini?' }}" />
                                @endif
                            </div>
                        </td>
                    </tr>
                    @if ($detailId === $user->id && $detailUser)
                        <tr>
                            <td colspan="5" class="!bg-desk !p-0">
                                <div class="grid gap-x-10 gap-y-6 px-5 py-5 md:grid-cols-2">
                                    <div>
                                        <p class="mb-2 text-sm font-bold text-ink-soft">Login terakhir</p>
                                        @forelse ($detailLogins as $login)
                                            <div class="flex items-baseline justify-between gap-4 border-b border-rule py-1.5 text-sm">
                                                <span><span class="badge {{ $login->status === 'failed' ? 'badge-fail' : 'badge-paid' }}">{{ $login->status === 'failed' ? 'Gagal' : 'Berhasil' }}</span> {{ $login->deviceLabel() }} <span class="font-mono text-xs">{{ $login->ip_address }}</span></span>
                                                <span class="shrink-0 text-ink-soft">{{ $login->created_at->timezone('Asia/Jakarta')->format('d/m/Y H:i') }}</span>
                                            </div>
                                        @empty
                                            <p class="text-sm text-ink-soft">Belum pernah login.</p>
                                        @endforelse
                                    </div>
                                    <div>
                                        <p class="mb-2 text-sm font-bold text-ink-soft">Aktivitas terakhir</p>
                                        @forelse ($detailTrail as $entry)
                                            <div class="flex items-baseline justify-between gap-4 border-b border-rule py-1.5 text-sm">
                                                <span class="font-mono text-xs">{{ $entry->action }}</span>
                                                <span class="shrink-0 text-ink-soft">{{ $entry->created_at->timezone('Asia/Jakarta')->format('d/m/Y H:i') }}</span>
                                            </div>
                                        @empty
                                            <p class="text-sm text-ink-soft">Belum ada aktivitas tercatat.</p>
                                        @endforelse
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endif
                @empty
                    <tr><td colspan="5" class="py-10 text-center text-ink-soft">Tidak ada pengguna yang cocok.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $users->links() }}</div>
</div>
