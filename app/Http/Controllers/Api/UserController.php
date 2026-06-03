<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function search(Request $request)
    {
        $keyword = $request->query('q');

        // Jika tidak ada keyword, kembalikan array kosong untuk menghemat beban server
        if (empty($keyword)) {
            return response()->json([
                'success' => true,
                'data' => []
            ], 200);
        }

        $currentUserId = auth('api')->id();

        // Cari user berdasarkan nama atau email, kecualikan diri sendiri
        $users = User::with('profile') // Bawa data profil agar avatar/nama lengkap terlihat
            ->where('id', '!=', $currentUserId)
            ->where(function ($query) use ($keyword) {
                $query->where('name', 'LIKE', "%{$keyword}%")
                      ->orWhere('email', 'LIKE', "%{$keyword}%");
            })
            // Opsional: Implementasi filter privacy_level jika diperlukan nanti
            ->limit(20) // Batasi hasil pencarian agar response tidak bengkak
            ->get();

        return response()->json([
            'success' => true,
            'data' => $users
        ], 200);
    }
}
