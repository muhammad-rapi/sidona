<?php

namespace App\Livewire\Users;

use App\Enums\UserRole;
use App\Models\ActivityLog;
use App\Models\LoginLog;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app', ['title' => 'Kelola Pengguna'])]
class UserIndex extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $role = '';

    /** null = form tertutup, 0 = pengguna baru, >0 = ubah pengguna */
    public ?int $editingId = null;

    public ?int $detailId = null;

    public string $name = '';

    public string $email = '';

    public string $userRole = 'bendahara';

    public string $password = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedRole(): void
    {
        $this->resetPage();
    }

    public function startCreate(): void
    {
        $this->resetErrorBag();
        $this->reset(['name', 'email', 'password']);
        $this->userRole = UserRole::Bendahara->value;
        $this->editingId = 0;
    }

    public function startEdit(int $userId): void
    {
        $user = User::query()->findOrFail($userId);

        $this->resetErrorBag();
        $this->name = $user->name;
        $this->email = $user->email;
        $this->userRole = $user->role->value;
        $this->password = '';
        $this->editingId = $user->id;
    }

    public function cancelEdit(): void
    {
        $this->editingId = null;
        $this->resetErrorBag();
    }

    public function toggleDetail(int $userId): void
    {
        $this->detailId = $this->detailId === $userId ? null : $userId;
    }

    public function save(AuditLogger $logger): void
    {
        abort_unless($this->editingId !== null, 422);

        $isNew = $this->editingId === 0;
        $user = $isNew ? null : User::query()->findOrFail($this->editingId);

        $data = $this->validate([
            'name' => ['required', 'string', 'min:2', 'max:100'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user?->id)],
            'userRole' => ['required', Rule::enum(UserRole::class)],
            'password' => [$isNew ? 'required' : 'nullable', Password::min(8)->letters()->numbers()],
        ], [
            'email.unique' => 'Email ini sudah dipakai akun lain.',
            'password.required' => 'Kata sandi awal wajib diisi.',
        ]);

        if ($user && $user->id === auth()->id() && $data['userRole'] !== $user->role->value) {
            $this->addError('userRole', 'Anda tidak bisa mengubah peran akun Anda sendiri.');

            return;
        }

        $attributes = [
            'name' => trim($data['name']),
            'email' => strtolower(trim($data['email'])),
            'role' => $data['userRole'],
        ];

        if ($isNew) {
            $created = User::create($attributes + ['password' => $data['password'], 'is_active' => true]);
            $logger->log('user.created', auth()->user(), $created, [], $created->only(['name', 'email', 'role']));
            session()->flash('status', 'Pengguna baru dibuat.');
        } else {
            $before = $user->only(['name', 'email', 'role']);
            $user->update($attributes + ($data['password'] ? ['password' => $data['password']] : []));
            $logger->log('user.updated', auth()->user(), $user, $before, $user->only(['name', 'email', 'role']));

            if ($data['password']) {
                $logger->log('user.password_reset', auth()->user(), $user, [], []);
            }

            session()->flash('status', 'Data pengguna diperbarui.');
        }

        $this->cancelEdit();
    }

    public function toggleActive(int $userId, AuditLogger $logger): void
    {
        $user = User::query()->findOrFail($userId);

        if ($user->id === auth()->id()) {
            session()->flash('status', 'Anda tidak bisa menonaktifkan akun Anda sendiri.');

            return;
        }

        $user->update(['is_active' => ! $user->is_active]);
        $logger->log($user->is_active ? 'user.activated' : 'user.deactivated', auth()->user(), $user, [], ['is_active' => $user->is_active]);

        session()->flash('status', $user->is_active ? 'Akun diaktifkan.' : 'Akun dinonaktifkan. Pengguna ini tidak bisa login lagi.');
    }

    public function render()
    {
        $users = User::query()
            ->when(trim($this->search) !== '', fn ($q) => $q->where(fn ($q) => $q
                ->where('name', 'like', '%'.trim($this->search).'%')
                ->orWhere('email', 'like', '%'.trim($this->search).'%')))
            ->when($this->role !== '', fn ($q) => $q->where('role', $this->role))
            ->orderByDesc('is_active')
            ->orderBy('name')
            ->paginate(12);

        $detailUser = $this->detailId ? User::query()->find($this->detailId) : null;

        return view('livewire.users.user-index', [
            'users' => $users,
            'roles' => UserRole::cases(),
            'detailUser' => $detailUser,
            'detailLogins' => $detailUser
                ? LoginLog::query()->where('email', $detailUser->email)->latest('created_at')->limit(5)->get()
                : collect(),
            'detailTrail' => $detailUser
                ? ActivityLog::query()->where('user_id', $detailUser->id)->latest('id')->limit(5)->get()
                : collect(),
        ]);
    }
}
