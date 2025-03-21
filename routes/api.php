<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\ClientController;
use App\Http\Controllers\API\DietitianController;
use App\Http\Controllers\API\DietPlanController;
use App\Http\Controllers\API\DietPlanMealController;
use App\Http\Controllers\API\FoodController;
use App\Http\Controllers\API\FoodLogController;
use App\Http\Controllers\API\SubscriptionPlanController;
use App\Http\Controllers\API\SubscriptionController;
use App\Http\Controllers\API\PaymentController;
use App\Http\Controllers\API\ProgressController;
use App\Http\Controllers\API\GoalController;


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

Route::middleware('auth:sanctum')->group(function () {
    Route::get('food-logs-list', [FoodLogController::class, 'index']);
    Route::post('food-logs-add', [FoodLogController::class, 'store']);
    Route::get('food-logs-get/{foodLog}', [FoodLogController::class, 'show']);
    Route::put('food-logs-update/{foodLog}', [FoodLogController::class, 'update']);
    Route::delete('food-logs-delete/{foodLog}', [FoodLogController::class, 'destroy']);
    Route::get('clients/{clientId}/food-logs', [FoodLogController::class, 'getFoodLogsByClient']);
    Route::get('clients/{clientId}/food-logs/{date}', [FoodLogController::class, 'getFoodLogsByClientAndDate']);
    Route::get('clients/{clientId}/compare-diet-plan/{date}', [FoodLogController::class, 'compareDietPlanWithFoodLogs']);
}); 

Route::middleware('auth:sanctum')->group(function () {
    Route::get('subscription-plans-list', [SubscriptionPlanController::class, 'index']);
    Route::post('subscription-plans-add', [SubscriptionPlanController::class, 'store']);
    Route::get('subscription-plans-get/{subscriptionPlan}', [SubscriptionPlanController::class, 'show']);
    Route::put('subscription-plans-update/{subscriptionPlan}', [SubscriptionPlanController::class, 'update']);
    Route::delete('subscription-plans-delete/{subscriptionPlan}', [SubscriptionPlanController::class, 'destroy']);
    Route::get('dietitians/{dietitianId}/subscription-plans', [SubscriptionPlanController::class, 'getSubscriptionPlansByDietitian']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('subscriptions-list', [SubscriptionController::class, 'index']);
    Route::post('subscriptions-add', [SubscriptionController::class, 'store']);
    Route::get('subscriptions-get/{subscription}', [SubscriptionController::class, 'show']);
    Route::put('subscriptions-update/{subscription}', [SubscriptionController::class, 'update']);
    Route::delete('subscriptions-delete/{subscription}', [SubscriptionController::class, 'destroy']);
    Route::get('clients/{clientId}/subscriptions', [SubscriptionController::class, 'getSubscriptionsByClient']);
    Route::get('dietitians/{dietitianId}/subscriptions', [SubscriptionController::class, 'getSubscriptionsByDietitian']);
});


Route::middleware('auth:sanctum')->group(function () {
    Route::get('payments-list', [PaymentController::class, 'index']);
    Route::post('payments-add', [PaymentController::class, 'store']);
    Route::get('payments-get/{payment}', [PaymentController::class, 'show']);
    Route::put('payments-update/{payment}', [PaymentController::class, 'update']);
    Route::delete('payments-delete/{payment}', [PaymentController::class, 'destroy']);
    Route::get('clients/{clientId}/payments', [PaymentController::class, 'getPaymentsByClient']);
    Route::get('subscriptions/{subscriptionId}/payments', [PaymentController::class, 'getPaymentsBySubscription']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('progress-list', [ProgressController::class, 'index']);
    Route::post('progress-add', [ProgressController::class, 'store']);
    Route::get('progress-get/{progress}', [ProgressController::class, 'show']);
    Route::put('progress-update/{progress}', [ProgressController::class, 'update']);
    Route::delete('progress-delete/{progress}', [ProgressController::class, 'destroy']);
    Route::get('clients/{clientId}/progress', [ProgressController::class, 'getProgressByClient']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('goals-list', [GoalController::class, 'index']);
    Route::post('goals-add', [GoalController::class, 'store']);
    Route::get('goals-get/{goal}', [GoalController::class, 'show']);
    Route::put('goals-update/{goal}', [GoalController::class, 'update']);
    Route::delete('goals-delete/{goal}', [GoalController::class, 'destroy']);
    Route::get('clients/{clientId}/goals', [GoalController::class, 'getGoalsByClient']);
    Route::get('dietitians/{dietitianId}/goals', [GoalController::class, 'getGoalsByDietitian']);
});