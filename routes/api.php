<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
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
    Route::get('/current',[AuthController::class, 'currentUser']);
    // route untuk users
    Route::get('/users', [UserController::class, 'filterUser']);
    // route untuk users
    Route::get('/users/{id}', [UserController::class, 'filterUserById']);

});
