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
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reporter_id')
                ->constrained('users')
                ->cascadeOnDelete();
            $table->foreignId('reported_id')
                ->constrained('users')
                ->cascadeOnDelete();
            $table->string('reason', 255)
                ->nullable();
            // Menyimpan beberapa baris riwayat obrolan terakhir dalam format JSON raw data
            $table->longText('evidence_snapshot');
            $table->enum('status', ['pending', 'reviewed', 'resolved'])
                ->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
