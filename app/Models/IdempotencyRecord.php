<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

 class IdempotencyRecord extends Model
{
    use HasFactory;

    protected $fillable = ['scope', 'key', 'request_hash', 'status', 'result_type', 'result_id', 'response'];

    protected function casts(): array
    {
        return ['response' => 'array'];
    }

    public function result(): MorphTo
    {
        return $this->morphTo();
    }
}
