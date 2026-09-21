<?php

namespace App\Livewire;

use App\Domain\Services\BalanceService;
use App\Models\InstructorProfile;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class InstructorBalanceDashboard extends Component
{
    public string $search = '';

    public function render(): View
    {
        $query = InstructorProfile::query()->with('user')->orderBy('id');
        if ($this->search !== '') {
            $query->whereHas('user', fn ($q) => $q->where('name', 'like', '%'.$this->search.'%'));
        }
        $rows = $query->get()->map(fn (InstructorProfile $instructor): array => ['instructor' => $instructor, 'balance' => app(BalanceService::class)->forInstructor($instructor)]);

        return view('livewire.instructor-balance-dashboard', ['rows' => $rows]);
    }
}
