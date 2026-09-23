<?php

namespace App\Livewire\Audit;

use App\Models\LoginLog;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('components.layouts.app')]
class LoginLogIndex extends Component
{
    #[Url]
    public string $status = '';

    #[Url]
    public string $email = '';

    #[Url]
    public string $from = '';

    #[Url]
    public string $to = '';

    public function render()
    {
        $query = LoginLog::query()->latest();

        $query->when($this->status !== '', fn ($q) => $q->where('status', $this->status));
        $query->when($this->email !== '', fn ($q) => $q->where('email', 'like', "%{$this->email}%"));
        $query->when($this->from !== '', fn ($q) => $q->whereDate('created_at', '>=', $this->from));
        $query->when($this->to !== '', fn ($q) => $q->whereDate('created_at', '<=', $this->to));

        return view('livewire.audit.login-log-index', [
            'entries' => $query->paginate(15),
        ]);
    }
}
