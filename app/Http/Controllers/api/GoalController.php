<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Goal;
use App\Models\Client;
use App\Models\Dietitian;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class GoalController extends Controller
{
    public function index()
    {
        try {
            $goals = Goal::with(['client.user', 'dietitian.user'])->get();
            return response()->json([
                'success' => true,
                'message' => 'Hedefler başarıyla getirildi',
                'data' => $goals,
            ]);
        } catch (\Exception $e) {
            Log::error('Hedefler listeleme hatası', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Hedefler getirilemedi: ' . $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    
    public function store(Request $request)
    {
        try {
            $rules = [
                'client_id' => 'required|exists:clients,id',
                'dietitian_id' => 'required|exists:dietitians,id',
                'title' => 'required|string|max:255',
                'description' => 'required|string',
                'target_value' => 'nullable|numeric|min:0',
                'current_value' => 'nullable|numeric|min:0',
                'unit' => 'nullable|string|max:20',
                'category' => 'required|in:weight,measurement,nutrition,habit,other',
                'start_date' => 'required|date',
                'target_date' => 'required|date|after:start_date',
                'status' => 'required|in:not_started,in_progress,completed,failed',
                'priority' => 'required|in:low,medium,high',
                'progress_percentage' => 'nullable|numeric|min:0|max:100',
            ];

            $messages = [
                'client_id.required' => 'Danışan ID alanı zorunludur',
                'client_id.exists' => 'Geçerli bir danışan ID giriniz',
                'dietitian_id.required' => 'Diyetisyen ID alanı zorunludur',
                'dietitian_id.exists' => 'Geçerli bir diyetisyen ID giriniz',
                'title.required' => 'Başlık alanı zorunludur',
                'title.max' => 'Başlık 255 karakterden uzun olamaz',
                'description.required' => 'Açıklama alanı zorunludur',
                'target_value.numeric' => 'Hedef değer sayısal bir değer olmalıdır',
                'current_value.numeric' => 'Mevcut değer sayısal bir değer olmalıdır',
                'unit.max' => 'Birim 20 karakterden uzun olamaz',
                'category.required' => 'Kategori alanı zorunludur',
                'category.in' => 'Kategori yalnızca "weight", "measurement", "nutrition", "habit" veya "other" olabilir',
                'start_date.required' => 'Başlangıç tarihi alanı zorunludur',
                'start_date.date' => 'Başlangıç tarihi geçerli bir tarih olmalıdır',
                'target_date.required' => 'Hedef tarihi alanı zorunludur',
                'target_date.date' => 'Hedef tarihi geçerli bir tarih olmalıdır',
                'target_date.after' => 'Hedef tarihi, başlangıç tarihinden sonra olmalıdır',
                'status.required' => 'Durum alanı zorunludur',
                'status.in' => 'Durum yalnızca "not_started", "in_progress", "completed" veya "failed" olabilir',
                'priority.required' => 'Öncelik alanı zorunludur',
                'priority.in' => 'Öncelik yalnızca "low", "medium" veya "high" olabilir',
                'progress_percentage.numeric' => 'İlerleme yüzdesi sayısal bir değer olmalıdır',
                'progress_percentage.max' => 'İlerleme yüzdesi 100’den büyük olamaz',
            ];

            $request->validate($rules, $messages);

            $currentUser = auth()->user();
            $client = Client::where('user_id', $currentUser->id)->first();
            $dietitian = Dietitian::where('user_id', $currentUser->id)->first();

            if (!$client && !$dietitian) {
                return response()->json([
                    'success' => false,
                    'message' => 'Hedef oluşturma yetkiniz yok',
                    'data' => null,
                ], 403);
            }

            if ($client && $client->id != $request->client_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sadece kendi adınıza hedef oluşturabilirsiniz',
                    'data' => null,
                ], 403);
            }

            if ($dietitian && $dietitian->id != $request->dietitian_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sadece kendi danışanlarınız için hedef oluşturabilirsiniz',
                    'data' => null,
                ], 403);
            }

            $client = Client::findOrFail($request->client_id);
            if ($client->dietitian_id != $request->dietitian_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bu diyetisyen, bu danışanın diyetisyeni değil',
                    'data' => null,
                ], 403);
            }

            $progressPercentage = $request->progress_percentage;
            if ($request->target_value && $request->current_value && !$progressPercentage) {
                $target = $request->target_value;
                $current = $request->current_value;
                $initial = $client->progress()->latest('date')->first()->weight ?? $current; 
                if ($target != $initial) {
                    $progressPercentage = (($initial - $current) / ($initial - $target)) * 100;
                    $progressPercentage = max(0, min(100, $progressPercentage)); 
                }
            }

            $goal = Goal::create([
                'client_id' => $request->client_id,
                'dietitian_id' => $request->dietitian_id,
                'title' => $request->title,
                'description' => $request->description,
                'target_value' => $request->target_value,
                'current_value' => $request->current_value,
                'unit' => $request->unit,
                'category' => $request->category,
                'start_date' => $request->start_date,
                'target_date' => $request->target_date,
                'status' => $request->status,
                'priority' => $request->priority,
                'progress_percentage' => $progressPercentage,
            ]);

            Log::info('Hedef oluşturuldu', ['goal_id' => $goal->id]);

            return response()->json([
                'success' => true,
                'message' => 'Hedef başarıyla oluşturuldu',
                'data' => $goal->load(['client.user', 'dietitian.user']),
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Giriş bilgileri geçersiz',
                'data' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Hedef oluşturma hatası', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Hedef oluşturulamadı: ' . $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

  
    public function show(Goal $goal)
    {
        try {
            $goal->load(['client.user', 'dietitian.user']);
            return response()->json([
                'success' => true,
                'message' => 'Hedef başarıyla getirildi',
                'data' => $goal,
            ]);
        } catch (\Exception $e) {
            Log::error('Hedef getirme hatası', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Hedef getirilemedi: ' . $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    
    public function update(Request $request, Goal $goal)
    {
        try {
            $rules = [
                'title' => 'sometimes|required|string|max:255',
                'description' => 'sometimes|required|string',
                'target_value' => 'nullable|numeric|min:0',
                'current_value' => 'nullable|numeric|min:0',
                'unit' => 'nullable|string|max:20',
                'category' => 'sometimes|required|in:weight,measurement,nutrition,habit,other',
                'start_date' => 'sometimes|required|date',
                'target_date' => 'sometimes|required|date|after:start_date',
                'status' => 'sometimes|required|in:not_started,in_progress,completed,failed',
                'priority' => 'sometimes|required|in:low,medium,high',
                'progress_percentage' => 'nullable|numeric|min:0|max:100',
            ];

            $messages = [
                'title.required' => 'Başlık alanı zorunludur',
                'title.max' => 'Başlık 255 karakterden uzun olamaz',
                'description.required' => 'Açıklama alanı zorunludur',
                'target_value.numeric' => 'Hedef değer sayısal bir değer olmalıdır',
                'current_value.numeric' => 'Mevcut değer sayısal bir değer olmalıdır',
                'unit.max' => 'Birim 20 karakterden uzun olamaz',
                'category.required' => 'Kategori alanı zorunludur',
                'category.in' => 'Kategori yalnızca "weight", "measurement", "nutrition", "habit" veya "other" olabilir',
                'start_date.required' => 'Başlangıç tarihi alanı zorunludur',
                'start_date.date' => 'Başlangıç tarihi geçerli bir tarih olmalıdır',
                'target_date.required' => 'Hedef tarihi alanı zorunludur',
                'target_date.date' => 'Hedef tarihi geçerli bir tarih olmalıdır',
                'target_date.after' => 'Hedef tarihi, başlangıç tarihinden sonra olmalıdır',
                'status.required' => 'Durum alanı zorunludur',
                'status.in' => 'Durum yalnızca "not_started", "in_progress", "completed" veya "failed" olabilir',
                'priority.required' => 'Öncelik alanı zorunludur',
                'priority.in' => 'Öncelik yalnızca "low", "medium" veya "high" olabilir',
                'progress_percentage.numeric' => 'İlerleme yüzdesi sayısal bir değer olmalıdır',
                'progress_percentage.max' => 'İlerleme yüzdesi 100’den büyük olamaz',
            ];

            $request->validate($rules, $messages);

            $currentUser = auth()->user();
            $client = Client::where('user_id', $currentUser->id)->first();
            $dietitian = Dietitian::where('user_id', $currentUser->id)->first();

            if (!$client && !$dietitian) {
                return response()->json([
                    'success' => false,
                    'message' => 'Hedef güncelleme yetkiniz yok',
                    'data' => null,
                ], 403);
            }

            if ($client && $client->id != $goal->client_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sadece kendi hedeflerinizi güncelleyebilirsiniz',
                    'data' => null,
                ], 403);
            }

            if ($dietitian && $dietitian->id != $goal->dietitian_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bu hedef sizin danışanlarınıza ait değil',
                    'data' => null,
                ], 403);
            }

            $progressPercentage = $request->progress_percentage;
            if ($request->has('target_value') && $request->has('current_value') && !$progressPercentage) {
                $target = $request->target_value;
                $current = $request->current_value;
                $initial = $goal->current_value ?? $current; // İlk ölçümden başlama varsayımı
                if ($target != $initial) {
                    $progressPercentage = (($initial - $current) / ($initial - $target)) * 100;
                    $progressPercentage = max(0, min(100, $progressPercentage)); // 0-100 arasında sınırla
                }
            }

            $goal->update(array_merge($request->all(), ['progress_percentage' => $progressPercentage]));

            Log::info('Hedef güncellendi', ['goal_id' => $goal->id]);

            return response()->json([
                'success' => true,
                'message' => 'Hedef başarıyla güncellendi',
                'data' => $goal->load(['client.user', 'dietitian.user']),
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Güncelleme bilgileri geçersiz',
                'data' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Hedef güncelleme hatası', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Hedef güncellenemedi: ' . $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    public function destroy(Goal $goal)
    {
        try {
            $currentUser = auth()->user();
            $client = Client::where('user_id', $currentUser->id)->first();
            $dietitian = Dietitian::where('user_id', $currentUser->id)->first();

            if (!$client && !$dietitian) {
                return response()->json([
                    'success' => false,
                    'message' => 'Hedef silme yetkiniz yok',
                    'data' => null,
                ], 403);
            }

            if ($client && $client->id != $goal->client_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sadece kendi hedeflerinizi silebilirsiniz',
                    'data' => null,
                ], 403);
            }

            if ($dietitian && $dietitian->id != $goal->dietitian_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bu hedef sizin danışanlarınıza ait değil',
                    'data' => null,
                ], 403);
            }

            $goal->delete();

            Log::info('Hedef silindi', ['goal_id' => $goal->id]);

            return response()->json([
                'success' => true,
                'message' => 'Hedef başarıyla silindi',
                'data' => null,
            ], 204);
        } catch (\Exception $e) {
            Log::error('Hedef silme hatası', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Hedef silinemedi: ' . $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    
    public function getGoalsByClient($clientId)
    {
        try {
            $client = Client::findOrFail($clientId);

            $currentUser = auth()->user();
            $isClient = $currentUser->id == $client->user_id;
            $dietitian = $client->dietitian;

            if (!$isClient && (!$dietitian || $dietitian->user_id != $currentUser->id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bu danışanın hedeflerini görüntüleme yetkiniz yok',
                    'data' => null,
                ], 403);
            }

            $goals = Goal::with(['client.user', 'dietitian.user'])
                ->where('client_id', $clientId)
                ->get();

            if ($goals->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bu danışan için hedef bulunamadı',
                    'data' => null,
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Danışanın hedefleri başarıyla getirildi',
                'data' => $goals,
            ]);
        } catch (\Exception $e) {
            Log::error('Danışanın hedefleri getirme hatası', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Danışanın hedefleri getirilemedi: ' . $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

  
    public function getGoalsByDietitian($dietitianId)
    {
        try {
            $dietitian = Dietitian::findOrFail($dietitianId);

            $currentUser = auth()->user();
            $isDietitian = $currentUser->id == $dietitian->user_id;

            if (!$isDietitian) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bu diyetisyenin hedeflerini görüntüleme yetkiniz yok',
                    'data' => null,
                ], 403);
            }

            $goals = Goal::with(['client.user', 'dietitian.user'])
                ->where('dietitian_id', $dietitianId)
                ->get();

            if ($goals->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bu diyetisyen için hedef bulunamadı',
                    'data' => null,
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Diyetisyenin hedefleri başarıyla getirildi',
                'data' => $goals,
            ]);
        } catch (\Exception $e) {
            Log::error('Diyetisyenin hedefleri getirme hatası', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Diyetisyenin hedefleri getirilemedi: ' . $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }
}