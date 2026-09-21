<?php

namespace App\Filament\Pages;

use App\Domain\Services\BalanceService;
use App\Models\InstructorProfile;
use BackedEnum;
use Filament\Pages\Page;

class InstructorBalances extends Page
{
    protected string $view = 'filament.pages.instructor-balances';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationLabel = 'Instructor balances';

    protected static ?string $title = 'Instructor balances and payout history';

    public function getViewData(): array
    {
        $balanceService = app(BalanceService::class);
        $query = InstructorProfile::query()->with(['user', 'payouts' => fn ($query) => $query->latest('id')->limit(5)]);
        if (! auth()->user()?->isAdmin()) {
            $query->where('user_id', auth()->id());
        }
        $instructors = $query->orderBy('id')->get();

        return ['rows' => $instructors->map(fn (InstructorProfile $instructor): array => ['instructor' => $instructor, 'balance' => $balanceService->forInstructor($instructor), 'payouts' => $instructor->payouts])];
    }
}
