<?php

namespace App\Models;

use Database\Factories\AuditLogFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @use HasFactory<AuditLogFactory>
 */
class AuditLog extends Model
{
    /** @use HasFactory<AuditLogFactory> */
    use HasFactory;

    public $timestamps = false;

    protected $fillable = ['actor_type', 'actor_id', 'event', 'auditable_type', 'auditable_id', 'before', 'after', 'correlation_id', 'created_at'];

    protected function casts(): array
    {
        return ['before' => 'array', 'after' => 'array', 'created_at' => 'datetime'];
    }

    /** @return MorphTo<Model, $this> */
    public function auditable(): MorphTo
    {
        return $this->morphTo();
    }

    /** @return MorphTo<Model, $this> */
    public function actor(): MorphTo
    {
        return $this->morphTo();
    }
}
