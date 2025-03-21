<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Models\Client;
use App\Models\Dietitian;
use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class SubscriptionController extends Controller
{
   
    public function index()
    {
        try {
            $subscriptions = Subscription::with(['client.user', 'dietitian.user', 'subscriptionPlan'])
                ->get();
            return response()->json([
                'success' => true,
                'message' => 'Abonelikler başarıyla getirildi',
                'data' => $subscriptions,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Abonelikler getirilemedi: ' . $e->getMessage(),
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
                'subscription_plan_id' => 'required|exists:subscription_plans,id',
                'start_date' => 'required|date|after_or_equal:today',
                'status' => 'required|in:active,expired,canceled',
                'auto_renew' => 'required|boolean',
                'payment_status' => 'required|in:paid,unpaid',
            ];

            $messages = [
                'client_id.required' => 'Danışan ID alanı zorunludur',
                'client_id.exists' => 'Geçerli bir danışan ID giriniz',
                'dietitian_id.required' => 'Diyetisyen ID alanı zorunludur',
                'dietitian_id.exists' => 'Geçerli bir diyetisyen ID giriniz',
                'subscription_plan_id.required' => 'Abonelik planı ID alanı zorunludur',
                'subscription_plan_id.exists' => 'Geçerli bir abonelik planı ID giriniz',
                'start_date.required' => 'Başlangıç tarihi alanı zorunludur',
                'start_date.date' => 'Başlangıç tarihi geçerli bir tarih olmalıdır',
                'start_date.after_or_equal' => 'Başlangıç tarihi bugün veya sonrası olmalıdır',
                'status.required' => 'Durum alanı zorunludur',
                'status.in' => 'Durum yalnızca "active", "expired" veya "canceled" olabilir',
                'auto_renew.required' => 'Otomatik yenileme alanı zorunludur',
                'auto_renew.boolean' => 'Otomatik yenileme alanı true/false olmalıdır',
                'payment_status.required' => 'Ödeme durumu alanı zorunludur',
                'payment_status.in' => 'Ödeme durumu yalnızca "paid" veya "unpaid" olabilir',
            ];

            $request->validate($rules, $messages);

            $currentUser = auth()->user();
            $client = Client::where('user_id', $currentUser->id)->first();
            $dietitian = Dietitian::where('user_id', $currentUser->id)->first();

            if (!$client && !$dietitian) {
                return response()->json([
                    'success' => false,
                    'message' => 'Abonelik oluşturma yetkiniz yok',
                    'data' => null,
                ], 403);
            }

            if ($client && $client->id != $request->client_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sadece kendi adınıza abonelik oluşturabilirsiniz',
                    'data' => null,
                ], 403);
            }

            if ($dietitian) {
                $client = Client::findOrFail($request->client_id);
                if ($client->dietitian_id != $dietitian->id) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Bu danışan sizin danışanınız değil',
                        'data' => null,
                    ], 403);
                }
            }

            $subscriptionPlan = SubscriptionPlan::findOrFail($request->subscription_plan_id);
            if ($subscriptionPlan->dietitian_id != $request->dietitian_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bu abonelik planı seçilen diyetisyene ait değil',
                    'data' => null,
                ], 400);
            }

            $startDate = \Carbon\Carbon::parse($request->start_date);
            $endDate = $startDate->copy()->addDays($subscriptionPlan->duration);

            $subscription = Subscription::create([
                'client_id' => $request->client_id,
                'dietitian_id' => $request->dietitian_id,
                'subscription_plan_id' => $request->subscription_plan_id,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'status' => $request->status,
                'auto_renew' => $request->auto_renew,
                'payment_status' => $request->payment_status,
            ]);

            Log::info('Abonelik oluşturuldu', ['subscription_id' => $subscription->id]);

            return response()->json([
                'success' => true,
                'message' => 'Abonelik başarıyla oluşturuldu',
                'data' => $subscription->load(['client.user', 'dietitian.user', 'subscriptionPlan']),
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
                'message' => 'Abonelik oluşturulamadı: ' . $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

   
    public function show(Subscription $subscription)
    {
        try {
            $subscription->load(['client.user', 'dietitian.user', 'subscriptionPlan']);
            return response()->json([
                'success' => true,
                'message' => 'Abonelik başarıyla getirildi',
                'data' => $subscription,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Abonelik getirilemedi: ' . $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    public function update(Request $request, Subscription $subscription)
    {
        try {
            $rules = [
                'status' => 'sometimes|required|in:active,expired,canceled',
                'auto_renew' => 'sometimes|required|boolean',
                'payment_status' => 'sometimes|required|in:paid,unpaid',
            ];

            $messages = [
                'status.required' => 'Durum alanı zorunludur',
                'status.in' => 'Durum yalnızca "active", "expired" veya "canceled" olabilir',
                'auto_renew.required' => 'Otomatik yenileme alanı zorunludur',
                'auto_renew.boolean' => 'Otomatik yenileme alanı true/false olmalıdır',
                'payment_status.required' => 'Ödeme durumu alanı zorunludur',
                'payment_status.in' => 'Ödeme durumu yalnızca "paid" veya "unpaid" olabilir',
            ];

            $request->validate($rules, $messages);

            $currentUser = auth()->user();
            $client = Client::where('user_id', $currentUser->id)->first();
            $dietitian = Dietitian::where('user_id', $currentUser->id)->first();

            if (!$client && !$dietitian) {
                return response()->json([
                    'success' => false,
                    'message' => 'Abonelik güncelleme yetkiniz yok',
                    'data' => null,
                ], 403);
            }

            if ($client && $client->id != $subscription->client_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sadece kendi aboneliğinizi güncelleyebilirsiniz',
                    'data' => null,
                ], 403);
            }

            if ($dietitian && $subscription->dietitian_id != $dietitian->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bu abonelik sizin danışanınıza ait değil',
                    'data' => null,
                ], 403);
            }

            $subscription->update($request->all());

            Log::info('Abonelik güncellendi', ['subscription_id' => $subscription->id]);

            return response()->json([
                'success' => true,
                'message' => 'Abonelik başarıyla güncellendi',
                'data' => $subscription->load(['client.user', 'dietitian.user', 'subscriptionPlan']),
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
                'message' => 'Abonelik güncellenemedi: ' . $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    
    public function destroy(Subscription $subscription)
    {
        try {
            $currentUser = auth()->user();
            $client = Client::where('user_id', $currentUser->id)->first();
            $dietitian = Dietitian::where('user_id', $currentUser->id)->first();

            if (!$client && !$dietitian) {
                return response()->json([
                    'success' => false,
                    'message' => 'Abonelik silme yetkiniz yok',
                    'data' => null,
                ], 403);
            }

            if ($client && $client->id != $subscription->client_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sadece kendi aboneliğinizi silebilirsiniz',
                    'data' => null,
                ], 403);
            }

            if ($dietitian && $subscription->dietitian_id != $dietitian->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bu abonelik sizin danışanınıza ait değil',
                    'data' => null,
                ], 403);
            }

            $subscription->delete();

            Log::info('Abonelik silindi', ['subscription_id' => $subscription->id]);

            return response()->json([
                'success' => true,
                'message' => 'Abonelik başarıyla silindi',
                'data' => null,
            ], 204);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Abonelik silinemedi: ' . $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    public function getSubscriptionsByClient($clientId)
    {
        try {
            $client = Client::findOrFail($clientId);

            $currentUser = auth()->user();
            $isClient = $currentUser->id == $client->user_id;
            $dietitian = Dietitian::where('user_id', $currentUser->id)->first();

            if (!$isClient && !$dietitian) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bu danışanın aboneliklerini görüntüleme yetkiniz yok',
                    'data' => null,
                ], 403);
            }

            if ($dietitian && $client->dietitian_id != $dietitian->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bu danışan sizin danışanınız değil',
                    'data' => null,
                ], 403);
            }

            $subscriptions = Subscription::with(['client.user', 'dietitian.user', 'subscriptionPlan'])
                ->where('client_id', $clientId)
                ->get();

            if ($subscriptions->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bu danışan için abonelik bulunamadı',
                    'data' => null,
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Danışanın abonelikleri başarıyla getirildi',
                'data' => $subscriptions,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Danışanın abonelikleri getirilemedi: ' . $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    
    public function getSubscriptionsByDietitian($dietitianId)
    {
        try {
            $dietitian = Dietitian::findOrFail($dietitianId);

            $currentUser = auth()->user();
            $isDietitian = $currentUser->id == $dietitian->user_id;
            $isClient = Client::where('user_id', $currentUser->id)
                ->where('dietitian_id', $dietitianId)
                ->exists();

            if (!$isDietitian && !$isClient) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bu diyetisyenin aboneliklerini görüntüleme yetkiniz yok',
                    'data' => null,
                ], 403);
            }

            $subscriptions = Subscription::with(['client.user', 'dietitian.user', 'subscriptionPlan'])
                ->where('dietitian_id', $dietitianId)
                ->get();

            if ($subscriptions->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bu diyetisyen için abonelik bulunamadı',
                    'data' => null,
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Diyetisyenin abonelikleri başarıyla getirildi',
                'data' => $subscriptions,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Diyetisyenin abonelikleri getirilemedi: ' . $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }
}