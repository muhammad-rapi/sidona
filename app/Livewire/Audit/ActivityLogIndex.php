<?php

namespace App\Livewire\Audit;

use App\Models\ActivityLog;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('components.layouts.app')]
class ActivityLogIndex extends Component
{
    #[Url]
    public string $action = '';

    #[Url]
    public string $user_id = '';

    #[Url]
    public string $from = '';

    #[Url]
    public string $to = '';

    public ?int $expandedId = null;

    public function toggle(int $id): void
    {
        $this->expandedId = $this->expandedId === $id ? null : $id;
    }

    public function render()
    {
        $query = ActivityLog::query()->with('user')->latest();

        $query->when($this->action !== '', fn ($q) => $q->where('action', 'like', "%{$this->action}%"));
        $query->when($this->user_id !== '', fn ($q) => $q->where('user_id', $this->user_id));
        $query->when($this->from !== '', fn ($q) => $q->whereDate('created_at', '>=', $this->from));
        $query->when($this->to !== '', fn ($q) => $q->whereDate('created_at', '<=', $this->to));

        return view('livewire.audit.activity-log-index', [
            'entries' => $query->paginate(15),
        ]);
    }
}
