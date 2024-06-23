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
    // fungsi forgot-password
    public function forgotPassword(Request $request)
    {
        // Validasi request
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Data tidak lengkap'], 400);
        }

        // Verifikasi email
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => 'maaf email tidak terdaftar'], 400);
        }

        // Cek apakah user ditemukan
        dd($user);

        // Setel password default
        $defaultPassword = 'galeri2020';

        try {
            // Reset password dan simpan ke database
            $user->password = Hash::make($defaultPassword);
            $user->save();

            // Cek apakah password berhasil disimpan
            dd('Password berhasil disimpan');
        } catch (\Exception $e) {
            // Menangani kesalahan tanpa log
            if (strpos($e->getMessage(), 'SQLSTATE[HY000] [1049]') !== false) {
                return response()->json(['message' => 'SQLSTATE[HY000] [1049] Unknown database \'nama-database-salah\''], 500);
            } else {
                return response()->json(['message' => 'Terjadi kesalahan pada server: ' . $e->getMessage()], 500);
            }
        }

        return response()->json(['message' => 'Akun berhasil dilakukan Reset Password, Gunakan password galeri2020'], 200);
    }

    // fungsi current
    public function currentUser() {
        $user = auth()->user();

        if ($user) {
            return response()->json([
                'data' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ],
                'message' => 'Informasi pengguna saat ini',
            ], 200);
        } else {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
    }

}
