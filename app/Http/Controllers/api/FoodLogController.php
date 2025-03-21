<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\FoodLog;
use App\Models\Food;
use App\Models\Client;
use App\Models\Dietitian;
use App\Models\DietPlan;
use App\Models\DietPlanMeal;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class FoodLogController extends Controller
{

    public function index()
    {
        try {
            $foodLogs = FoodLog::with('client.user', 'food')->get();
            return response()->json([
                'success' => true,
                'message' => 'Besin logları başarıyla getirildi',
                'data' => $foodLogs,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Besin logları getirilemedi: ' . $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $rules = [
                'client_id' => 'required|exists:clients,id',
                'date' => 'required|date',
                'meal_type' => 'required|in:breakfast,lunch,dinner,snack',
                'quantity' => 'required|numeric|min:0',
                'photo_url' => 'nullable|string|max:255',
                'logged_at' => 'required|date',
            ];

            if ($request->has('food_id')) {
                $rules['food_id'] = 'required|exists:foods,id';
            } else {
                $rules['food_description'] = 'required|string|max:255';
                $rules['calories'] = 'required|integer|min:0';
                $rules['protein'] = 'nullable|numeric|min:0';
                $rules['fat'] = 'nullable|numeric|min:0';
                $rules['carbs'] = 'nullable|numeric|min:0';
            }

            $messages = [
                'client_id.required' => 'Danışan ID alanı zorunludur',
                'client_id.exists' => 'Geçerli bir danışan ID giriniz',
                'food_id.exists' => 'Geçerli bir besin ID giriniz',
                'date.required' => 'Log tarihi alanı zorunludur',
                'date.date' => 'Geçerli bir tarih formatı giriniz',
                'meal_type.required' => 'Öğün tipi alanı zorunludur',
                'meal_type.in' => 'Öğün tipi yalnızca "breakfast", "lunch", "dinner" veya "snack" olabilir',
                'quantity.required' => 'Miktar alanı zorunludur',
                'quantity.numeric' => 'Miktar sayısal bir değer olmalıdır',
                'food_description.required' => 'Besin açıklaması alanı zorunludur (food_id belirtilmediğinde)',
                'calories.required' => 'Kalori alanı zorunludur (food_id belirtilmediğinde)',
                'calories.integer' => 'Kalori bir tamsayı olmalıdır',
            ];

            $request->validate($rules, $messages);

            $client = Client::findOrFail($request->client_id);

            $currentUser = auth()->user();

            if ($currentUser->id != $client->user_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sadece kendi adınıza besin logu oluşturabilirsiniz',
                    'data' => null,
                ], 403);
            }

            $foodData = [
                'client_id' => $request->client_id,
                'date' => $request->date,
                'meal_type' => $request->meal_type,
                'quantity' => $request->quantity,
                'photo_url' => $request->photo_url,
                'logged_at' => $request->logged_at,
            ];

            if ($request->has('food_id')) {
                $food = Food::findOrFail($request->food_id);
                $servingMultiplier = $request->quantity / $food->serving_size;
                $foodData['food_id'] = $food->id;
                $foodData['food_description'] = $food->name;
                $foodData['calories'] = round($food->calories * $servingMultiplier);
                $foodData['protein'] = $food->protein ? round($food->protein * $servingMultiplier, 2) : null;
                $foodData['fat'] = $food->fat ? round($food->fat * $servingMultiplier, 2) : null;
                $foodData['carbs'] = $food->carbs ? round($food->carbs * $servingMultiplier, 2) : null;
            } else {
                $foodData['food_description'] = $request->food_description;
                $foodData['calories'] = $request->calories;
                $foodData['protein'] = $request->protein;
                $foodData['fat'] = $request->fat;
                $foodData['carbs'] = $request->carbs;
            }

            $foodLog = FoodLog::create($foodData);


            return response()->json([
                'success' => true,
                'message' => 'Besin logu başarıyla oluşturuldu',
                'data' => $foodLog,
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Giriş bilgileri geçersiz',
                'data' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Besin logu oluşturulamadı: ' . $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }


    public function show(FoodLog $foodLog)
    {
        try {
            $client = Client::findOrFail($foodLog->client_id);

            $currentUser = auth()->user();
            $dietitian = Dietitian::where('user_id', $currentUser->id)->first();
            $isClient = $currentUser->id == $client->user_id;

            if (!$isClient && !$dietitian) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bu logu görüntüleme yetkiniz yok',
                    'data' => null,
                ], 403);
            }

            if ($dietitian && $client->dietitian_id != $dietitian->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bu danışan sizin danışanınız değil',
                    'data' => null,
                ], 403);
            }

            $foodLog->load('client.user', 'food');
            return response()->json([
                'success' => true,
                'message' => 'Besin logu başarıyla getirildi',
                'data' => $foodLog,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Besin logu getirilemedi: ' . $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    public function update(Request $request, FoodLog $foodLog)
    {
        try {
            $rules = [
                'client_id' => 'sometimes|required|exists:clients,id',
                'date' => 'sometimes|required|date',
                'meal_type' => 'sometimes|required|in:breakfast,lunch,dinner,snack',
                'quantity' => 'sometimes|required|numeric|min:0',
                'photo_url' => 'nullable|string|max:255',
                'logged_at' => 'sometimes|required|date',
            ];

            if ($request->has('food_id')) {
                $rules['food_id'] = 'required|exists:foods,id';
            } elseif (!$foodLog->food_id && !$request->has('food_id')) {
                $rules['food_description'] = 'sometimes|required|string|max:255';
                $rules['calories'] = 'sometimes|required|integer|min:0';
                $rules['protein'] = 'nullable|numeric|min:0';
                $rules['fat'] = 'nullable|numeric|min:0';
                $rules['carbs'] = 'nullable|numeric|min:0';
            }

            $messages = [
                'client_id.required' => 'Danışan ID alanı zorunludur',
                'client_id.exists' => 'Geçerli bir danışan ID giriniz',
                'food_id.exists' => 'Geçerli bir besin ID giriniz',
                'date.required' => 'Log tarihi alanı zorunludur',
                'date.date' => 'Geçerli bir tarih formatı giriniz',
                'meal_type.required' => 'Öğün tipi alanı zorunludur',
                'meal_type.in' => 'Öğün tipi yalnızca "breakfast", "lunch", "dinner" veya "snack" olabilir',
                'quantity.required' => 'Miktar alanı zorunludur',
                'quantity.numeric' => 'Miktar sayısal bir değer olmalıdır',
                'food_description.required' => 'Besin açıklaması alanı zorunludur (food_id belirtilmediğinde)',
                'calories.required' => 'Kalori alanı zorunludur (food_id belirtilmediğinde)',
                'calories.integer' => 'Kalori bir tamsayı olmalıdır',
            ];

            $request->validate($rules, $messages);

            $clientId = $request->client_id ?? $foodLog->client_id;
            $client = Client::findOrFail($clientId);

            $currentUser = auth()->user();

            if ($currentUser->id != $client->user_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sadece kendi besin loglarınızı güncelleyebilirsiniz',
                    'data' => null,
                ], 403);
            }

            if ($request->has('food_id') || ($foodLog->food_id && !$request->has('food_id'))) {
                $foodId = $request->food_id ?? $foodLog->food_id;
                $quantity = $request->quantity ?? $foodLog->quantity;

                $food = Food::findOrFail($foodId);
                $servingMultiplier = $quantity / $food->serving_size;
                $foodLog->food_id = $food->id;
                $foodLog->food_description = $food->name;
                $foodLog->calories = round($food->calories * $servingMultiplier);
                $foodLog->protein = $food->protein ? round($food->protein * $servingMultiplier, 2) : null;
                $foodLog->fat = $food->fat ? round($food->fat * $servingMultiplier, 2) : null;
                $foodLog->carbs = $food->carbs ? round($food->carbs * $servingMultiplier, 2) : null;
            } elseif ($request->has('food_description') || $request->has('calories')) {
                $foodLog->food_id = null;
                $foodLog->food_description = $request->food_description ?? $foodLog->food_description;
                $foodLog->calories = $request->calories ?? $foodLog->calories;
                $foodLog->protein = $request->has('protein') ? $request->protein : $foodLog->protein;
                $foodLog->fat = $request->has('fat') ? $request->fat : $foodLog->fat;
                $foodLog->carbs = $request->has('carbs') ? $request->carbs : $foodLog->carbs;
            }

            $foodLog->update($request->except(['calories', 'protein', 'fat', 'carbs', 'food_description']));


            return response()->json([
                'success' => true,
                'message' => 'Besin logu başarıyla güncellendi',
                'data' => $foodLog,
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Güncelleme bilgileri geçersiz',
                'data' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Besin logu güncellenemedi: ' . $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }


    public function destroy(FoodLog $foodLog)
    {
        try {
            $client = Client::findOrFail($foodLog->client_id);

            $currentUser = auth()->user();

            if ($currentUser->id != $client->user_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sadece kendi besin loglarınızı silebilirsiniz',
                    'data' => null,
                ], 403);
            }

            $foodLog->delete();


            return response()->json([
                'success' => true,
                'message' => 'Besin logu başarıyla silindi',
                'data' => null,
            ], 204);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Besin logu silinemedi: ' . $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    public function getFoodLogsByClient($clientId)
    {
        try {
            $client = Client::findOrFail($clientId);

            $currentUser = auth()->user();
            $dietitian = Dietitian::where('user_id', $currentUser->id)->first();
            $isClient = $currentUser->id == $client->user_id;

            if (!$isClient && !$dietitian) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bu logları görüntüleme yetkiniz yok',
                    'data' => null,
                ], 403);
            }

            if ($dietitian && $client->dietitian_id != $dietitian->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bu danışan sizin danışanınız değil',
                    'data' => null,
                ], 403);
            }

            $foodLogs = FoodLog::with('food')
                ->where('client_id', $clientId)
                ->get();

            if ($foodLogs->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bu danışan için besin logu bulunamadı',
                    'data' => null,
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Danışanın besin logları başarıyla getirildi',
                'data' => $foodLogs,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Danışanın besin logları getirilemedi: ' . $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    public function getFoodLogsByClientAndDate($clientId, $date)
    {
        try {
            $client = Client::findOrFail($clientId);

            $currentUser = auth()->user();
            $dietitian = Dietitian::where('user_id', $currentUser->id)->first();
            $isClient = $currentUser->id == $client->user_id;

            if (!$isClient && !$dietitian) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bu logları görüntüleme yetkiniz yok',
                    'data' => null,
                ], 403);
            }

            if ($dietitian && $client->dietitian_id != $dietitian->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bu danışan sizin danışanınız değil',
                    'data' => null,
                ], 403);
            }

            $foodLogs = FoodLog::with('food')
                ->where('client_id', $clientId)
                ->where('date', $date)
                ->get();

            if ($foodLogs->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bu danışan için belirtilen tarihte besin logu bulunamadı',
                    'data' => null,
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Danışanın belirtilen tarihteki besin logları başarıyla getirildi',
                'data' => $foodLogs,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Danışanın tarihe göre besin logları getirilemedi: ' . $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }
    public function compareDietPlanWithFoodLogs($clientId, $date)
    {
        try {
            $client = Client::findOrFail($clientId);

            $currentUser = auth()->user();
            $dietitian = Dietitian::where('user_id', $currentUser->id)->first();
            $isClient = $currentUser->id == $client->user_id;

            if (!$isClient && !$dietitian) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bu karşılaştırmayı görüntüleme yetkiniz yok',
                    'data' => null,
                ], 403);
            }

            if ($dietitian && $client->dietitian_id != $dietitian->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bu danışan sizin danışanınız değil',
                    'data' => null,
                ], 403);
            }

            $dietPlan = DietPlan::where('client_id', $clientId)
                ->where('start_date', '<=', $date)
                ->where(function ($query) use ($date) {
                    $query->where('end_date', '>=', $date)
                        ->orWhereNull('end_date')
                        ->orWhere('is_ongoing', true);
                })
                ->first();

            if (!$dietPlan) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bu tarihte danışan için aktif bir diyet planı bulunamadı',
                    'data' => null,
                ], 404);
            }

            $startDate = \Carbon\Carbon::parse($dietPlan->start_date);
            $targetDate = \Carbon\Carbon::parse($date);
            $dayNumber = $targetDate->diffInDays($startDate) + 1;

            $dietPlanMeals = DietPlanMeal::where('diet_plan_id', $dietPlan->id)
                ->where('day_number', $dayNumber)
                ->get();

            $foodLogs = FoodLog::with('food')
                ->where('client_id', $clientId)
                ->where('date', $date)
                ->get();

            $comparison = [
                'date' => $date,
                'day_number' => $dayNumber,
                'meals' => [],
                'daily_totals' => [
                    'diet_plan' => [
                        'calories' => 0,
                        'protein' => 0,
                        'fat' => 0,
                        'carbs' => 0,
                    ],
                    'food_logs' => [
                        'calories' => 0,
                        'protein' => 0,
                        'fat' => 0,
                        'carbs' => 0,
                    ],
                    'difference' => [
                        'calories' => 0,
                        'protein' => 0,
                        'fat' => 0,
                        'carbs' => 0,
                    ],
                ],
            ];

            $mealTypes = ['breakfast', 'lunch', 'dinner', 'snack'];

            foreach ($mealTypes as $mealType) {
                $dietPlanMealData = $dietPlanMeals->where('meal_type', $mealType);
                $dietPlanMealSummary = [
                    'calories' => 0,
                    'protein' => 0,
                    'fat' => 0,
                    'carbs' => 0,
                    'items' => [],
                ];

                foreach ($dietPlanMealData as $meal) {
                    $dietPlanMealSummary['calories'] += $meal->calories;
                    $dietPlanMealSummary['protein'] += $meal->protein;
                    $dietPlanMealSummary['fat'] += $meal->fat;
                    $dietPlanMealSummary['carbs'] += $meal->carbs;

                    $dietPlanMealSummary['items'][] = [
                        'description' => $meal->description,
                        'calories' => $meal->calories,
                        'protein' => $meal->protein,
                        'fat' => $meal->fat,
                        'carbs' => $meal->carbs,
                    ];
                }

                $comparison['daily_totals']['diet_plan']['calories'] += $dietPlanMealSummary['calories'];
                $comparison['daily_totals']['diet_plan']['protein'] += $dietPlanMealSummary['protein'];
                $comparison['daily_totals']['diet_plan']['fat'] += $dietPlanMealSummary['fat'];
                $comparison['daily_totals']['diet_plan']['carbs'] += $dietPlanMealSummary['carbs'];

                $foodLogData = $foodLogs->where('meal_type', $mealType);
                $foodLogSummary = [
                    'calories' => 0,
                    'protein' => 0,
                    'fat' => 0,
                    'carbs' => 0,
                    'items' => [],
                ];

                foreach ($foodLogData as $log) {
                    $foodLogSummary['calories'] += $log->calories ?? 0;
                    $foodLogSummary['protein'] += $log->protein ?? 0;
                    $foodLogSummary['fat'] += $log->fat ?? 0;
                    $foodLogSummary['carbs'] += $log->carbs ?? 0;

                    $foodLogSummary['items'][] = [
                        'food_description' => $log->food_description,
                        'quantity' => $log->quantity,
                        'calories' => $log->calories ?? 0,
                        'protein' => $log->protein ?? 0,
                        'fat' => $log->fat ?? 0,
                        'carbs' => $log->carbs ?? 0,
                    ];
                }

                $comparison['daily_totals']['food_logs']['calories'] += $foodLogSummary['calories'];
                $comparison['daily_totals']['food_logs']['protein'] += $foodLogSummary['protein'];
                $comparison['daily_totals']['food_logs']['fat'] += $foodLogSummary['fat'];
                $comparison['daily_totals']['food_logs']['carbs'] += $foodLogSummary['carbs'];

                $comparison['meals'][$mealType] = [
                    'diet_plan' => $dietPlanMealSummary,
                    'food_logs' => $foodLogSummary,
                    'difference' => [
                        'calories' => $foodLogSummary['calories'] - $dietPlanMealSummary['calories'],
                        'protein' => $foodLogSummary['protein'] - $dietPlanMealSummary['protein'],
                        'fat' => $foodLogSummary['fat'] - $dietPlanMealSummary['fat'],
                        'carbs' => $foodLogSummary['carbs'] - $dietPlanMealSummary['carbs'],
                    ],
                ];
            }

            $comparison['daily_totals']['difference']['calories'] =
                $comparison['daily_totals']['food_logs']['calories'] - $comparison['daily_totals']['diet_plan']['calories'];
            $comparison['daily_totals']['difference']['protein'] =
                $comparison['daily_totals']['food_logs']['protein'] - $comparison['daily_totals']['diet_plan']['protein'];
            $comparison['daily_totals']['difference']['fat'] =
                $comparison['daily_totals']['food_logs']['fat'] - $comparison['daily_totals']['diet_plan']['fat'];
            $comparison['daily_totals']['difference']['carbs'] =
                $comparison['daily_totals']['food_logs']['carbs'] - $comparison['daily_totals']['diet_plan']['carbs'];

            return response()->json([
                'success' => true,
                'message' => 'Diyet planı ile yedikleri başarıyla karşılaştırıldı',
                'data' => $comparison,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Karşılaştırma yapılamadı: ' . $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }
}