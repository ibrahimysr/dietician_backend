<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Client;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class PaymentController extends Controller
{
    
    public function index()
    {
        try {
            $payments = Payment::with(['client.user', 'subscription'])
                ->get();
            return response()->json([
                'success' => true,
                'message' => 'Ödemeler başarıyla getirildi',
                'data' => $payments,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ödemeler getirilemedi: ' . $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    
    public function store(Request $request)
    {
        try {
            $rules = [
                'subscription_id' => 'required|exists:subscriptions,id',
                'client_id' => 'required|exists:clients,id',
                'amount' => 'required|numeric|min:0',
                'currency' => 'required|string|size:3',
                'payment_date' => 'required|date',
                'payment_method' => 'required|string|max:50',
                'transaction_id' => 'required|string|max:255|unique:payments,transaction_id',
                'status' => 'required|in:pending,completed,failed,refunded',
                'refund_date' => 'nullable|date|after:payment_date',
            ];

            $messages = [
                'subscription_id.required' => 'Abonelik ID alanı zorunludur',
                'subscription_id.exists' => 'Geçerli bir abonelik ID giriniz',
                'client_id.required' => 'Danışan ID alanı zorunludur',
                'client_id.exists' => 'Geçerli bir danışan ID giriniz',
                'amount.required' => 'Miktar alanı zorunludur',
                'amount.numeric' => 'Miktar sayısal bir değer olmalıdır',
                'currency.required' => 'Para birimi alanı zorunludur',
                'currency.size' => 'Para birimi 3 karakter olmalıdır (örneğin, TRY)',
                'payment_date.required' => 'Ödeme tarihi alanı zorunludur',
                'payment_date.date' => 'Ödeme tarihi geçerli bir tarih olmalıdır',
                'payment_method.required' => 'Ödeme yöntemi alanı zorunludur',
                'transaction_id.required' => 'İşlem kimliği alanı zorunludur',
                'transaction_id.unique' => 'Bu işlem kimliği zaten kullanılıyor',
                'status.required' => 'Durum alanı zorunludur',
                'status.in' => 'Durum yalnızca "pending", "completed", "failed" veya "refunded" olabilir',
                'refund_date.date' => 'İade tarihi geçerli bir tarih olmalıdır',
                'refund_date.after' => 'İade tarihi, ödeme tarihinden sonra olmalıdır',
            ];

            $request->validate($rules, $messages);

            $currentUser = auth()->user();
            $client = Client::where('user_id', $currentUser->id)->first();

            if (!$client || $client->id != $request->client_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sadece kendi adınıza ödeme oluşturabilirsiniz',
                    'data' => null,
                ], 403);
            }

            $subscription = Subscription::findOrFail($request->subscription_id);
            if ($subscription->client_id != $request->client_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bu abonelik size ait değil',
                    'data' => null,
                ], 403);
            }

            $subscriptionPlan = $subscription->subscriptionPlan;
            if ($request->amount != $subscriptionPlan->price) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ödeme miktarı abonelik planının fiyatıyla eşleşmiyor',
                    'data' => null,
                ], 400);
            }

            $payment = Payment::create([
                'subscription_id' => $request->subscription_id,
                'client_id' => $request->client_id,
                'amount' => $request->amount,
                'currency' => $request->currency,
                'payment_date' => $request->payment_date,
                'payment_method' => $request->payment_method,
                'transaction_id' => $request->transaction_id,
                'status' => $request->status,
                'refund_date' => $request->refund_date,
            ]);

            if ($payment->status == 'completed') {
                $subscription->update(['payment_status' => 'paid']);
            }


            return response()->json([
                'success' => true,
                'message' => 'Ödeme başarıyla oluşturuldu',
                'data' => $payment->load(['client.user', 'subscription']),
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
                'message' => 'Ödeme oluşturulamadı: ' . $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    
    public function show(Payment $payment)
    {
        try {
            $payment->load(['client.user', 'subscription']);
            return response()->json([
                'success' => true,
                'message' => 'Ödeme başarıyla getirildi',
                'data' => $payment,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ödeme getirilemedi: ' . $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    
    public function update(Request $request, Payment $payment)
    {
        try {
            $rules = [
                'status' => 'sometimes|required|in:pending,completed,failed,refunded',
                'refund_date' => 'nullable|date|after:payment_date',
            ];

            $messages = [
                'status.required' => 'Durum alanı zorunludur',
                'status.in' => 'Durum yalnızca "pending", "completed", "failed" veya "refunded" olabilir',
                'refund_date.date' => 'İade tarihi geçerli bir tarih olmalıdır',
                'refund_date.after' => 'İade tarihi, ödeme tarihinden sonra olmalıdır',
            ];

            $request->validate($rules, $messages);

            $currentUser = auth()->user();
            $client = Client::where('user_id', $currentUser->id)->first();

            if (!$client || $client->id != $payment->client_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sadece kendi ödemelerinizi güncelleyebilirsiniz',
                    'data' => null,
                ], 403);
            }

            if ($request->has('status') && $request->status == 'completed' && $payment->status != 'completed') {
                $payment->subscription->update(['payment_status' => 'paid']);
            } elseif ($request->has('status') && $request->status == 'refunded' && $payment->status != 'refunded') {
                $payment->subscription->update(['payment_status' => 'unpaid']);
            }

            $payment->update($request->all());


            return response()->json([
                'success' => true,
                'message' => 'Ödeme başarıyla güncellendi',
                'data' => $payment->load(['client.user', 'subscription']),
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
                'message' => 'Ödeme güncellenemedi: ' . $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    
    public function destroy(Payment $payment)
    {
        try {
            $currentUser = auth()->user();
            $client = Client::where('user_id', $currentUser->id)->first();

            if (!$client || $client->id != $payment->client_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sadece kendi ödemelerinizi silebilirsiniz',
                    'data' => null,
                ], 403);
            }

            $payment->delete();


            return response()->json([
                'success' => true,
                'message' => 'Ödeme başarıyla silindi',
                'data' => null,
            ], 204);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ödeme silinemedi: ' . $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    public function getPaymentsByClient($clientId)
    {
        try {
            $client = Client::findOrFail($clientId);

            $currentUser = auth()->user();
            $isClient = $currentUser->id == $client->user_id;

            if (!$isClient) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bu danışanın ödemelerini görüntüleme yetkiniz yok',
                    'data' => null,
                ], 403);
            }

            $payments = Payment::with(['client.user', 'subscription'])
                ->where('client_id', $clientId)
                ->get();

            if ($payments->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bu danışan için ödeme bulunamadı',
                    'data' => null,
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Danışanın ödemeleri başarıyla getirildi',
                'data' => $payments,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Danışanın ödemeleri getirilemedi: ' . $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    
    public function getPaymentsBySubscription($subscriptionId)
    {
        try {
            $subscription = Subscription::findOrFail($subscriptionId);

            $currentUser = auth()->user();
            $client = Client::where('user_id', $currentUser->id)->first();

            if (!$client || $client->id != $subscription->client_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bu aboneliğin ödemelerini görüntüleme yetkiniz yok',
                    'data' => null,
                ], 403);
            }

            $payments = Payment::with(['client.user', 'subscription'])
                ->where('subscription_id', $subscriptionId)
                ->get();

            if ($payments->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bu abonelik için ödeme bulunamadı',
                    'data' => null,
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Aboneliğin ödemeleri başarıyla getirildi',
                'data' => $payments,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Aboneliğin ödemeleri getirilemedi: ' . $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }
}