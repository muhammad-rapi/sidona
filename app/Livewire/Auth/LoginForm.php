<?php

namespace App\Livewire\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.guest')]
class LoginForm extends Component
{
    public string $email = '';

    public string $password = '';

    public function authenticate(): void
    {
        $credentials = $this->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $candidate = User::query()->where('email', $credentials['email'])->first();

        if ($candidate && ! $candidate->is_active && Hash::check($credentials['password'], $candidate->password)) {
            throw ValidationException::withMessages([
                'email' => 'Akun ini dinonaktifkan. Hubungi super admin.',
            ]);
        }

        if (! Auth::attempt($credentials + ['is_active' => true])) {
            throw ValidationException::withMessages([
                'email' => 'Email atau kata sandi salah.',
            ]);
        }

        session()->regenerate();
        session()->flash('status', 'Berhasil masuk. Selamat datang, '.auth()->user()->name.'.');

        $this->redirectRoute('dashboard', navigate: true);
    }

    public function render()
    {
        return view('livewire.auth.login-form');
    }
}
