<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');

            $table->string('role')->default('user');
            $table->string('organisation')->nullable();

            $table->string('mobile')->nullable();
            $table->text('address')->nullable();

            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('country')->nullable();
            $table->string('pincode')->nullable();

            $table->string('phone_number_id')->nullable();
            $table->text('access_token')->nullable();
            $table->string('business_account_id')->nullable();

            $table->boolean('is_online')->default(0);
            $table->string('bot_status')->default('on');
            $table->timestamp('last_logout_at')->nullable();

            $table->timestamp('last_seen')->nullable();

            $table->string('otp')->nullable();
            $table->string('otp_code')->nullable();

            $table->timestamp('otp_expires_at')->nullable();
            $table->timestamp('trial_ends_at')->nullable();

            $table->boolean('is_blocked')->default(0);
            $table->boolean('free_trial_enabled')->default(1);
            $table->boolean('free_trial_used')->default(0);

            $table->boolean('is_verified')->default(false);
            $table->boolean('is_active')->default(true);

            $table->string('api_key')->nullable();

            $table->unsignedBigInteger('parent_user_id')->nullable();

            $table->string('team_role')->default('owner');
            $table->boolean('is_active')->default(1);

            $table->text('permissions')->nullable();

            $table->rememberToken();
            $table->timestamps();

            $table->foreign('parent_user_id')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
