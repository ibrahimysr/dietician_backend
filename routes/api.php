<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\ClientController;


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


Route::middleware('auth:sanctum')->group(function () {
    Route::get('clients-list', [ClientController::class, 'index']);
    Route::post('clients-add', [ClientController::class, 'store']);
    Route::get('clients-get/{client}', [ClientController::class, 'show']);
    Route::put('clients-update/{client}', [ClientController::class, 'update']);
    Route::delete('clients-delete/{client}', [ClientController::class, 'destroy']);
    
    Route::get('dietitians/{dietitianId}/clients', [ClientController::class, 'getClientsByDietitian']);
    Route::get('users/{userId}/client', [ClientController::class, 'getClientByUserId']);
});