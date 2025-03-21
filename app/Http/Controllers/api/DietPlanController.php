<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\DietPlan;
use App\Models\Client;
use App\Models\Dietitian;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class DietPlanController extends Controller
{
    public function index()
    {
        try {
            $dietPlans = DietPlan::with('client.user', 'dietitian.user')->get();
            return response()->json([
                'success' => true,
                'message' => 'Diyet planları başarıyla getirildi',
                'data' => $dietPlans,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Diyet planları getirilemedi: ' . $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }


    public function store(Request $request)
    {
        try {
            $request->validate([
                'client_id' => 'required|exists:clients,id',
                'title' => 'required|string|max:255',
                'start_date' => 'required|date',
                'end_date' => 'nullable|date|after_or_equal:start_date',
                'daily_calories' => 'required|integer|min:0',
                'notes' => 'nullable|string',
                'status' => 'required|in:active,completed,paused',
                'is_ongoing' => 'boolean',
            ], [
                'client_id.required' => 'Danışan ID alanı zorunludur',
                'client_id.exists' => 'Geçerli bir danışan ID giriniz',
                'title.required' => 'Başlık alanı zorunludur',
                'start_date.required' => 'Başlangıç tarihi alanı zorunludur',
                'start_date.date' => 'Geçerli bir tarih formatı giriniz',
                'end_date.date' => 'Geçerli bir tarih formatı giriniz',
                'end_date.after_or_equal' => 'Bitiş tarihi, başlangıç tarihinden önce olamaz',
                'daily_calories.required' => 'Günlük kalori alanı zorunludur',
                'daily_calories.integer' => 'Günlük kalori sayısal bir değer olmalıdır',
                'status.required' => 'Durum alanı zorunludur',
                'status.in' => 'Durum yalnızca "active", "completed" veya "paused" olabilir',
            ]);

            $client = Client::findOrFail($request->client_id);


            if (!$client->dietitian_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bu danışanın bir diyetisyeni yok',
                    'data' => null,
                ], 422);
            }

            $currentUser = auth()->user();
            $dietitian = Dietitian::where('user_id', $currentUser->id)->first();

            if (!$dietitian) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mevcut kullanıcı bir diyetisyen değil',
                    'data' => null,
                ], 403);
            }

            if ($client->dietitian_id != $dietitian->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bu danışan sizin danışanınız değil',
                    'data' => null,
                ], 403);
            }

            $dietPlan = DietPlan::create([
                'client_id' => $request->client_id,
                'dietitian_id' => $client->dietitian_id,
                'title' => $request->title,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'daily_calories' => $request->daily_calories,
                'notes' => $request->notes,
                'status' => $request->status,
                'is_ongoing' => $request->is_ongoing ?? false,
            ]);


            return response()->json([
                'success' => true,
                'message' => 'Diyet planı başarıyla oluşturuldu',
                'data' => $dietPlan,
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
                'message' => 'Diyet planı oluşturulamadı: ' . $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }


    public function show(DietPlan $dietPlan)
    {
        try {
            $dietPlan->load('client.user', 'dietitian.user', 'meals');
            return response()->json([
                'success' => true,
                'message' => 'Diyet planı başarıyla getirildi',
                'data' => $dietPlan,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Diyet planı getirilemedi: ' . $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }


    public function update(Request $request, DietPlan $dietPlan)
    {
        try {
            $request->validate([
                'client_id' => 'sometimes|required|exists:clients,id',
                'title' => 'sometimes|required|string|max:255',
                'start_date' => 'sometimes|required|date',
                'end_date' => 'nullable|date|after_or_equal:start_date',
                'daily_calories' => 'sometimes|required|integer|min:0',
                'notes' => 'nullable|string',
                'status' => 'sometimes|required|in:active,completed,paused',
                'is_ongoing' => 'boolean',
            ], [
                'client_id.required' => 'Danışan ID alanı zorunludur',
                'client_id.exists' => 'Geçerli bir danışan ID giriniz',
                'title.required' => 'Başlık alanı zorunludur',
                'start_date.required' => 'Başlangıç tarihi alanı zorunludur',
                'start_date.date' => 'Geçerli bir tarih formatı giriniz',
                'end_date.date' => 'Geçerli bir tarih formatı giriniz',
                'end_date.after_or_equal' => 'Bitiş tarihi, başlangıç tarihinden önce olamaz',
                'daily_calories.required' => 'Günlük kalori alanı zorunludur',
                'daily_calories.integer' => 'Günlük kalori sayısal bir değer olmalıdır',
                'status.required' => 'Durum alanı zorunludur',
                'status.in' => 'Durum yalnızca "active", "completed" veya "paused" olabilir',
            ]);

            if ($request->has('client_id') && $request->client_id != $dietPlan->client_id) {
                $client = Client::findOrFail($request->client_id);

                if (!$client->dietitian_id) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Bu danışanın bir diyetisyeni yok',
                        'data' => null,
                    ], 422);
                }

                $currentUser = auth()->user();
                $dietitian = Dietitian::where('user_id', $currentUser->id)->first();

                if (!$dietitian) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Mevcut kullanıcı bir diyetisyen değil',
                        'data' => null,
                    ], 403);
                }

                if ($client->dietitian_id != $dietitian->id) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Bu danışan sizin danışanınız değil',
                        'data' => null,
                    ], 403);
                }

                $dietPlan->client_id = $request->client_id;
                $dietPlan->dietitian_id = $client->dietitian_id;
            }

            $dietPlan->update($request->except('client_id'));


            return response()->json([
                'success' => true,
                'message' => 'Diyet planı başarıyla güncellendi',
                'data' => $dietPlan,
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
                'message' => 'Diyet planı güncellenemedi: ' . $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }


    public function destroy(DietPlan $dietPlan)
    {
        try {
            $dietPlan->delete();


            return response()->json([
                'success' => true,
                'message' => 'Diyet planı başarıyla silindi',
                'data' => null,
            ], 204);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Diyet planı silinemedi: ' . $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }


    public function getDietPlansByDietitian($dietitianId)
    {
        try {
            $dietPlans = DietPlan::with('client.user')
                ->where('dietitian_id', $dietitianId)
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Diyetisyenin diyet planları başarıyla getirildi',
                'data' => $dietPlans,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Diyetisyenin diyet planları getirilemedi: ' . $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    public function getDietPlansByClient($clientId)
    {
        try {
            $dietPlans = DietPlan::with('dietitian.user', 'meals')
                ->where('client_id', $clientId)
                ->get();

            if ($dietPlans->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bu danışan için diyet planı bulunamadı',
                    'data' => null,
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Danışanın diyet planları başarıyla getirildi',
                'data' => $dietPlans,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Danışanın diyet planları getirilemedi: ' . $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }
}