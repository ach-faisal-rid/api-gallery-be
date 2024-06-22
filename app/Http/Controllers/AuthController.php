<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use App\Models\User;


class AuthController extends Controller
{
    // fungsi login
    public function login(Request $request) {
        // Validasi request
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => 'data tidak lengkap'], 400);
        }

        // Verifikasi email
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['error' => 'login gagal, cek email'], 400);
        }

        // Verifikasi password
        if (!Hash::check($request->password, $user->password)) {
            return response()->json(['error' => 'login gagal, password'], 400);
        }

        // Membuat token
        try {
            $token = $user->createToken('auth_token')->plainTextToken;
        } catch (\Exception $e) {
            return response()->json(['error' => 'token tidak berhasil dibuat'], 500);
        }

        // Response data user dengan token
        return response()->json([
            'success' => true,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'no_telpon' => $user->no_telpon,
                'level' => $user->level,
                'tgl_buat' =>date('Y-m-d H:i:s', strtotime($user->tgl_buat)),
                'tgl_update' =>date('Y-m-d H:i:s', strtotime($user->tgl_update)),
            ],
            'message' => 'login berhasil',
            'token' => $token,
        ], 200);
    }
}
