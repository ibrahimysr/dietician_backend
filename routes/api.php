<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\ClientController;
use App\Http\Controllers\API\DietitianController;
use App\Http\Controllers\API\DietPlanController;
use App\Http\Controllers\API\DietPlanMealController;
use App\Http\Controllers\API\FoodController;

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

Route::middleware('auth:sanctum')->group(function () {
    Route::get('diet-plans-list', [DietPlanController::class, 'index']);
    Route::post('diet-plans-add', [DietPlanController::class, 'store']);
    Route::get('diet-plans-get/{dietPlan}', [DietPlanController::class, 'show']);
    Route::put('diet-plans-update/{dietPlan}', [DietPlanController::class, 'update']);
    Route::delete('diet-plans-delete/{dietPlan}', [DietPlanController::class, 'destroy']);
    Route::get('dietitians/{dietitianId}/diet-plans', [DietPlanController::class, 'getDietPlansByDietitian']);
    Route::get('clients/{clientId}/diet-plans', [DietPlanController::class, 'getDietPlansByClient']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('diet-plan-meals-list', [DietPlanMealController::class, 'index']);
    Route::post('diet-plan-meals-add', [DietPlanMealController::class, 'store']);
    Route::get('diet-plan-meals-get/{dietPlanMeal}', [DietPlanMealController::class, 'show']);
    Route::put('diet-plan-meals-update/{dietPlanMeal}', [DietPlanMealController::class, 'update']);
    Route::delete('diet-plan-meals-delete/{dietPlanMeal}', [DietPlanMealController::class, 'destroy']);
    Route::get('diet-plans/{dietPlanId}/meals', [DietPlanMealController::class, 'getMealsByDietPlan']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('foods-list', [FoodController::class, 'index']);
    Route::post('foods-add', [FoodController::class, 'store']);
    Route::get('foods-get/{food}', [FoodController::class, 'show']);
    Route::put('foods-update/{food}', [FoodController::class, 'update']);
    Route::delete('foods-delete/{food}', [FoodController::class, 'destroy']);
    Route::get('foods-custom', [FoodController::class, 'getCustomFoods']);
    Route::get('foods-general', [FoodController::class, 'getGeneralFoods']);
});