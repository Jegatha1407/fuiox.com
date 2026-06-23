<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('messages', 'status')) {
            Schema::table('messages', function (Blueprint $table) {
                $table->string('status')->nullable()->after('type');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('messages', 'status')) {
            Schema::table('messages', function (Blueprint $table) {
                $table->dropColumn('status');
            });
        }
    }
};
