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
        Schema::table('messages', function (Blueprint $table) {
            $table->string('media_type')->nullable()->after('message'); // image, audio, video, document, voice
            $table->string('media_url')->nullable()->after('media_type'); // URL to the media file
            $table->string('media_id')->nullable()->after('media_url'); // WhatsApp media ID
            $table->string('media_caption')->nullable()->after('media_id'); // Caption for media
            $table->string('media_filename')->nullable()->after('media_caption'); // Original filename
            $table->string('media_mime_type')->nullable()->after('media_filename'); // MIME type
            $table->integer('media_size')->nullable()->after('media_mime_type'); // File size in bytes
            $table->string('meta_message_id')->nullable()->after('media_size'); // WhatsApp message ID
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropColumn([
                'media_type',
                'media_url',
                'media_id',
                'media_caption',
                'media_filename',
                'media_mime_type',
                'media_size',
                'meta_message_id'
            ]);
        });
    }
};
