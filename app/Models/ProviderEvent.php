<?php

namespace App\Models;

use Database\Factories\ProviderEventFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/** @use HasFactory<ProviderEventFactory> */
class ProviderEvent extends Model
{
    /** @use HasFactory<ProviderEventFactory> */
    use HasFactory;

    protected $fillable = ['provider', 'provider_reference', 'event_type', 'outcome', 'payload', 'occurred_at'];

    public $timestamps = true;

    protected function casts(): array
    {
        return ['payload' => 'array', 'occurred_at' => 'datetime'];
    }
}
