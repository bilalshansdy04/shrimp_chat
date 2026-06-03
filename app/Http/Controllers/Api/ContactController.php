<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use App\Models\Room;
use App\Models\RoomParticipant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ContactController extends Controller
{
    public function sendRequest(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'target_user_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json(
                ['success' => false, 'errors' => $validator->errors()],
                422,
            );
        }

        $requesterId = auth('api')->id();
        $targetId = $request->target_user_id;

        if ($requesterId == $targetId) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Tidak dapat menambahkan diri sendiri.',
                ],
                400,
            );
        }

        $existingContact = Contact::where(function ($query) use (
            $requesterId,
            $targetId,
        ) {
            $query
                ->where('user_id', $requesterId)
                ->where('contact_id', $targetId);
        })
            ->orWhere(function ($query) use ($requesterId, $targetId) {
                $query
                    ->where('user_id', $targetId)
                    ->where('contact_id', $requesterId);
            })
            ->first();

        if ($existingContact) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Relasi kontak sudah ada atau sedang menunggu persetujuan.',
                ],
                409,
            );
        }

        Contact::create([
            'user_id' => $requesterId,
            'contact_id' => $targetId,
            'status' => 'pending',
        ]);

        return response()->json(
            ['success' => true, 'message' => 'Permintaan pertemanan terkirim.'],
            201,
        );
    }

    public function pendingRequests()
    {
        $targetId = auth('api')->id();

        $requests = Contact::with('requester.profile')
            ->where('contact_id', $targetId)
            ->where('status', 'pending')
            ->get();

        return response()->json(['success' => true, 'data' => $requests], 200);
    }

    public function respondRequest(Request $request, $contact_id)
    {
        $validator = Validator::make($request->all(), [
            'action' => 'required|in:accept,reject',
        ]);

        if ($validator->fails()) {
            return response()->json(
                ['success' => false, 'errors' => $validator->errors()],
                422,
            );
        }

        $userId = auth('api')->id();

        $contact = Contact::where('id', $contact_id)
            ->where('contact_id', $userId)
            ->where('status', 'pending')
            ->first();

        if (! $contact) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Permintaan tidak ditemukan atau tidak valid.',
                ],
                404,
            );
        }

        if ($request->action === 'accept') {
            DB::beginTransaction();
            try {
                $contact->update(['status' => 'accepted']);

                $room = Room::create([
                    'type' => 'personal',
                ]);

                RoomParticipant::create([
                    'room_id' => $room->id,
                    'user_id' => $contact->user_id,
                    'role' => 'member',
                    'joined_at' => now(),
                ]);

                RoomParticipant::create([
                    'room_id' => $room->id,
                    'user_id' => $contact->contact_id,
                    'role' => 'member',
                    'joined_at' => now(),
                ]);

                DB::commit();
                $message =
                    'Permintaan pertemanan diterima. Ruang obrolan telah dibuat.';
            } catch (\Exception $e) {
                DB::rollBack();

                return response()->json(
                    [
                        'success' => false,
                        'message' => 'Terjadi kesalahan sistem saat membuat ruang obrolan.',
                        'error' => $e->getMessage(),
                    ],
                    500,
                );
            }
        } else {
            $contact->delete();
            $message = 'Permintaan pertemanan ditolak.';
        }

        return response()->json(
            ['success' => true, 'message' => $message],
            200,
        );
    }
}
