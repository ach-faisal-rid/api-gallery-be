<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\GalleryController;
use App\Http\Controllers\UserController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

// route login
Route::post('/login', [AuthController::class, 'login']);

// route forgot password
Route::post('/forgot-password',[AuthController::class, 'forgotPassword']);

Route::middleware('auth:sanctum')->group(function () {

    // route current
    Route::get('/current',[AuthController::class, 'current']);
    // route untuk users
    Route::get('/users', [UserController::class, 'index_ambil_data']);
    // route untuk users id
    Route::get('/users/{id}', [UserController::class, 'show_detail_data']);
    // route tambah user
    Route::post('/users', [UserController::class, 'store_buat_data_baru']);
    // route update
    Route::put('/users/{id}', [UserController::class, 'update_merubah_data']);
    // route delete
    Route::delete('/users/{id}', [UserController::class, 'delete_menghapus_data']);

    // route filter gallery
    Route::get('/gallery', [GalleryController::class, 'index_ambil_data']);
    // route fillter gallery id
    Route::get('/gallery/{id}', [GalleryController::class, 'show_detail_data']);
    // route tambah gallery
    Route::post('/gallery', [GalleryController::class, 'store_buat_data_baru']);
    // route update gallery name
    Route::put('/gallery/{id}', [GalleryController::class, 'update_merubah_data']);
    // route update image gallery
    Route::post('/gallery/update-image/{id}', [GalleryController::class, 'update_image']);
    // route hapus gallery
    Route::delete('/gallery/{id}', [GalleryController::class, 'delete_menghapus_data']);
});
