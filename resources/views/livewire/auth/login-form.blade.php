<div class="flex min-h-screen items-center justify-center px-5 py-10">
    <div class="w-full max-w-sm">
        <a href="{{ route('program.index') }}" class="paint-type block text-center text-5xl tracking-wide text-board">Sidona</a>
        <p class="mt-2 text-center text-sm text-white/70">Panel staf: bendahara, admin, dan auditor.</p>

        <form wire:submit="authenticate" class="on-board mt-8 border-4 border-board bg-board p-6 text-ink" novalidate>
            <h1 class="paint-type text-4xl">Masuk</h1>

            <div class="mt-6">
                <label for="email" class="label">Email</label>
                <input id="email" type="email" wire:model="email" autocomplete="username" class="field @error('email') field-error @enderror">
                @error('email') <p class="error-text" role="alert">{{ $message }}</p> @enderror
            </div>

            <div class="mt-4">
                <label for="password" class="label">Kata sandi</label>
                <input id="password" type="password" wire:model="password" autocomplete="current-password" class="field @error('password') field-error @enderror">
                @error('password') <p class="error-text" role="alert">{{ $message }}</p> @enderror
            </div>

            <button type="submit" class="btn btn-ink btn-lg mt-6 w-full" wire:loading.attr="disabled">Masuk</button>
        </form>

        <p class="mt-6 text-center text-sm"><a href="{{ route('program.index') }}" class="text-white/70 underline underline-offset-4 hover:text-board">Kembali ke situs donasi</a></p>
    </div>
</div>
