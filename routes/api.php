<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\ClientController;
use App\Http\Controllers\API\DietitianController;


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


Route::middleware('auth:sanctum')->group(function () {
    Route::get('dietitians-list', [DietitianController::class, 'index']);
    Route::post('dietitians-add', [DietitianController::class, 'store']);
    Route::get('dietitians-get/{dietitian}', [DietitianController::class, 'show']);
    Route::put('dietitians-update/{dietitian}', [DietitianController::class, 'update']);
    Route::delete('dietitians-delete/{dietitian}', [DietitianController::class, 'destroy']);
    
    Route::get('users/{userId}/dietitian', [DietitianController::class, 'getDietitianByUserId']);
    Route::put('dietitians/{dietitian}/toggle-status', [DietitianController::class, 'toggleActiveStatus']);
    Route::get('dietitians/{dietitian}/stats', [DietitianController::class, 'getDietitianStats']);
    Route::get('active-dietitians', [DietitianController::class, 'getActiveDietitians']);
});