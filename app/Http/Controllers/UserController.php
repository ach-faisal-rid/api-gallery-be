<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
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

    // Fungsi untuk memformat nomor telepon
    private function format_nomor(string $nomor): string
    {
        // Cek apakah nomor telepon dimulai dengan '0'
        if (substr($nomor, 0, 1) === '0') {
            // Mengganti '0' dengan '62'
            $nomor = '62' . substr($nomor, 1);
        }
        return $nomor;
    }

    // Fungsi untuk menyimpan data baru
    public function store_buat_data_baru(Request $request)
    {
        // Validasi request dari client
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'no_telpon' => 'required|string|max:15|unique:users,no_telpon',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8',
            'level' => 'required|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Data tidak lengkap', 'errors' => $validator->errors()], 400);
        }

        // Format nomor telepon
        $format_no_telpon = $this->format_nomor($request->input('no_telpon'));

        // Hash password
        $password_hashed = Hash::make($request->input('password'));

        // Buat data pengguna baru
        $user = new User();
        $user->name = $request->input('name');
        $user->no_telpon = $format_no_telpon;
        $user->email = $request->input('email');
        $user->password = $password_hashed;
        $user->level = $request->input('level');

        if ($user->save()) {
            return response()->json([
                'message' => 'Berhasil',
                'data' => $user
            ], 200);
        } else {
            return response()->json(['message' => 'Terjadi kesalahan saat menyimpan data'], 500);
        }
    }

    // Fungsi untuk memperbarui data pengguna
    public function update_merubah_data(Request $request, $id)
    {
        // Log request data
        Log::info('Update request received', ['request_data' => $request->all(), 'user_id' => $id]);

        // Validasi request dari client
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'no_telpon' => 'required|string|max:15',
            'email' => 'required|string|email|max:255',
            'level' => 'required|string|max:50',
        ]);

        if ($validator->fails()) {
            Log::error('Validation failed', ['errors' => $validator->errors()]);
            return response()->json(['message' => 'Data tidak lengkap', 'errors' => $validator->errors()], 400);
        }

        // Cari pengguna berdasarkan ID
        $user = User::find($id);
        if (!$user) {
            Log::warning("User not found", ['user_id' => $id]);
            return response()->json(['message' => "Data pengguna dengan ID $id tidak ditemukan"], 404);
        }

        // Cek apakah nama sudah digunakan oleh pengguna lain
        $cek_nama_sama = User::where('name', $request->input('name'))->where('id', '!=', $id)->exists();
        if ($cek_nama_sama) {
            Log::warning('Name already used', ['name' => $request->input('name')]);
            return response()->json(['message' => 'Nama sudah digunakan oleh pengguna lain'], 400);
        }

        // Cek apakah email sudah digunakan oleh pengguna lain
        $cek_email_sama = User::where('email', $request->input('email'))->where('id', '!=', $id)->exists();
        if ($cek_email_sama) {
            Log::warning('Email already used', ['email' => $request->input('email')]);
            return response()->json(['message' => 'Email sudah digunakan oleh pengguna lain'], 400);
        }

        // Format nomor telepon
        $format_no_telpon = $this->format_nomor($request->input('no_telpon'));

        // Perbarui data pengguna
        $user->name = $request->input('name');
        $user->no_telpon = $format_no_telpon;
        $user->email = $request->input('email');
        $user->level = $request->input('level');

        // Simpan perubahan
        if ($user->save()) {
            Log::info('User updated successfully', ['user' => $user]);
            return response()->json([
                'message' => 'Data berhasil diperbarui',
                'data' => $user
            ], 200);
        } else {
            Log::error('Failed to update user', ['user_id' => $id]);
            return response()->json(['message' => 'Proses update data gagal'], 500);
        }
    }


    // Fungsi untuk menghapus data pengguna
    public function delete_menghapus_data($id)
    {
        // Cari pengguna berdasarkan ID
        $user = User::find($id);

        // Jika pengguna tidak ditemukan
        if (!$user) {
            return response()->json(['message' => "Data pengguna dengan ID $id tidak ditemukan"], 404);
        }

        // Lakukan penghapusan data
        if ($user->delete()) {
            return response()->json(['message' => 'Data berhasil dihapus'], 200);
        } else {
            return response()->json(['message' => 'Proses menghapus data gagal'], 500);
        }
    }
}
