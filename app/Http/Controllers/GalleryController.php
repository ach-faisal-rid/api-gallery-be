<?php

namespace App\Http\Controllers;

use App\Models\Gallery;
use Illuminate\Http\Request;

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
}
