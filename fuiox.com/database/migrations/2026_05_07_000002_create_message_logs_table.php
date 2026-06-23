<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('message_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('template_id')->nullable();
            $table->string('contact_phone');
            $table->string('status');
            $table->text('response')->nullable();
            $table->timestamps();

            $table->index('user_id');
            $table->index('template_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('message_logs');
    }
};
