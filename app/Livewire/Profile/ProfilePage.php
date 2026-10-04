<?php

namespace App\Livewire\Profile;

use App\Models\LoginLog;
use App\Services\AuditLogger;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app', ['title' => 'Profil'])]
class ProfilePage extends Component
{
    public string $name = '';

    public string $email = '';

    public string $current_password = '';

    public string $password = '';

    public string $password_confirmation = '';

    public function mount(): void
    {
        $this->name = auth()->user()->name;
        $this->email = auth()->user()->email;
    }

    public function saveProfile(AuditLogger $logger): void
    {
        $user = auth()->user();

        $data = $this->validate([
            'name' => ['required', 'string', 'min:2', 'max:100'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
        ], [
            'email.unique' => 'Email ini sudah dipakai akun lain.',
        ]);

        $before = $user->only(['name', 'email']);
        $user->update(['name' => trim($data['name']), 'email' => strtolower(trim($data['email']))]);

        if ($before !== $user->only(['name', 'email'])) {
            $logger->log('profile.updated', $user, $user, $before, $user->only(['name', 'email']));
        }

        session()->flash('status', 'Profil diperbarui.');
        $this->redirectRoute('profile', navigate: true);
    }

    public function changePassword(AuditLogger $logger): void
    {
        $user = auth()->user();

        $this->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', 'different:current_password', Password::min(8)->letters()->numbers()],
        ], [
            'current_password.current_password' => 'Kata sandi saat ini salah.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak sama.',
            'password.different' => 'Kata sandi baru harus berbeda dari yang lama.',
        ]);

        $user->update(['password' => $this->password]);
        $logger->log('profile.password_changed', $user, $user, [], []);

        $this->reset(['current_password', 'password', 'password_confirmation']);
        session()->flash('status', 'Kata sandi diganti.');
        $this->redirectRoute('profile', navigate: true);
    }

    public function render()
    {
        return view('livewire.profile.profile-page', [
            'logins' => LoginLog::query()->where('email', auth()->user()->email)->latest('created_at')->limit(6)->get(),
        ]);
    }
}
