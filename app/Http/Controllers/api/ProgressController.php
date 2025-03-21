<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Progress;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class ProgressController extends Controller
{
    
    public function index()
    {
        try {
            $progressRecords = Progress::with(['client.user'])->get();
            return response()->json([
                'success' => true,
                'message' => 'İlerleme kayıtları başarıyla getirildi',
                'data' => $progressRecords,
            ]);
        } catch (\Exception $e) {
            Log::error('İlerleme kayıtları listeleme hatası', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'İlerleme kayıtları getirilemedi: ' . $e->getMessage(),
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
                'weight' => 'required|numeric|min:0',
                'waist' => 'nullable|numeric|min:0',
                'arm' => 'nullable|numeric|min:0',
                'chest' => 'nullable|numeric|min:0',
                'hip' => 'nullable|numeric|min:0',
                'body_fat_percentage' => 'nullable|numeric|min:0|max:100',
                'notes' => 'nullable|string',
                'photo_url' => 'nullable|string|max:255',
            ];

            $messages = [
                'client_id.required' => 'Danışan ID alanı zorunludur',
                'client_id.exists' => 'Geçerli bir danışan ID giriniz',
                'date.required' => 'Tarih alanı zorunludur',
                'date.date' => 'Tarih geçerli bir tarih olmalıdır',
                'weight.required' => 'Kilo alanı zorunludur',
                'weight.numeric' => 'Kilo sayısal bir değer olmalıdır',
                'waist.numeric' => 'Bel ölçüsü sayısal bir değer olmalıdır',
                'arm.numeric' => 'Kol ölçüsü sayısal bir değer olmalıdır',
                'chest.numeric' => 'Göğüs ölçüsü sayısal bir değer olmalıdır',
                'hip.numeric' => 'Kalça ölçüsü sayısal bir değer olmalıdır',
                'body_fat_percentage.numeric' => 'Vücut yağ oranı sayısal bir değer olmalıdır',
                'body_fat_percentage.max' => 'Vücut yağ oranı 100’den büyük olamaz',
                'photo_url.string' => 'Fotoğraf URL’si bir metin olmalıdır',
                'photo_url.max' => 'Fotoğraf URL’si 255 karakterden uzun olamaz',
            ];

            $request->validate($rules, $messages);

            $currentUser = auth()->user();
            $client = Client::where('user_id', $currentUser->id)->first();
            $dietitian = $client ? Client::find($request->client_id)->dietitian : null;

            if (!$client && !$dietitian) {
                return response()->json([
                    'success' => false,
                    'message' => 'İlerleme kaydı oluşturma yetkiniz yok',
                    'data' => null,
                ], 403);
            }

            if ($client && $client->id != $request->client_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sadece kendi adınıza ilerleme kaydı oluşturabilirsiniz',
                    'data' => null,
                ], 403);
            }

            if ($dietitian && $dietitian->id != Client::find($request->client_id)->dietitian_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bu danışan sizin danışanınız değil',
                    'data' => null,
                ], 403);
            }

            $progress = Progress::create($request->all());


            return response()->json([
                'success' => true,
                'message' => 'İlerleme kaydı başarıyla oluşturuldu',
                'data' => $progress->load(['client.user']),
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
                'message' => 'İlerleme kaydı oluşturulamadı: ' . $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    
    public function show(Progress $progress)
    {
        try {
            $progress->load(['client.user']);
            return response()->json([
                'success' => true,
                'message' => 'İlerleme kaydı başarıyla getirildi',
                'data' => $progress,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'İlerleme kaydı getirilemedi: ' . $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    
    public function update(Request $request, Progress $progress)
    {
        try {
            $rules = [
                'weight' => 'sometimes|required|numeric|min:0',
                'waist' => 'nullable|numeric|min:0',
                'arm' => 'nullable|numeric|min:0',
                'chest' => 'nullable|numeric|min:0',
                'hip' => 'nullable|numeric|min:0',
                'body_fat_percentage' => 'nullable|numeric|min:0|max:100',
                'notes' => 'nullable|string',
                'photo_url' => 'nullable|string|max:255',
            ];

            $messages = [
                'weight.required' => 'Kilo alanı zorunludur',
                'weight.numeric' => 'Kilo sayısal bir değer olmalıdır',
                'waist.numeric' => 'Bel ölçüsü sayısal bir değer olmalıdır',
                'arm.numeric' => 'Kol ölçüsü sayısal bir değer olmalıdır',
                'chest.numeric' => 'Göğüs ölçüsü sayısal bir değer olmalıdır',
                'hip.numeric' => 'Kalça ölçüsü sayısal bir değer olmalıdır',
                'body_fat_percentage.numeric' => 'Vücut yağ oranı sayısal bir değer olmalıdır',
                'body_fat_percentage.max' => 'Vücut yağ oranı 100’den büyük olamaz',
                'photo_url.string' => 'Fotoğraf URL’si bir metin olmalıdır',
                'photo_url.max' => 'Fotoğraf URL’si 255 karakterden uzun olamaz',
            ];

            $request->validate($rules, $messages);

            $currentUser = auth()->user();
            $client = Client::where('user_id', $currentUser->id)->first();
            $dietitian = $client ? Client::find($progress->client_id)->dietitian : null;

            if (!$client && !$dietitian) {
                return response()->json([
                    'success' => false,
                    'message' => 'İlerleme kaydı güncelleme yetkiniz yok',
                    'data' => null,
                ], 403);
            }

            if ($client && $client->id != $progress->client_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sadece kendi ilerleme kaydınızı güncelleyebilirsiniz',
                    'data' => null,
                ], 403);
            }

            if ($dietitian && $dietitian->id != Client::find($progress->client_id)->dietitian_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bu danışan sizin danışanınız değil',
                    'data' => null,
                ], 403);
            }

            $progress->update($request->all());


            return response()->json([
                'success' => true,
                'message' => 'İlerleme kaydı başarıyla güncellendi',
                'data' => $progress->load(['client.user']),
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
                'message' => 'İlerleme kaydı güncellenemedi: ' . $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    
    public function destroy(Progress $progress)
    {
        try {
            $currentUser = auth()->user();
            $client = Client::where('user_id', $currentUser->id)->first();
            $dietitian = $client ? Client::find($progress->client_id)->dietitian : null;

            if (!$client && !$dietitian) {
                return response()->json([
                    'success' => false,
                    'message' => 'İlerleme kaydı silme yetkiniz yok',
                    'data' => null,
                ], 403);
            }

            if ($client && $client->id != $progress->client_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sadece kendi ilerleme kaydınızı silebilirsiniz',
                    'data' => null,
                ], 403);
            }

            if ($dietitian && $dietitian->id != Client::find($progress->client_id)->dietitian_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bu danışan sizin danışanınız değil',
                    'data' => null,
                ], 403);
            }

            $progress->delete();


            return response()->json([
                'success' => true,
                'message' => 'İlerleme kaydı başarıyla silindi',
                'data' => null,
            ], 204);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'İlerleme kaydı silinemedi: ' . $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    public function getProgressByClient(Request $request, $clientId)
    {
        try {
            $client = Client::findOrFail($clientId);

            $currentUser = auth()->user();
            $isClient = $currentUser->id == $client->user_id;
            $dietitian = $client->dietitian;

            if (!$isClient && (!$dietitian || $dietitian->user_id != $currentUser->id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bu danışanın ilerleme kayıtlarını görüntüleme yetkiniz yok',
                    'data' => null,
                ], 403);
            }

            $query = Progress::with(['client.user'])
                ->where('client_id', $clientId);

            if ($request->has('start_date') && $request->has('end_date')) {
                $query->whereBetween('date', [$request->start_date, $request->end_date]);
            }

            $progressRecords = $query->get();

            if ($progressRecords->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bu danışan için ilerleme kaydı bulunamadı',
                    'data' => null,
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Danışanın ilerleme kayıtları başarıyla getirildi',
                'data' => $progressRecords,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Danışanın ilerleme kayıtları getirilemedi: ' . $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }
}