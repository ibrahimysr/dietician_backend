<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Recipe;
use App\Models\Dietitian;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class RecipeController extends Controller
{
    
    public function index(Request $request)
    {
        try {
            $query = Recipe::with(['dietitian.user']);

            $currentUser = auth()->user();
            $dietitian = Dietitian::where('user_id', $currentUser->id)->first();

            if ($dietitian) {
                $query->where(function ($q) use ($dietitian) {
                    $q->where('is_public', true)
                      ->orWhere('dietitian_id', $dietitian->id);
                });
            } else {
                $query->where('is_public', true);
            }

            if ($request->has('tags')) {
                $tags = explode(',', $request->tags);
                foreach ($tags as $tag) {
                    $query->where('tags', 'like', '%' . trim($tag) . '%');
                }
            }

            $recipes = $query->get();

            return response()->json([
                'success' => true,
                'message' => 'Tarifler başarıyla getirildi',
                'data' => $recipes,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Tarifler getirilemedi: ' . $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $rules = [
                'dietitian_id' => 'required|exists:dietitians,id',
                'title' => 'required|string|max:255',
                'description' => 'required|string',
                'ingredients' => 'required|array',
                'instructions' => 'required|string',
                'prep_time' => 'required|integer|min:0',
                'cook_time' => 'required|integer|min:0',
                'servings' => 'required|integer|min:1',
                'calories' => 'required|integer|min:0',
                'protein' => 'required|numeric|min:0',
                'fat' => 'required|numeric|min:0',
                'carbs' => 'required|numeric|min:0',
                'tags' => 'nullable|string',
                'photo_url' => 'nullable|string|max:255',
                'is_public' => 'required|boolean',
            ];

            $messages = [
                'dietitian_id.required' => 'Diyetisyen ID alanı zorunludur',
                'dietitian_id.exists' => 'Geçerli bir diyetisyen ID giriniz',
                'title.required' => 'Başlık alanı zorunludur',
                'title.max' => 'Başlık 255 karakterden uzun olamaz',
                'description.required' => 'Açıklama alanı zorunludur',
                'ingredients.required' => 'Malzemeler alanı zorunludur',
                'ingredients.array' => 'Malzemeler bir dizi olmalıdır',
                'instructions.required' => 'Talimatlar alanı zorunludur',
                'prep_time.required' => 'Hazırlık süresi alanı zorunludur',
                'prep_time.integer' => 'Hazırlık süresi bir tamsayı olmalıdır',
                'cook_time.required' => 'Pişirme süresi alanı zorunludur',
                'cook_time.integer' => 'Pişirme süresi bir tamsayı olmalıdır',
                'servings.required' => 'Porsiyon sayısı alanı zorunludur',
                'servings.integer' => 'Porsiyon sayısı bir tamsayı olmalıdır',
                'calories.required' => 'Kalori alanı zorunludur',
                'calories.integer' => 'Kalori bir tamsayı olmalıdır',
                'protein.required' => 'Protein alanı zorunludur',
                'protein.numeric' => 'Protein sayısal bir değer olmalıdır',
                'fat.required' => 'Yağ alanı zorunludur',
                'fat.numeric' => 'Yağ sayısal bir değer olmalıdır',
                'carbs.required' => 'Karbonhidrat alanı zorunludur',
                'carbs.numeric' => 'Karbonhidrat sayısal bir değer olmalıdır',
                'photo_url.max' => 'Fotoğraf URL’si 255 karakterden uzun olamaz',
                'is_public.required' => 'Herkese açık mı alanı zorunludur',
                'is_public.boolean' => 'Herkese açık mı alanı true/false olmalıdır',
            ];

            $request->validate($rules, $messages);

            $currentUser = auth()->user();
            $dietitian = Dietitian::where('user_id', $currentUser->id)->first();

            if (!$dietitian) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tarif oluşturma yetkiniz yok, sadece diyetisyenler tarif oluşturabilir',
                    'data' => null,
                ], 403);
            }

            if ($dietitian->id != $request->dietitian_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sadece kendi adınıza tarif oluşturabilirsiniz',
                    'data' => null,
                ], 403);
            }

            $recipe = Recipe::create($request->all());


            return response()->json([
                'success' => true,
                'message' => 'Tarif başarıyla oluşturuldu',
                'data' => $recipe->load(['dietitian.user']),
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
                'message' => 'Tarif oluşturulamadı: ' . $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }


    public function show(Recipe $recipe)
    {
        try {
            $currentUser = auth()->user();
            $dietitian = Dietitian::where('user_id', $currentUser->id)->first();

            if (!$recipe->is_public && (!$dietitian || $dietitian->id != $recipe->dietitian_id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bu tarifi görüntüleme yetkiniz yok',
                    'data' => null,
                ], 403);
            }

            $recipe->load(['dietitian.user']);
            return response()->json([
                'success' => true,
                'message' => 'Tarif başarıyla getirildi',
                'data' => $recipe,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Tarif getirilemedi: ' . $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

  
    public function update(Request $request, Recipe $recipe)
    {
        try {
            $rules = [
                'title' => 'sometimes|required|string|max:255',
                'description' => 'sometimes|required|string',
                'ingredients' => 'sometimes|required|array',
                'instructions' => 'sometimes|required|string',
                'prep_time' => 'sometimes|required|integer|min:0',
                'cook_time' => 'sometimes|required|integer|min:0',
                'servings' => 'sometimes|required|integer|min:1',
                'calories' => 'sometimes|required|integer|min:0',
                'protein' => 'sometimes|required|numeric|min:0',
                'fat' => 'sometimes|required|numeric|min:0',
                'carbs' => 'sometimes|required|numeric|min:0',
                'tags' => 'nullable|string',
                'photo_url' => 'nullable|string|max:255',
                'is_public' => 'sometimes|required|boolean',
            ];

            $messages = [
                'title.required' => 'Başlık alanı zorunludur',
                'title.max' => 'Başlık 255 karakterden uzun olamaz',
                'description.required' => 'Açıklama alanı zorunludur',
                'ingredients.required' => 'Malzemeler alanı zorunludur',
                'ingredients.array' => 'Malzemeler bir dizi olmalıdır',
                'instructions.required' => 'Talimatlar alanı zorunludur',
                'prep_time.required' => 'Hazırlık süresi alanı zorunludur',
                'prep_time.integer' => 'Hazırlık süresi bir tamsayı olmalıdır',
                'cook_time.required' => 'Pişirme süresi alanı zorunludur',
                'cook_time.integer' => 'Pişirme süresi bir tamsayı olmalıdır',
                'servings.required' => 'Porsiyon sayısı alanı zorunludur',
                'servings.integer' => 'Porsiyon sayısı bir tamsayı olmalıdır',
                'calories.required' => 'Kalori alanı zorunludur',
                'calories.integer' => 'Kalori bir tamsayı olmalıdır',
                'protein.required' => 'Protein alanı zorunludur',
                'protein.numeric' => 'Protein sayısal bir değer olmalıdır',
                'fat.required' => 'Yağ alanı zorunludur',
                'fat.numeric' => 'Yağ sayısal bir değer olmalıdır',
                'carbs.required' => 'Karbonhidrat alanı zorunludur',
                'carbs.numeric' => 'Karbonhidrat sayısal bir değer olmalıdır',
                'photo_url.max' => 'Fotoğraf URL’si 255 karakterden uzun olamaz',
                'is_public.required' => 'Herkese açık mı alanı zorunludur',
                'is_public.boolean' => 'Herkese açık mı alanı true/false olmalıdır',
            ];

            $request->validate($rules, $messages);

            $currentUser = auth()->user();
            $dietitian = Dietitian::where('user_id', $currentUser->id)->first();

            if (!$dietitian || $dietitian->id != $recipe->dietitian_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bu tarifi güncelleme yetkiniz yok',
                    'data' => null,
                ], 403);
            }

            $recipe->update($request->all());


            return response()->json([
                'success' => true,
                'message' => 'Tarif başarıyla güncellendi',
                'data' => $recipe->load(['dietitian.user']),
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
                'message' => 'Tarif güncellenemedi: ' . $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

 
    public function destroy(Recipe $recipe)
    {
        try {
            $currentUser = auth()->user();
            $dietitian = Dietitian::where('user_id', $currentUser->id)->first();

            if (!$dietitian || $dietitian->id != $recipe->dietitian_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bu tarifi silme yetkiniz yok',
                    'data' => null,
                ], 403);
            }

            $recipe->delete();


            return response()->json([
                'success' => true,
                'message' => 'Tarif başarıyla silindi',
                'data' => null,
            ], 204);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Tarif silinemedi: ' . $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    
    public function getRecipesByDietitian($dietitianId)
    {
        try {
            $dietitian = Dietitian::findOrFail($dietitianId);

            $currentUser = auth()->user();
            $isDietitian = $currentUser->id == $dietitian->user_id;

            $query = Recipe::with(['dietitian.user'])
                ->where('dietitian_id', $dietitianId);

            if (!$isDietitian) {
                $query->where('is_public', true);
            }

            $recipes = $query->get();

            if ($recipes->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bu diyetisyen için tarif bulunamadı',
                    'data' => null,
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Diyetisyenin tarifleri başarıyla getirildi',
                'data' => $recipes,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Diyetisyenin tarifleri getirilemedi: ' . $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }
}