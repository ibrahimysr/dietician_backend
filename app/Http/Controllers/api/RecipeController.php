<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Recipe;
use App\Models\Dietitian;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
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
            Log::error('Tarif listeleme hatası', ['error' => $e->getMessage()]);
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
                'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', 
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
                'photo.image' => 'Yüklenen dosya bir resim olmalıdır',
                'photo.mimes' => 'Resim yalnızca jpeg, png, jpg veya gif formatında olabilir',
                'photo.max' => 'Resim boyutu 2MB’ı geçemez',
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

            $photoUrl = null;
            if ($request->hasFile('photo')) {
                $photoPath = $request->file('photo')->store('recipe_photos', 'public');
                $photoUrl = Storage::disk('public')->url($photoPath);
            }

            $recipeData = [
                'dietitian_id' => $request->dietitian_id,
                'title' => $request->title,
                'description' => $request->description,
                'ingredients' => $request->ingredients,
                'instructions' => $request->instructions,
                'prep_time' => $request->prep_time,
                'cook_time' => $request->cook_time,
                'servings' => $request->servings,
                'calories' => $request->calories,
                'protein' => $request->protein,
                'fat' => $request->fat,
                'carbs' => $request->carbs,
                'tags' => $request->tags,
                'photo_url' => $photoUrl,
                'is_public' => $request->is_public,
            ];

            $recipe = Recipe::create($recipeData);

            Log::info('Tarif oluşturuldu', ['recipe_id' => $recipe->id]);

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
            Log::error('Tarif oluşturma hatası', ['error' => $e->getMessage()]);
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
            Log::error('Tarif getirme hatası', ['error' => $e->getMessage()]);
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
                'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', 
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
                'photo.image' => 'Yüklenen dosya bir resim olmalıdır',
                'photo.mimes' => 'Resim yalnızca jpeg, png, jpg veya gif formatında olabilir',
                'photo.max' => 'Resim boyutu 2MB’ı geçemez',
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

            $photoUrl = $recipe->photo_url;
            if ($request->hasFile('photo')) {
                if ($photoUrl) {
                    $oldPhotoPath = str_replace(Storage::disk('public')->url(''), '', $photoUrl);
                    Storage::disk('public')->delete($oldPhotoPath);
                }
                $photoPath = $request->file('photo')->store('recipe_photos', 'public');
                $photoUrl = Storage::disk('public')->url($photoPath);
            }

            $recipeData = [
                'title' => $request->title ?? $recipe->title,
                'description' => $request->description ?? $recipe->description,
                'ingredients' => $request->ingredients ?? $recipe->ingredients,
                'instructions' => $request->instructions ?? $recipe->instructions,
                'prep_time' => $request->prep_time ?? $recipe->prep_time,
                'cook_time' => $request->cook_time ?? $recipe->cook_time,
                'servings' => $request->servings ?? $recipe->servings,
                'calories' => $request->calories ?? $recipe->calories,
                'protein' => $request->protein ?? $recipe->protein,
                'fat' => $request->fat ?? $recipe->fat,
                'carbs' => $request->carbs ?? $recipe->carbs,
                'tags' => $request->tags ?? $recipe->tags,
                'photo_url' => $photoUrl,
                'is_public' => $request->is_public ?? $recipe->is_public,
            ];

            $recipe->update($recipeData);

            Log::info('Tarif güncellendi', ['recipe_id' => $recipe->id]);

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
            Log::error('Tarif güncelleme hatası', ['error' => $e->getMessage()]);
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

            if ($recipe->photo_url) {
                $photoPath = str_replace(Storage::disk('public')->url(''), '', $recipe->photo_url);
                Storage::disk('public')->delete($photoPath);
            }

            $recipe->delete();

            Log::info('Tarif silindi', ['recipe_id' => $recipe->id]);

            return response()->json([
                'success' => true,
                'message' => 'Tarif başarıyla silindi',
                'data' => null,
            ], 204);
        } catch (\Exception $e) {
            Log::error('Tarif silme hatası', ['error' => $e->getMessage()]);
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
            Log::error('Diyetisyenin tarifleri getirme hatası', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Diyetisyenin tarifleri getirilemedi: ' . $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }
}