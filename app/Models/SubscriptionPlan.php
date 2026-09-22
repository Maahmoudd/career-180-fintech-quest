<?php

namespace App\Models;

use App\Enums\PlanInterval;
use Database\Factories\SubscriptionPlanFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $name
 * @property PlanInterval $interval
 * @property int $term_days
 * @property int $price_minor
 * @property int $platform_fee_bps
 * @property string $currency
 * @property bool $active
 *
 * @use HasFactory<SubscriptionPlanFactory>
 */
class SubscriptionPlan extends Model
{
    /** @use HasFactory<SubscriptionPlanFactory> */
    use HasFactory;

    protected $fillable = ['name', 'interval', 'term_days', 'price_minor', 'platform_fee_bps', 'currency', 'active'];

    protected function casts(): array
    {
        return ['interval' => PlanInterval::class, 'price_minor' => 'integer', 'platform_fee_bps' => 'integer', 'term_days' => 'integer', 'active' => 'boolean'];
    }

    /** @return HasMany<Subscription, $this> */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }
}
