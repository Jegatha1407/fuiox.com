<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('credential_update_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('status', ['pending', 'accepted', 'rejected'])->default('pending');
            $table->text('reason')->nullable(); // user's reason for update
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('used_at')->nullable(); // when user actually updated
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('credential_update_requests');
    }
};