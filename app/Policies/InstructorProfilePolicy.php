<?php

namespace App\Policies;

use App\Models\InstructorProfile;
use App\Models\User;

class InstructorProfilePolicy
{
    public function view(User $user, InstructorProfile $instructor): bool
    {
        return $user->isAdmin() || $user->id === $instructor->user_id;
    }

    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->role === 'instructor';
    }

    public function reconcile(User $user): bool
    {
        return $user->isAdmin();
    }
}
