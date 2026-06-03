<?php

namespace App\Http\Controllers\Api;

use App\Events\MessageSent;
use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MessageController extends Controller
{
    /**
     * Mengambil daftar ruang obrolan (Chat List) milik user yang login
     */
    public function getRooms()
    {
        $userId = auth('api')->id();

        // Cari semua room di mana user ini menjadi partisipan
        $rooms = Room::whereHas('participants', function ($query) use (
            $userId,
        ) {
            $query->where('user_id', $userId);
        })
            ->with(['participants.user.profile']) // Ambil juga data lawan bicaranya
            ->get();

        return response()->json(
            [
                'success' => true,
                'data' => $rooms,
            ],
            200,
        );
    }

    /**
     * Mengambil riwayat pesan di dalam satu ruang obrolan (Room)
     */
    public function getMessages($roomId)
    {
        $userId = auth('api')->id();

        // Pastikan user adalah anggota dari room ini sebelum bisa melihat pesan
        $isMember = Room::where('id', $roomId)
            ->whereHas('participants', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })
            ->exists();

        if (! $isMember) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Anda tidak memiliki akses ke ruangan ini.',
                ],
                403,
            );
        }

        // Ambil riwayat pesan, urutkan dari yang terlama ke terbaru
        $messages = Message::with(['sender.profile', 'replyTo'])
            ->where('room_id', $roomId)
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json(
            [
                'success' => true,
                'data' => $messages,
            ],
            200,
        );
    }

    /**
     * Mengirim pesan baru ke sebuah ruangan (Sesuai SRS-P06)
     */
    public function sendMessage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'room_id' => 'required|exists:rooms,id',
            'content' => 'required_without:media_url|string|nullable',
            'reply_to_id' => 'nullable|exists:messages,id',
        ]);

        if ($validator->fails()) {
            return response()->json(
                ['success' => false, 'errors' => $validator->errors()],
                422,
            );
        }

        $userId = auth('api')->id();
        $roomId = $request->room_id;

        // Validasi lagi, apakah pengirim benar-benar anggota room tersebut?
        $isMember = Room::where('id', $roomId)
            ->whereHas('participants', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })
            ->exists();

        if (! $isMember) {
            return response()->json(
                ['success' => false, 'message' => 'Akses ditolak.'],
                403,
            );
        }

        // Simpan pesan ke Database
        $message = Message::create([
            'room_id' => $roomId,
            'sender_id' => $userId,
            'content' => $request->content,
            'reply_to_id' => $request->reply_to_id,
            'is_deleted' => false,
        ]);

        // Muat relasi pengirim untuk dikembalikan sebagai response
        $message->load('sender.profile');

        broadcast(new MessageSent($message))->toOthers();

        return response()->json(
            [
                'success' => true,
                'message' => 'Pesan terkirim',
                'data' => $message,
            ],
            201,
        );
    }
}
