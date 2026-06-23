<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $fillable = [
        'user_id',
        'wa_id',
        'message',
        'type',
        'status',
        'message_id',
        'read',
        'reaction',
        'reply_to',
        'reply_to_id',
        'whatsapp_message_id',
        'media_type',
        'media_url',
        'media_id',
        'media_caption',
        'media_filename',
        'media_mime_type',
        'media_size',
        'meta_message_id',
    ];
 protected $casts = [
        'read'       => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

   
