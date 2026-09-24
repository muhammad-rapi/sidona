<div class="min-h-[80vh] flex items-center justify-center px-4">
    <form wire:submit="authenticate" class="block bg-white p-6 max-w-[350px] w-full rounded-lg shadow-[0_10px_15px_-3px_rgba(0,0,0,0.1),0_4px_6px_-2px_rgba(0,0,0,0.05)]">
        <h1 class="text-lg font-medium text-center text-graphite">Masuk ke SIDONA</h1>

        <div class="relative mt-4">
            <input
                type="email"
                wire:model="email"
                placeholder="Email"
                class="bg-white p-4 pr-12 text-sm w-full rounded-lg border border-frost-gray shadow-[0_1px_2px_0_rgba(0,0,0,0.05)] outline-none"
            >
            <span class="absolute inset-y-0 right-0 grid place-content-center pr-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M4 4h16v16H4z" stroke="none"/>
                    <path d="M22 6l-10 7L2 6" />
                    <path d="M2 6a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V6z" />
                </svg>
            </span>
        </div>
        @error('email') <p class="text-sm text-red-700 mt-1">{{ $message }}</p> @enderror

        <div class="relative mt-2">
            <input
                type="password"
                wire:model="password"
                placeholder="Kata sandi"
                class="bg-white p-4 pr-12 text-sm w-full rounded-lg border border-frost-gray shadow-[0_1px_2px_0_rgba(0,0,0,0.05)] outline-none"
            >
            <span class="absolute inset-y-0 right-0 grid place-content-center pr-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="11" width="18" height="11" rx="2" />
                    <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                </svg>
            </span>
        </div>
        @error('password') <p class="text-sm text-red-700 mt-1">{{ $message }}</p> @enderror

        <button type="submit" class="block mt-4 py-3 px-5 bg-coral-pulse text-white text-sm font-medium w-full rounded-lg transition-all duration-200 ease-out hover:scale-[1.03] hover:bg-coral-pulse-dark active:scale-95">
            Masuk
        </button>
    </form>
</div>
