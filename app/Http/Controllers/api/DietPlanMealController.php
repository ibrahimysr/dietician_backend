<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Dietitian;
use App\Models\DietPlanMeal;
use App\Models\DietPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class DietPlanMealController extends Controller
{
    public function index()
    {
        try {
            $meals = DietPlanMeal::with('dietPlan')->get();
            return response()->json([
                'success' => true,
                'message' => 'Diyet planı öğünleri başarıyla getirildi',
                'data' => $meals,
            ]);
        } catch (\Exception $e) {
            Log::error('Diyet planı öğünleri listeleme hatası', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Diyet planı öğünleri getirilemedi: ' . $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $rules = [
                'diet_plan_id' => 'required|exists:diet_plans,id',
                'day_number' => 'required|integer|min:1',
                'meal_type' => 'required|in:breakfast,lunch,dinner,snack',
                'description' => 'required|string',
                'calories' => 'required|integer|min:0',
                'protein' => 'required|numeric|min:0',
                'fat' => 'required|numeric|min:0',
                'carbs' => 'required|numeric|min:0',
                'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', 
            ];

            $messages = [
                'diet_plan_id.required' => 'Diyet planı ID alanı zorunludur',
                'diet_plan_id.exists' => 'Geçerli bir diyet planı ID giriniz',
                'day_number.required' => 'Gün numarası alanı zorunludur',
                'day_number.integer' => 'Gün numarası bir tamsayı olmalıdır',
                'meal_type.required' => 'Öğün tipi alanı zorunludur',
                'meal_type.in' => 'Öğün tipi yalnızca "breakfast", "lunch", "dinner" veya "snack" olabilir',
                'description.required' => 'Açıklama alanı zorunludur',
                'calories.required' => 'Kalori alanı zorunludur',
                'calories.integer' => 'Kalori bir tamsayı olmalıdır',
                'protein.required' => 'Protein alanı zorunludur',
                'protein.numeric' => 'Protein sayısal bir değer olmalıdır',
                'fat.required' => 'Yağ alanı zorunludur',
                'fat.numeric' => 'Yağ sayısal bir değer olmalıdır',
                'carbs.required' => 'Karbonhidrat alanı zorunludur',
                'carbs.numeric' => 'Karbonhidrat sayısal bir değer olmalıdır',
                'photo.image' => 'Yüklenen dosya bir resim olmalıdır',
                'photo.mimes' => 'Resim yalnızca jpeg, png, jpg veya gif formatında olabilir',
                'photo.max' => 'Resim boyutu 2MB’ı geçemez',
            ];

            $request->validate($rules, $messages);

            $dietPlan = DietPlan::findOrFail($request->diet_plan_id);

            $currentUser = auth()->user();
            $dietitian = Dietitian::where('user_id', $currentUser->id)->first();

            if (!$dietitian) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mevcut kullanıcı bir diyetisyen değil',
                    'data' => null,
                ], 403);
            }

            if ($dietPlan->dietitian_id != $dietitian->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bu diyet planı sizin diyet planınız değil',
                    'data' => null,
                ], 403);
            }

            $photoUrl = null;
            if ($request->hasFile('photo')) {
                $photoPath = $request->file('photo')->store('diet_plan_meals', 'public');
                $photoUrl = Storage::disk('public')->url($photoPath);
            }

            $meal = DietPlanMeal::create([
                'diet_plan_id' => $request->diet_plan_id,
                'day_number' => $request->day_number,
                'meal_type' => $request->meal_type,
                'description' => $request->description,
                'calories' => $request->calories,
                'protein' => $request->protein,
                'fat' => $request->fat,
                'carbs' => $request->carbs,
                'photo_url' => $photoUrl,
            ]);

            Log::info('Diyet planı öğünü oluşturuldu', ['meal_id' => $meal->id]);

            return response()->json([
                'success' => true,
                'message' => 'Diyet planı öğünü başarıyla oluşturuldu',
                'data' => $meal->load('dietPlan'),
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Giriş bilgileri geçersiz',
                'data' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Diyet planı öğünü oluşturma hatası', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Diyet planı öğünü oluşturulamadı: ' . $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    public function show(DietPlanMeal $dietPlanMeal)
    {
        try {
            $dietPlanMeal->load('dietPlan');
            return response()->json([
                'success' => true,
                'message' => 'Diyet planı öğünü başarıyla getirildi',
                'data' => $dietPlanMeal,
            ]);
        } catch (\Exception $e) {
            Log::error('Diyet planı öğünü getirme hatası', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Diyet planı öğünü getirilemedi: ' . $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    public function update(Request $request, DietPlanMeal $dietPlanMeal)
    {
        try {
            $rules = [
                'diet_plan_id' => 'sometimes|required|exists:diet_plans,id',
                'day_number' => 'sometimes|required|integer|min:1',
                'meal_type' => 'sometimes|required|in:breakfast,lunch,dinner,snack',
                'description' => 'sometimes|required|string',
                'calories' => 'sometimes|required|integer|min:0',
                'protein' => 'sometimes|required|numeric|min:0',
                'fat' => 'sometimes|required|numeric|min:0',
                'carbs' => 'sometimes|required|numeric|min:0',
                'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', 
            ];

            $messages = [
                'diet_plan_id.required' => 'Diyet planı ID alanı zorunludur',
                'diet_plan_id.exists' => 'Geçerli bir diyet planı ID giriniz',
                'day_number.required' => 'Gün numarası alanı zorunludur',
                'day_number.integer' => 'Gün numarası bir tamsayı olmalıdır',
                'meal_type.required' => 'Öğün tipi alanı zorunludur',
                'meal_type.in' => 'Öğün tipi yalnızca "breakfast", "lunch", "dinner" veya "snack" olabilir',
                'description.required' => 'Açıklama alanı zorunludur',
                'calories.required' => 'Kalori alanı zorunludur',
                'calories.integer' => 'Kalori bir tamsayı olmalıdır',
                'protein.required' => 'Protein alanı zorunludur',
                'protein.numeric' => 'Protein sayısal bir değer olmalıdır',
                'fat.required' => 'Yağ alanı zorunludur',
                'fat.numeric' => 'Yağ sayısal bir değer olmalıdır',
                'carbs.required' => 'Karbonhidrat alanı zorunludur',
                'carbs.numeric' => 'Karbonhidrat sayısal bir değer olmalıdır',
                'photo.image' => 'Yüklenen dosya bir resim olmalıdır',
                'photo.mimes' => 'Resim yalnızca jpeg, png, jpg veya gif formatında olabilir',
                'photo.max' => 'Resim boyutu 2MB’ı geçemez',
            ];

            $request->validate($rules, $messages);

            if ($request->has('diet_plan_id') && $request->diet_plan_id != $dietPlanMeal->diet_plan_id) {
                $dietPlan = DietPlan::findOrFail($request->diet_plan_id);

                $currentUser = auth()->user();
                $dietitian = Dietitian::where('user_id', $currentUser->id)->first();

                if (!$dietitian) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Mevcut kullanıcı bir diyetisyen değil',
                        'data' => null,
                    ], 403);
                }

                if ($dietPlan->dietitian_id != $dietitian->id) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Bu diyet planı sizin diyet planınız değil',
                        'data' => null,
                    ], 403);
                }
            }

            $photoUrl = $dietPlanMeal->photo_url;
            if ($request->hasFile('photo')) {
                if ($photoUrl) {
                    $oldPhotoPath = str_replace(Storage::disk('public')->url(''), '', $photoUrl);
                    Storage::disk('public')->delete($oldPhotoPath);
                }
                $photoPath = $request->file('photo')->store('diet_plan_meals', 'public');
                $photoUrl = Storage::disk('public')->url($photoPath);
            }

            $dietPlanMeal->update([
                'diet_plan_id' => $request->diet_plan_id ?? $dietPlanMeal->diet_plan_id,
                'day_number' => $request->day_number ?? $dietPlanMeal->day_number,
                'meal_type' => $request->meal_type ?? $dietPlanMeal->meal_type,
                'description' => $request->description ?? $dietPlanMeal->description,
                'calories' => $request->calories ?? $dietPlanMeal->calories,
                'protein' => $request->protein ?? $dietPlanMeal->protein,
                'fat' => $request->fat ?? $dietPlanMeal->fat,
                'carbs' => $request->carbs ?? $dietPlanMeal->carbs,
                'photo_url' => $photoUrl,
            ]);

            Log::info('Diyet planı öğünü güncellendi', ['meal_id' => $dietPlanMeal->id]);

            return response()->json([
                'success' => true,
                'message' => 'Diyet planı öğünü başarıyla güncellendi',
                'data' => $dietPlanMeal->load('dietPlan'),
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Güncelleme bilgileri geçersiz',
                'data' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Diyet planı öğünü güncelleme hatası', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Diyet planı öğünü güncellenemedi: ' . $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    public function destroy(DietPlanMeal $dietPlanMeal)
    {
        try {
            if ($dietPlanMeal->photo_url) {
                $photoPath = str_replace(Storage::disk('public')->url(''), '', $dietPlanMeal->photo_url);
                Storage::disk('public')->delete($photoPath);
            }

            $dietPlanMeal->delete();

            Log::info('Diyet planı öğünü silindi', ['meal_id' => $dietPlanMeal->id]);

            return response()->json([
                'success' => true,
                'message' => 'Diyet planı öğünü başarıyla silindi',
                'data' => null,
            ], 204);
        } catch (\Exception $e) {
            Log::error('Diyet planı öğünü silme hatası', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Diyet planı öğünü silinemedi: ' . $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    public function getMealsByDietPlan($dietPlanId)
    {
        try {
            $meals = DietPlanMeal::where('diet_plan_id', $dietPlanId)->get();

            if ($meals->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bu diyet planı için öğün bulunamadı',
                    'data' => null,
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Diyet planının öğünleri başarıyla getirildi',
                'data' => $meals,
            ]);
        } catch (\Exception $e) {
            Log::error('Diyet planının öğünleri getirme hatası', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Diyet planının öğünleri getirilemedi: ' . $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }
}