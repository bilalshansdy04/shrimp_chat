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
        Schema::create('group_invitations', function (Blueprint $table) {
            $table->id();
            // Harus room bertipe group
            $table->foreignId('room_id')
                ->constrained('rooms')
                ->onDelete('cascade');
            // The Inviter
            $table->foreignId('inviter_id')
                ->constrained('users')
                ->onDelete('cascade');
            // The Invitee
            $table->foreignId('invitee_id')
                ->constrained('users')
                ->onDelete('cascade');
            $table->enum('status', ['pending', 'accepted', 'rejected'])
                ->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('group_invitations');
    }
};
