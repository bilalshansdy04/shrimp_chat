<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Profile;
use App\Models\Contact;
use App\Models\Room;
use App\Models\RoomParticipant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Buat 2 User Dummy
        $userA = User::create([
            'name' => 'Budi',
            'email' => 'budi@shrimp.com',
            'password' => 'password123', // Otomatis di-hash oleh model
            'privacy_level' => 'public',
            'is_profile_complete' => true
        ]);

        $userB = User::create([
            'name' => 'Siti',
            'email' => 'siti@shrimp.com',
            'password' => 'password123',
            'privacy_level' => 'public',
            'is_profile_complete' => true
        ]);

        // 2. Buat Profil Kelengkapan untuk mereka
        Profile::create([
            'user_id' => $userA->id,
            'full_name' => 'Budi Santoso',
            'birth_date' => '1998-01-01',
            'gender' => 'male',
        ]);

        Profile::create([
            'user_id' => $userB->id,
            'full_name' => 'Siti Aminah',
            'birth_date' => '1999-02-02',
            'gender' => 'female',
        ]);

        // 3. Buat Relasi Pertemanan (Budi dan Siti sudah berteman)
        Contact::create([
            'user_id' => $userA->id,
            'contact_id' => $userB->id,
            'status' => 'accepted'
        ]);

        // 4. Buatkan Kamar Obrolan (Room) Personal untuk Budi dan Siti
        $room = Room::create([
            'type' => 'personal'
        ]);

        // 5. Masukkan Budi dan Siti ke dalam Kamar Obrolan tersebut
        RoomParticipant::create([
            'room_id' => $room->id,
            'user_id' => $userA->id,
            'role' => 'member',
            'joined_at' => now(),
        ]);

        RoomParticipant::create([
            'room_id' => $room->id,
            'user_id' => $userB->id,
            'role' => 'member',
            'joined_at' => now(),
        ]);

        $this->command->info('Database berhasil disuntikkan dengan data Dummy: Budi & Siti siap chatting!');
    }
}