<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use App\Models\Dietitian;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class SubscriptionPlanController extends Controller
{

    public function index()
    {
        try {
            $subscriptionPlans = SubscriptionPlan::with('dietitian.user')->get();
            return response()->json([
                'success' => true,
                'message' => 'Abonelik planları başarıyla getirildi',
                'data' => $subscriptionPlans,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Abonelik planları getirilemedi: ' . $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }


    public function store(Request $request)
    {
        try {
            $rules = [
                'dietitian_id' => 'required|exists:dietitians,id',
                'name' => 'required|string|max:255',
                'description' => 'required|string',
                'duration' => 'required|integer|min:1',
                'price' => 'required|numeric|min:0',
                'features' => 'required|array|min:1',
                'features.*' => 'string',
                'status' => 'required|in:active,inactive',
            ];

            $messages = [
                'dietitian_id.required' => 'Diyetisyen ID alanı zorunludur',
                'dietitian_id.exists' => 'Geçerli bir diyetisyen ID giriniz',
                'name.required' => 'Plan adı alanı zorunludur',
                'name.string' => 'Plan adı bir metin olmalıdır',
                'description.required' => 'Açıklama alanı zorunludur',
                'duration.required' => 'Süre alanı zorunludur',
                'duration.integer' => 'Süre bir tamsayı olmalıdır',
                'price.required' => 'Fiyat alanı zorunludur',
                'price.numeric' => 'Fiyat sayısal bir değer olmalıdır',
                'features.required' => 'Özellikler alanı zorunludur',
                'features.array' => 'Özellikler bir dizi olmalıdır',
                'status.required' => 'Durum alanı zorunludur',
                'status.in' => 'Durum yalnızca "active" veya "inactive" olabilir',
            ];

            $request->validate($rules, $messages);

            $currentUser = auth()->user();
            $dietitian = Dietitian::where('user_id', $currentUser->id)->first();

            if (!$dietitian || $dietitian->id != $request->dietitian_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sadece kendi adınıza abonelik planı oluşturabilirsiniz',
                    'data' => null,
                ], 403);
            }

            $subscriptionPlan = SubscriptionPlan::create([
                'dietitian_id' => $request->dietitian_id,
                'name' => $request->name,
                'description' => $request->description,
                'duration' => $request->duration,
                'price' => $request->price,
                'features' => $request->features,
                'status' => $request->status,
            ]);


            return response()->json([
                'success' => true,
                'message' => 'Abonelik planı başarıyla oluşturuldu',
                'data' => $subscriptionPlan,
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
                'message' => 'Abonelik planı oluşturulamadı: ' . $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }


    public function show(SubscriptionPlan $subscriptionPlan)
    {
        try {
            $subscriptionPlan->load('dietitian.user');
            return response()->json([
                'success' => true,
                'message' => 'Abonelik planı başarıyla getirildi',
                'data' => $subscriptionPlan,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Abonelik planı getirilemedi: ' . $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }


    public function update(Request $request, SubscriptionPlan $subscriptionPlan)
    {
        try {
            $rules = [
                'dietitian_id' => 'sometimes|required|exists:dietitians,id',
                'name' => 'sometimes|required|string|max:255',
                'description' => 'sometimes|required|string',
                'duration' => 'sometimes|required|integer|min:1',
                'price' => 'sometimes|required|numeric|min:0',
                'features' => 'sometimes|required|array|min:1',
                'features.*' => 'string',
                'status' => 'sometimes|required|in:active,inactive',
            ];

            $messages = [
                'dietitian_id.required' => 'Diyetisyen ID alanı zorunludur',
                'dietitian_id.exists' => 'Geçerli bir diyetisyen ID giriniz',
                'name.required' => 'Plan adı alanı zorunludur',
                'name.string' => 'Plan adı bir metin olmalıdır',
                'description.required' => 'Açıklama alanı zorunludur',
                'duration.required' => 'Süre alanı zorunludur',
                'duration.integer' => 'Süre bir tamsayı olmalıdır',
                'price.required' => 'Fiyat alanı zorunludur',
                'price.numeric' => 'Fiyat sayısal bir değer olmalıdır',
                'features.required' => 'Özellikler alanı zorunludur',
                'features.array' => 'Özellikler bir dizi olmalıdır',
                'status.required' => 'Durum alanı zorunludur',
                'status.in' => 'Durum yalnızca "active" veya "inactive" olabilir',
            ];

            $request->validate($rules, $messages);

            $currentUser = auth()->user();
            $dietitian = Dietitian::where('user_id', $currentUser->id)->first();

            $dietitianId = $request->dietitian_id ?? $subscriptionPlan->dietitian_id;
            if (!$dietitian || $dietitian->id != $dietitianId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sadece kendi abonelik planlarınızı güncelleyebilirsiniz',
                    'data' => null,
                ], 403);
            }

            $subscriptionPlan->update($request->all());


            return response()->json([
                'success' => true,
                'message' => 'Abonelik planı başarıyla güncellendi',
                'data' => $subscriptionPlan,
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
                'message' => 'Abonelik planı güncellenemedi: ' . $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    public function destroy(SubscriptionPlan $subscriptionPlan)
    {
        try {
            $currentUser = auth()->user();
            $dietitian = Dietitian::where('user_id', $currentUser->id)->first();

            if (!$dietitian || $dietitian->id != $subscriptionPlan->dietitian_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sadece kendi abonelik planlarınızı silebilirsiniz',
                    'data' => null,
                ], 403);
            }

            $subscriptionPlan->delete();


            return response()->json([
                'success' => true,
                'message' => 'Abonelik planı başarıyla silindi',
                'data' => null,
            ], 204);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Abonelik planı silinemedi: ' . $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }


    public function getSubscriptionPlansByDietitian($dietitianId)
    {
        try {
            $dietitian = Dietitian::findOrFail($dietitianId);

            $subscriptionPlans = SubscriptionPlan::where('dietitian_id', $dietitianId)
                ->get();

            if ($subscriptionPlans->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bu diyetisyen için abonelik planı bulunamadı',
                    'data' => null,
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Diyetisyenin abonelik planları başarıyla getirildi',
                'data' => $subscriptionPlans,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Diyetisyenin abonelik planları getirilemedi: ' . $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

}