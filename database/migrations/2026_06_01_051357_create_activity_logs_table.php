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
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            // Target user pemilik log 
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();
            // Contoh: ‘MESSAGE_DELETED’, ‘ROLE_UPDATED’, ‘ROOM_ARCHIVED’
            $table->string('action', 55);
            // Menyimpan metadata perubahan seperti room_id, message_id, atau status baru
            $table->json('payload');
            $table->timestamp('created_at')->userCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
