<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Profile;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "name" => "required|string|max:50|unique:users,name",
            "email" => "required|string|email|max:100|unique:users,email",
            "password" => "required|string|min:8|confirmed",
        ]);

        if ($validator->fails()) {
            return response()->json(
                [
                    "message" => false,
                    "errors" => $validator->errors(),
                ],
                422,
            );
        }

        $user = User::create([
            "name" => $request->name,
            "email" => $request->email,
            "password" => $request->password,
            "privacy_level" => "public",
            "is_profile_complete" => false,
        ]);

        $token = auth("api")->login($user);

        return response()->json(
            [
                "success" => true,
                "message" =>
                    "Registersi Berhasil. Mohon untuk lengkapi profil anda.",
                "token" => $token,
                "data" => [
                    "name" => $user->name,
                    "is_profile_complete" => $user->is_profile_complete,
                ],
            ],
            201,
        );
    }

    public function onboarding(Request $request)
    {
        $user = auth("api")->user();

        $validator = Validator::make($request->all(), [
            "full_name" => "required|string|max:100",
            "birth_date" => "required|date",
            "gender" => "required|in:male,female",
            "privacy_level" => "required|in:public,private,restricted",
            "avatar_url" => "nullable|url",
        ]);

        if ($validator->fails()) {
            return response()->json(
                [
                    "success" => false,
                    "errors" => $validator->errors(),
                ],
                422,
            );
        }

        Profile::updateOrCreate(
            ["user_id" => $user->id],
            [
                "full_name" => $request->full_name,
                "birth_date" => $request->birth_date,
                "gender" => $request->gender,
                "avatar_url" => $request->avatar_url,
            ],
        );

        $user->update([
            "privacy_level" => $request->privacy_level,
            "is_profile_complete" => true,
        ]);

        return response()->json(
            [
                "success" => true,
                "message" =>
                    "Profil berhasil disimpan. Akses dashboard dibuka.",
                "data" => [
                    "name" => $user->name,
                    "full_name" => $request->full_name,
                    "privacy_level" => $user->privacy_level,
                    "is_profile_complete" => $user->is_profile_complete,
                ],
            ],
            200,
        );
    }
}
