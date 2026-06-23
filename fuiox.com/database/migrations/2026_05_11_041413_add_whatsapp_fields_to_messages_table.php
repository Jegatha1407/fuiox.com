<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            if (!Schema::hasColumn('messages', 'reaction')) {
                $table->string('reaction')->nullable()->after('status');
            }
            if (!Schema::hasColumn('messages', 'reply_to')) {
                $table->text('reply_to')->nullable()->after('reaction');
            }
            if (!Schema::hasColumn('messages', 'reply_to_id')) {
                $table->string('reply_to_id')->nullable()->after('reply_to');
            }
            if (!Schema::hasColumn('messages', 'whatsapp_message_id')) {
                $table->string('whatsapp_message_id')->nullable()->after('reply_to_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropColumn(['reaction', 'reply_to', 'reply_to_id', 'whatsapp_message_id']);
        });
    }
};