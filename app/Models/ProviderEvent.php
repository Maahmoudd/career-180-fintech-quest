<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

 class ProviderEvent extends Model
{
    use HasFactory;

    protected $fillable = ['provider', 'provider_reference', 'event_type', 'outcome', 'payload', 'occurred_at'];

    public $timestamps = true;

    protected function casts(): array
    {
        return ['payload' => 'array', 'occurred_at' => 'datetime'];
    }
}
