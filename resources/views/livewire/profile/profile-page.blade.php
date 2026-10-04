<div>
    <div class="page-head">
        <div>
            <h1 class="page-title">Profil saya</h1>
            <p class="page-sub">{{ auth()->user()->role->label() }} &middot; bergabung {{ auth()->user()->created_at->translatedFormat('j F Y') }}</p>
        </div>
    </div>

    <div class="grid max-w-5xl gap-8 lg:grid-cols-2">
        <form wire:submit="saveProfile" class="panel space-y-5 p-6" novalidate>
            <h2 class="text-lg font-extrabold">Data akun</h2>
            <div>
                <label for="name" class="label">Nama</label>
                <input id="name" type="text" wire:model="name" autocomplete="name" class="field @error('name') field-error @enderror">
                @error('name') <p class="error-text" role="alert">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="email" class="label">Email</label>
                <input id="email" type="email" wire:model="email" autocomplete="email" class="field @error('email') field-error @enderror">
                @error('email') <p class="error-text" role="alert">{{ $message }}</p> @enderror
            </div>
            <div>
                <p class="label">Peran</p>
                <p class="text-sm text-ink-soft">{{ auth()->user()->role->label() }}. Peran hanya bisa diubah oleh admin sistem.</p>
            </div>
            <button type="submit" class="btn btn-paint" wire:loading.attr="disabled" wire:target="saveProfile">Simpan profil</button>
        </form>

        <form wire:submit="changePassword" class="panel space-y-5 p-6" novalidate>
            <h2 class="text-lg font-extrabold">Ganti kata sandi</h2>
            <div>
                <label for="current_password" class="label">Kata sandi saat ini</label>
                <input id="current_password" type="password" wire:model="current_password" autocomplete="current-password" class="field @error('current_password') field-error @enderror">
                @error('current_password') <p class="error-text" role="alert">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="password" class="label">Kata sandi baru</label>
                <input id="password" type="password" wire:model="password" autocomplete="new-password" class="field @error('password') field-error @enderror">
                <p class="hint">Minimal 8 karakter, memuat huruf dan angka.</p>
                @error('password') <p class="error-text" role="alert">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="password_confirmation" class="label">Ulangi kata sandi baru</label>
                <input id="password_confirmation" type="password" wire:model="password_confirmation" autocomplete="new-password" class="field">
            </div>
            <button type="submit" class="btn btn-ink" wire:loading.attr="disabled" wire:target="changePassword">Ganti kata sandi</button>
        </form>
    </div>

    <section class="mt-10 max-w-5xl" aria-labelledby="aktivitas-login">
        <h2 id="aktivitas-login" class="mb-3 text-lg font-extrabold">Aktivitas login terakhir</h2>
        <div class="panel overflow-x-auto">
            <table class="ledger">
                <thead><tr><th>Waktu</th><th>Perangkat</th><th>Alamat IP</th><th>Status</th></tr></thead>
                <tbody>
                    @forelse ($logins as $login)
                        <tr>
                            <td class="whitespace-nowrap">{{ $login->created_at->timezone('Asia/Jakarta')->format('d/m/Y H:i') }}</td>
                            <td>{{ $login->deviceLabel() }}</td>
                            <td class="font-mono text-xs">{{ $login->ip_address }}</td>
                            <td><span class="badge {{ $login->status === 'failed' ? 'badge-fail' : 'badge-paid' }}">{{ $login->status === 'failed' ? 'Gagal' : 'Berhasil' }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="py-8 text-center text-ink-soft">Belum ada catatan login.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
