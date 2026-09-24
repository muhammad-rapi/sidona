<div class="max-w-sm mx-auto mt-24">
    <h1 class="text-xl font-semibold mb-6 text-center">Masuk ke SIDONA</h1>

    <form wire:submit="authenticate" class="space-y-4 bg-white p-6 rounded border border-line">
        <div>
            <label class="block text-sm font-medium mb-1">Email</label>
            <input type="email" wire:model="email" class="w-full rounded border border-line px-3 py-2">
            @error('email') <p class="text-sm text-red-700 mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Kata Sandi</label>
            <input type="password" wire:model="password" class="w-full rounded border border-line px-3 py-2">
            @error('password') <p class="text-sm text-red-700 mt-1">{{ $message }}</p> @enderror
        </div>

        <button type="submit" class="w-full rounded-full bg-coral px-6 py-3 text-white text-sm font-medium transition-colors duration-200 hover:bg-coral-dark">Masuk</button>
    </form>
</div>
