<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Template;
use App\Models\User;

class MessageLog extends Model
{
    protected $fillable = [
        'user_id',
        'template_id',
        'contact_phone',
        'status',
        'response',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function template()
    {
        return $this->belongsTo(Template::class);
    }
}
