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
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')
                ->constrained('rooms')
                ->onDelete('cascade');
            $table->foreignId('sender_id')
                ->constrained('users')
                ->onDelete('cascade');
            $table->text('content')
                ->nullable();
            // Self-Referencing untuk Reply
            $table->foreignId('reply_to_id')
                ->nullable()
                ->constrained('messages')
                ->onDelete('set null');
            // Referensi ID pesan asli untuk fitur Forward tanpa duplikasi media
            $table->unsignedBigInteger('forwarded_msg_id')
                ->nullable();
            //Alamat berkas di MinIO/S3 Storage
            $table->string('media_url', 255)
                ->nullable();
            $table->string('thumbnail', 255)
                ->nullable();
            $table->enum('media_type', ['image', 'video', 'document', 'audio', 'file'])
                ->nullable();
            //True jika pesan ditarik untuk semua orang (Delete for Everyone)  
            $table->boolean('is_deleted')
                ->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
