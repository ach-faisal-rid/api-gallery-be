<?php

namespace App\Http\Controllers;

use App\Models\Gallery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class GalleryController extends Controller
{
    // fungsi filter all
    public function filterGallery(Request $request) {
        // Ambil semua query parameter untuk filter
        $filters = $request->only(['name']);

        // Query dasar
        $query = Gallery::query();

        // Menambahkan kondisi filter ke query
        if (!empty($filters['name'])) {
            $query->where('name', 'like', '%' . $filters['name'] . '%');
        }

        // Eksekusi query dan ambil hasilnya
        $gallery = $query->get();

        // Periksa jika data tidak ditemukan
        if ($gallery->isEmpty()) {
            return response()->json([
                'message' => 'Data pengguna tidak ditemukan.',
                'filters' => $filters
            ], 404); // Mengembalikan status HTTP 404
        }

        // Kembalikan hasil sebagai respons JSON
        return response()->json($gallery);
    }

    // fungsi filter gallery id
    public function filterGalleryById(Request $request) {
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
        $gallery = Gallery::find($id);

        // Periksa jika data tidak ditemukan
        if (!$gallery) {
            return response()->json([
                'message' => 'Pengguna dengan ID tersebut tidak ditemukan.',
                'filters' => $filters
            ], 404); // Mengembalikan status HTTP 404
        }

        // Kembalikan hasil sebagai respons JSON
        return response()->json($gallery);
    }

    // fungsi tambah data
    public function store_buat_data_baru(Request $request) {

        // Validasi request dari client
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'image' => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048', // 2MB
        ]);

        // Jika validasi gagal, kembalikan respons dengan pesan kesalahan
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Data tidak lengkap',
                'errors' => $validator->errors()
            ], 400);
        }

        // Cek apakah nama sudah digunakan oleh galeri lain
        $find_galeri = Gallery::where('name', $request->input('name'))->first();
        if ($find_galeri) {
            return response()->json([
                'message' => 'Nama sudah digunakan',
            ], 400);
        }

        // Ambil file yang diunggah
        $file = $request->file('image');
        // Generate nama file yang unik
        $fileName = uniqid() . '.' . $file->getClientOriginalExtension();
        // Tentukan path penyimpanan
        $path = $file->storeAs('public/uploads', $fileName);

        // Simpan data ke dalam database
        $galeri = new Gallery();
        $galeri->name = $request->input('name');
        $galeri->file = $path;
        $galeri->file_name = $file->getClientOriginalName();
        $galeri->file_type = $file->getClientOriginalExtension();
        $galeri->file_size = $file->getSize();
        $galeri->users_id = auth()->id(); // Mengambil ID pengguna yang sedang login

        // Simpan data dan kembalikan respons
        if ($galeri->save()) {
            return response()->json([
                'message' => 'Data berhasil disimpan',
                'data' => $galeri
            ], 200);
        } else {
            return response()->json([
                'message' => 'Terjadi kesalahan saat menyimpan data',
            ], 500);
        }
    }

    // Fungsi untuk memperbarui data nama galeri
     public function update_merubah_data(Request $request, $id)
     {
         // Validasi request dari client
         $validator = Validator::make($request->all(), [
             'name' => 'required|string|max:255',
         ]);

         // Jika validasi gagal, kembalikan respons dengan pesan kesalahan
         if ($validator->fails()) {
             return response()->json([
                 'message' => 'Data tidak lengkap',
                 'errors' => $validator->errors()
             ], 400);
         }

         // Cari galeri berdasarkan ID dan users_id
         $galeri = Gallery::where('id', $id)
                         ->where('users_id', auth()->id())
                         ->first();

         // Jika tidak ditemukan, kembalikan respons 404
         if (!$galeri) {
             return response()->json([
                 'message' => "Data dengan ID $id tidak ditemukan"
             ], 404);
         }

         // Cek apakah nama sudah digunakan oleh galeri lain
         $cek_nama_sama = Gallery::where('name', $request->input('name'))
                               ->where('id', '!=', $id)
                               ->exists();
         if ($cek_nama_sama) {
             return response()->json([
                 'message' => 'Nama sudah digunakan oleh galeri lain'
             ], 400);
         }

         // Perbarui data galeri
         $galeri->name = $request->input('name');

         // Simpan perubahan
         if ($galeri->save()) {
             return response()->json([
                 'message' => 'Data berhasil diperbarui',
                 'data' => $galeri
             ], 200);
         } else {
             return response()->json([
                 'message' => 'Proses update data gagal'
             ], 500);
         }
     }

     // Fungsi untuk memperbarui image
    public function update_image(Request $request, $galeri_id)
    {
        // Validasi request dari client
        $request->validate([
            'image' => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048', // Maksimum 2MB
        ]);

        // Cari data galeri berdasarkan ID dan users_id
        $galeri = Gallery::where('id', $galeri_id)
                        ->where('users_id', auth()->id())
                        ->first();

        // Jika data tidak ditemukan, kembalikan respons 404
        if (!$galeri) {
            return response()->json([
                "message" => "Data dengan ID $galeri_id tidak ditemukan",
            ], 404);
        }

        // Hapus file lama jika ada
        if ($galeri->file && Storage::exists($galeri->file)) {
            Storage::delete($galeri->file);
        }

        // Simpan file baru
        $file = $request->file('image');
        $path = $file->store('public/uploads');
        $galeri->file = $path;
        $galeri->file_name = $file->getClientOriginalName();
        $galeri->file_type = $file->getClientOriginalExtension();
        $galeri->file_size = $file->getSize();

        // Simpan perubahan
        if ($galeri->save()) {
            return response()->json([
                'message' => 'Image berhasil diperbarui',
                'data' => $galeri
            ], 200);
        } else {
            return response()->json([
                'message' => 'Proses update image gagal'
            ], 500);
        }
    }

    // Fungsi untuk menghapus data galeri
    public function delete_menghapus_data($id)
    {
        // Cari data galeri berdasarkan ID dan users_id
        $galeri = Gallery::where('id', $_POST)
                        ->where('users_id', auth()->id())
                        ->first();

        // Jika data tidak ditemukan, kembalikan respons 404
        if (!$galeri) {
            return response()->json([
                "message" => "Data dengan ID $id tidak ditemukan",
            ], 404);
        }

        // Hapus file lama jika ada
        if ($galeri->file && Storage::exists($galeri->file)) {
            Storage::delete($galeri->file);
        }

        // Hapus data dari database
        if ($galeri->delete()) {
            return response()->json([
                "message" => "Data berhasil dihapus",
            ], 200);
        } else {
            return response()->json([
                "message" => "Proses menghapus data gagal",
            ], 500);
        }
    }
}
