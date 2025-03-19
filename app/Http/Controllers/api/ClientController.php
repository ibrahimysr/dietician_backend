<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ClientController extends Controller
{
    
    public function index()
    {
        try {
            $clients = Client::with('user', 'dietitian')->get();
            return response()->json([
                'success' => true,
                'message' => 'Danışanlar başarıyla getirildi',
                'data' => $clients,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Danışanlar getirilemedi: ' . $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

   
    public function store(Request $request)
    {
        try {
            $request->validate([
                'user_id' => 'required|exists:users,id',
                'dietitian_id' => 'nullable|exists:dietitians,id',
                'birth_date' => 'required|date',
                'gender' => 'required|in:male,female,other',
                'height' => 'required|numeric|min:0',
                'weight' => 'required|numeric|min:0',
                'activity_level' => 'required|in:sedentary,light,moderate,active,very_active',
                'goal' => 'required|string|max:255',
                'allergies' => 'nullable|string',
                'preferences' => 'nullable|string',
                'medical_conditions' => 'nullable|string',
            ], [
                'user_id.required' => 'Kullanıcı ID alanı zorunludur',
                'user_id.exists' => 'Geçerli bir kullanıcı ID giriniz',
                'dietitian_id.exists' => 'Geçerli bir diyetisyen ID giriniz',
                'birth_date.required' => 'Doğum tarihi alanı zorunludur',
                'birth_date.date' => 'Geçerli bir tarih formatı giriniz',
                'gender.required' => 'Cinsiyet alanı zorunludur',
                'gender.in' => 'Cinsiyet yalnızca "male", "female" veya "other" olabilir',
                'height.required' => 'Boy alanı zorunludur',
                'height.numeric' => 'Boy sayısal bir değer olmalıdır',
                'weight.required' => 'Kilo alanı zorunludur',
                'weight.numeric' => 'Kilo sayısal bir değer olmalıdır',
                'activity_level.required' => 'Aktivite seviyesi alanı zorunludur',
                'activity_level.in' => 'Aktivite seviyesi geçerli bir değer olmalıdır',
                'goal.required' => 'Hedef alanı zorunludur',
            ]);

            $existingClient = Client::where('user_id', $request->user_id)->first();
            if ($existingClient) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bu kullanıcı zaten bir danışan kaydına sahip',
                    'data' => null,
                ], 422);
            }

            $client = Client::create($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Danışan başarıyla kaydedildi',
                'data' => $client,
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
                'message' => 'Danışan kaydedilemedi: ' . $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

  
    public function show(Client $client)
    {
        try {
            $client->load('user', 'dietitian', 'dietPlans', 'progress');
            
            return response()->json([
                'success' => true,
                'message' => 'Danışan başarıyla getirildi',
                'data' => $client,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Danışan getirilemedi: ' . $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

  
    public function update(Request $request, Client $client)
    {
        try {
            $request->validate([
                'dietitian_id' => 'nullable|exists:dietitians,id',
                'birth_date' => 'sometimes|required|date',
                'gender' => 'sometimes|required|in:male,female,other',
                'height' => 'sometimes|required|numeric|min:0',
                'weight' => 'sometimes|required|numeric|min:0',
                'activity_level' => 'sometimes|required|in:sedentary,light,moderate,active,very_active',
                'goal' => 'sometimes|required|string|max:255',
                'allergies' => 'nullable|string',
                'preferences' => 'nullable|string',
                'medical_conditions' => 'nullable|string',
            ], [
                'dietitian_id.exists' => 'Geçerli bir diyetisyen ID giriniz',
                'birth_date.required' => 'Doğum tarihi alanı zorunludur',
                'birth_date.date' => 'Geçerli bir tarih formatı giriniz',
                'gender.required' => 'Cinsiyet alanı zorunludur',
                'gender.in' => 'Cinsiyet yalnızca "male", "female" veya "other" olabilir',
                'height.required' => 'Boy alanı zorunludur',
                'height.numeric' => 'Boy sayısal bir değer olmalıdır',
                'weight.required' => 'Kilo alanı zorunludur',
                'weight.numeric' => 'Kilo sayısal bir değer olmalıdır',
                'activity_level.required' => 'Aktivite seviyesi alanı zorunludur',
                'activity_level.in' => 'Aktivite seviyesi geçerli bir değer olmalıdır',
                'goal.required' => 'Hedef alanı zorunludur',
            ]);

            $client->update($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Danışan başarıyla güncellendi',
                'data' => $client,
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
                'message' => 'Danışan güncellenemedi: ' . $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

   
    public function destroy(Client $client)
    {
        try {
            $client->delete();

            return response()->json([
                'success' => true,
                'message' => 'Danışan başarıyla silindi',
                'data' => null,
            ], 204);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Danışan silinemedi: ' . $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

   
    public function getClientsByDietitian($dietitianId)
    {
        try {
            $clients = Client::with('user')
                ->where('dietitian_id', $dietitianId)
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Diyetisyenin danışanları başarıyla getirildi',
                'data' => $clients,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Diyetisyenin danışanları getirilemedi: ' . $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

   
    public function getClientByUserId($userId)
    {
        try {
            $client = Client::with('dietitian', 'dietPlans', 'progress')
                ->where('user_id', $userId)
                ->first();

            if (!$client) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bu kullanıcı için danışan kaydı bulunamadı',
                    'data' => null,
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Danışan başarıyla getirildi',
                'data' => $client,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Danışan getirilemedi: ' . $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    
   
}