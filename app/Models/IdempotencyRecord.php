<?php

namespace App\Models;

use Database\Factories\IdempotencyRecordFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @use HasFactory<IdempotencyRecordFactory>
 */
class IdempotencyRecord extends Model
{
    /** @use HasFactory<IdempotencyRecordFactory> */
    use HasFactory;

    protected $fillable = ['scope', 'key', 'request_hash', 'status', 'result_type', 'result_id', 'response'];

    protected function casts(): array
    {
        return ['response' => 'array'];
    }

    /** @return MorphTo<Model, $this> */
    public function result(): MorphTo
    {
        return $this->morphTo();
    }
}
