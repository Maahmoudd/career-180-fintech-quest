<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $role
 *
 * @use HasFactory<UserFactory>
 */
#[Fillable(['name', 'email', 'role', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected function casts(): array
    {
        return ['email_verified_at' => 'datetime', 'password' => 'hashed'];
    }

    /** @return HasOne<InstructorProfile, $this> */
    public function instructorProfile(): HasOne
    {
        return $this->hasOne(InstructorProfile::class);
    }

    /** @return HasOne<StudentProfile, $this> */
    public function studentProfile(): HasOne
    {
        return $this->hasOne(StudentProfile::class);
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return in_array($this->role, ['admin', 'instructor'], true);
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function initials(): string
    {
        $i = Str::initials($this->name, true);

        return Str::length($i) > 1 ? Str::substr($i, 0, 1).Str::substr($i, -1) : $i;
    }
}
