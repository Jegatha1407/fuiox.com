<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CredentialUpdateRequest extends Model
{
    protected $fillable = ['user_id', 'status', 'reason', 'accepted_at', 'used_at'];

    protected $casts = [
        'accepted_at' => 'datetime',
        'used_at'     => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isAccepted(): bool
    {
        return $this->status === 'accepted' && is_null($this->used_at);
    }
}