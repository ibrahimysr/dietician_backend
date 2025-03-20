<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Food;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class FoodController extends Controller
{
    public function index()
    {
        try {
            $foods = Food::with('creator')->get();
            return response()->json([
                'success' => true,
                'message' => 'Besinler başarıyla getirildi',
                'data' => $foods,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Besinler getirilemedi: ' . $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $request->validate(
                [
                    'name' => 'required|string|max:255',
                    'category' => 'required|string|max:255',
                    'serving_size' => 'required|numeric|min:0',
                    'calories' => 'required|integer|min:0',
                    'protein' => 'required|numeric|min:0',
                    'fat' => 'required|numeric|min:0',
                    'carbs' => 'required|numeric|min:0',
                    'fiber' => 'nullable|numeric|min:0',
                    'sugar' => 'nullable|numeric|min:0',
                    'is_custom' => 'boolean',
                ],
                [
                    'name.required' => 'Besin adı alanı zorunludur',
                    'category.required' => 'Kategori alanı zorunludur',
                    'serving_size.required' => 'Porsiyon boyutu alanı zorunludur',
                    'serving_size.numeric' => 'Porsiyon boyutu sayısal bir değer olmalıdır',
                    'calories.required' => 'Kalori alanı zorunludur',
                    'calories.integer' => 'Kalori bir tamsayı olmalıdır',
                    'protein.required' => 'Protein alanı zorunludur',
                    'protein.numeric' => 'Protein sayısal bir değer olmalıdır',
                    'fat.required' => 'Yağ alanı zorunludur',
                    'fat.numeric' => 'Yağ sayısal bir değer olmalıdır',
                    'carbs.required' => 'Karbonhidrat alanı zorunludur',
                    'carbs.numeric' => 'Karbonhidrat sayısal bir değer olmalıdır',
                    'fiber.numeric' => 'Lif sayısal bir değer olmalıdır',
                    'sugar.numeric' => 'Şeker sayısal bir değer olmalıdır',
                ]
            );

            $food = Food::create([
                'name' => $request->name,
                'category' => $request->category,
                'serving_size' => $request->serving_size,
                'calories' => $request->calories,
                'protein' => $request->protein,
                'fat' => $request->fat,
                'carbs' => $request->carbs,
                'fiber' => $request->fiber,
                'sugar' => $request->sugar,
                'is_custom' => $request->is_custom ?? false,
                'created_by' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Besin başarıyla oluşturuldu',
                'data' => $food,
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
                'message' => 'Besin oluşturulamadı: ' . $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    public function show(Food $food)
    {
        try {
            $food->load('creator');
            return response()->json([
                'succes' => true,
                'message' => 'Besin başarıyla getirildi',
                'data' => $food
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Besin getirilemedi: ' . $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    public function update(Request $request, Food $food)
    {
        try {
            if ($food->created_by != auth()->id() && $food->is_custom) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bu besini güncelleme yetkiniz yok',
                    'data' => null,
                ], 403);
            }

            $request->validate([
                'name' => 'sometimes|required|string|max:255',
                'category' => 'sometimes|required|string|max:255',
                'serving_size' => 'sometimes|required|numeric|min:0',
                'calories' => 'sometimes|required|integer|min:0',
                'protein' => 'sometimes|required|numeric|min:0',
                'fat' => 'sometimes|required|numeric|min:0',
                'carbs' => 'sometimes|required|numeric|min:0',
                'fiber' => 'nullable|numeric|min:0',
                'sugar' => 'nullable|numeric|min:0',
                'is_custom' => 'boolean',
            ], [
                'name.required' => 'Besin adı alanı zorunludur',
                'category.required' => 'Kategori alanı zorunludur',
                'serving_size.required' => 'Porsiyon boyutu alanı zorunludur',
                'serving_size.numeric' => 'Porsiyon boyutu sayısal bir değer olmalıdır',
                'calories.required' => 'Kalori alanı zorunludur',
                'calories.integer' => 'Kalori bir tamsayı olmalıdır',
                'protein.required' => 'Protein alanı zorunludur',
                'protein.numeric' => 'Protein sayısal bir değer olmalıdır',
                'fat.required' => 'Yağ alanı zorunludur',
                'fat.numeric' => 'Yağ sayısal bir değer olmalıdır',
                'carbs.required' => 'Karbonhidrat alanı zorunludur',
                'carbs.numeric' => 'Karbonhidrat sayısal bir değer olmalıdır',
                'fiber.numeric' => 'Lif sayısal bir değer olmalıdır',
                'sugar.numeric' => 'Şeker sayısal bir değer olmalıdır',
            ]);

            $food->update($request->all());


            return response()->json([
                'success' => true,
                'message' => 'Besin başarıyla güncellendi',
                'data' => $food,
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
                'message' => 'Besin güncellenemedi: ' . $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    public function destroy(Food $food)
    {
        try {
            if ($food->created_by != auth()->id() && $food->is_custom) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bu besini silme yetkiniz yok',
                    'data' => null,
                ], 403);
            }

            $food->delete();


            return response()->json([
                'success' => true,
                'message' => 'Besin başarıyla silindi',
                'data' => null,
            ], 204);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Besin silinemedi: ' . $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    public function getCustomFoods()
    {
        try {
            $foods = Food::where('is_custom', true)
                ->where('created_by', auth()->id())
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Özel besinler başarıyla getirildi',
                'data' => $foods,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Özel besinler getirilemedi: ' . $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    
    public function getGeneralFoods()
    {
        try {
            $foods = Food::where('is_custom', false)->get();

            return response()->json([
                'success' => true,
                'message' => 'Genel besinler başarıyla getirildi',
                'data' => $foods,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Genel besinler getirilemedi: ' . $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }
}
