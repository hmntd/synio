<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Mentorship extends Model
{
    use BelongsToTenant;

    protected $table = 'mentorships';

    protected $fillable = [
        'tenant_id',
        'mentee_id',
        'mentor_id',
        'status',
    ];

    public function mentee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'mentee_id', 'id');
    }

    public function mentor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'mentor_id', 'id');
    }
}
