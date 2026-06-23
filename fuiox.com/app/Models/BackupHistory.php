<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BackupHistory extends Model
{
    protected $fillable = [
    'file_name',
    'file_size',
    'backup_status',
    'mail_status',
];
}
