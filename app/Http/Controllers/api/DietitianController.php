<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Dietitian;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class DietitianController extends Controller
{
 
    public function index()
    {
        try {
            $dietitians = Dietitian::with('user')->get();
            return response()->json([
                'success' => true,
                'message' => 'Diyetisyenler başarıyla getirildi',
                'data' => $dietitians,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Diyetisyenler getirilemedi: ' . $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    
    public function store(Request $request)
    {
        try {
            $request->validate([
                'user_id' => 'required|exists:users,id',
                'specialty' => 'required|string|max:255',
                'bio' => 'required|string',
                'hourly_rate' => 'nullable|numeric|min:0',
                'experience_years' => 'nullable|integer|min:0',
                'is_active' => 'nullable|boolean',
            ], [
                'user_id.required' => 'Kullanıcı ID alanı zorunludur',
                'user_id.exists' => 'Geçerli bir kullanıcı ID giriniz',
                'specialty.required' => 'Uzmanlık alanı zorunludur',
                'specialty.max' => 'Uzmanlık en fazla 255 karakter olabilir',
                'bio.required' => 'Biyografi alanı zorunludur',
                'hourly_rate.numeric' => 'Saatlik ücret sayısal bir değer olmalıdır',
                'hourly_rate.min' => 'Saatlik ücret 0 veya daha büyük olmalıdır',
                'experience_years.integer' => 'Deneyim yılı tam sayı olmalıdır',
                'experience_years.min' => 'Deneyim yılı 0 veya daha büyük olmalıdır',
            ]);

            $existingDietitian = Dietitian::where('user_id', $request->user_id)->first();
            if ($existingDietitian) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bu kullanıcı zaten bir diyetisyen kaydına sahip',
                    'data' => null,
                ], 422);
            }

            $user = User::find($request->user_id);
            if ($user->role !== 'dietitian') {
                return response()->json([
                    'success' => false,
                    'message' => 'Kullanıcının rolü diyetisyen değil',
                    'data' => null,
                ], 422);
            }

            $dietitian = Dietitian::create($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Diyetisyen başarıyla kaydedildi',
                'data' => $dietitian,
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
                'message' => 'Diyetisyen kaydedilemedi: ' . $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    public function show(Dietitian $dietitian)
    {
        try {
            $dietitian->load('user', 'clients', 'subscriptionPlans');
            
            return response()->json([
                'success' => true,
                'message' => 'Diyetisyen başarıyla getirildi',
                'data' => $dietitian,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Diyetisyen getirilemedi: ' . $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

   
    public function update(Request $request, Dietitian $dietitian)
    {
        try {
            $request->validate([
                'specialty' => 'sometimes|required|string|max:255',
                'bio' => 'sometimes|required|string',
                'hourly_rate' => 'nullable|numeric|min:0',
                'experience_years' => 'nullable|integer|min:0',
                'is_active' => 'nullable|boolean',
            ], [
                'specialty.required' => 'Uzmanlık alanı zorunludur',
                'specialty.max' => 'Uzmanlık en fazla 255 karakter olabilir',
                'bio.required' => 'Biyografi alanı zorunludur',
                'hourly_rate.numeric' => 'Saatlik ücret sayısal bir değer olmalıdır',
                'hourly_rate.min' => 'Saatlik ücret 0 veya daha büyük olmalıdır',
                'experience_years.integer' => 'Deneyim yılı tam sayı olmalıdır',
                'experience_years.min' => 'Deneyim yılı 0 veya daha büyük olmalıdır',
            ]);

            $dietitian->update($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Diyetisyen başarıyla güncellendi',
                'data' => $dietitian,
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
                'message' => 'Diyetisyen güncellenemedi: ' . $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    
    public function destroy(Dietitian $dietitian)
    {
        try {
            $dietitian->delete();

            return response()->json([
                'success' => true,
                'message' => 'Diyetisyen başarıyla silindi',
                'data' => null,
            ], 204);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Diyetisyen silinemedi: ' . $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

   
    public function getDietitianByUserId($userId)
{
    try {
        $dietitian = Dietitian::with([
            'user', 
            'clients.user', // Include user information for each client
            'subscriptionPlans', 
            'recipes'
        ])
        ->where('user_id', $userId)
        ->first();

        if (!$dietitian) {
            return response()->json([
                'success' => false,
                'message' => 'Bu kullanıcı için diyetisyen kaydı bulunamadı',
                'data' => null,
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Diyetisyen başarıyla getirildi',
            'data' => $dietitian,
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Diyetisyen getirilemedi: ' . $e->getMessage(),
            'data' => null,
        ], 500);
    }
}

    public function toggleActiveStatus(Dietitian $dietitian)
    {
        try {
            $dietitian->is_active = !$dietitian->is_active;
            $dietitian->save();

            $statusText = $dietitian->is_active ? 'aktif' : 'pasif';

            return response()->json([
                'success' => true,
                'message' => "Diyetisyen durumu $statusText olarak güncellendi",
                'data' => $dietitian,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Diyetisyen durumu güncellenemedi: ' . $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    public function getActiveDietitians()
    {
        try {
            $dietitians = Dietitian::with('user')
                ->where('is_active', true)
                ->withCount('clients')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Aktif diyetisyenler başarıyla getirildi',
                'data' => $dietitians,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Aktif diyetisyenler getirilemedi: ' . $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    public function getDietitianStats(Dietitian $dietitian)
    {
        try {
            $stats = [
                'client_count' => $dietitian->clients()->count(),
                'active_diet_plans' => $dietitian->dietPlans()->whereHas('client')->count(),
                'recipe_count' => $dietitian->recipes()->count(),
                'subscription_plans' => $dietitian->subscriptionPlans()->count(),
                'active_subscriptions' => $dietitian->subscriptions()->where('status', 'active')->count(),
            ];

            return response()->json([
                'success' => true,
                'message' => 'Diyetisyen istatistikleri başarıyla getirildi',
                'data' => $stats,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Diyetisyen istatistikleri getirilemedi: ' . $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }
}