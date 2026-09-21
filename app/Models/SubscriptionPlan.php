<?php

namespace App\Models;

use App\Enums\PlanInterval;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubscriptionPlan extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'interval', 'term_days', 'price_minor', 'platform_fee_bps', 'currency', 'active'];

    protected function casts(): array
    {
        return ['interval' => PlanInterval::class, 'price_minor' => 'integer', 'platform_fee_bps' => 'integer', 'term_days' => 'integer', 'active' => 'boolean'];
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }
}
