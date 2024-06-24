<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{
    // Fungsi filter
    public function filterUser(Request $request)
    {
        // Ambil semua query parameter untuk filter
        $filters = $request->only(['name', 'email', 'no_telephone', 'level']);

        // Query dasar
        $query = User::query();

        // Menambahkan kondisi filter ke query
        if (!empty($filters['name'])) {
            $query->where('name', 'like', '%' . $filters['name'] . '%');
        }

        if (!empty($filters['email'])) {
            $query->where('email', 'like', '%' . $filters['email'] . '%');
        }

        if (!empty($filters['no_telephone'])) {
            $query->where('no_telephone', 'like', '%' . $filters['no_telephone'] . '%');
        }

        if (!empty($filters['level'])) {
            $query->where('level', $filters['level']);
        }

        // Eksekusi query dan ambil hasilnya
        $users = $query->get();

        // Periksa jika data tidak ditemukan
        if ($users->isEmpty()) {
            return response()->json([
                'message' => 'Data pengguna tidak ditemukan.',
                'filters' => $filters
            ], 404); // Mengembalikan status HTTP 404
        }

        // Kembalikan hasil sebagai respons JSON
        return response()->json($users);
    }

    // Fungsi filter berdasarkan id pengguna
    public function filterUserById(Request $request)
    {
        // Ambil semua query parameter untuk filter termasuk 'id'
        $filters = $request->only(['id']);

        // Periksa jika 'id' tidak ada dalam request
        if (empty($filters['id'])) {
            return response()->json([
                'message' => 'ID pengguna harus disertakan dalam permintaan.',
                'filters' => $filters
            ], 400); // Mengembalikan status HTTP 400 (Bad Request)
        }

        // Konversi ID ke integer untuk memastikan query yang tepat
        $id = (int) $filters['id'];

        // Cari pengguna berdasarkan ID
        $user = User::find($id);

        // Periksa jika data tidak ditemukan
        if (!$user) {
            return response()->json([
                'message' => 'Pengguna dengan ID tersebut tidak ditemukan.',
                'filters' => $filters
            ], 404); // Mengembalikan status HTTP 404
        }

        // Kembalikan hasil sebagai respons JSON
        return response()->json($user);
    }
}
