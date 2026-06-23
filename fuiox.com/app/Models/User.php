<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'organisation',
        'mobile',
        'address',
        'city',
        'state',
        'country',
        'pincode',
        'phone_number_id',
        'access_token',
        'business_account_id',
        'is_online',
        'bot_status',
        'last_seen',
        'otp',
        'otp_code',
        'trial_ends_at',
        'is_blocked',
        'free_trial_enabled',
        'api_key',
        'otp_expires_at',
        'is_verified',
        'parent_user_id',
        'team_role',
        'is_active',
        'permissions',
        'is_app_employee',
    ];

    protected $casts = [
        'is_online'      => 'boolean',
        'is_verified'    => 'boolean',
        'last_seen'      => 'datetime',
        'otp_expires_at' => 'datetime',
    ];

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }
}
