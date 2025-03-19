<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\UserController;

Route::prefix('auth')->group(function () {
    Route::post('/register', [UserController::class, 'store']);
    Route::post('/login', [UserController::class, 'login']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [UserController::class, 'logout']);
    Route::get('/users-list', [UserController::class, 'index']);
    Route::get('/users-get/{user}', [UserController::class, 'show']);
    Route::put('/users-update/{user}', [UserController::class, 'update']);
    Route::delete('/users-delete/{user}', [UserController::class, 'destroy']);
    Route::get('/current-user', function (Request $request) {
        return response()->json([
            'success' => true,
            'message' => 'Mevcut kullanıcı başarıyla getirildi',
            'data' => $request->user(),
        ]);
    });
});