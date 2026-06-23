<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Template extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'body',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
