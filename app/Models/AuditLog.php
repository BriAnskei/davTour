<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    public $timestamps = false; // We use created_at only (useCurrent)

    protected $fillable = [
        'action',
        'auditable_type',
        'auditable_id',
        'auditable_name',
        'old_values',
        'new_values',
        'ip_address',
        'created_at'
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'created_at' => 'datetime',
    ];

    public function auditable()
    {
        return $this->morphTo();
    }
}
