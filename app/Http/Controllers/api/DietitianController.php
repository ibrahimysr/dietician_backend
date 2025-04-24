<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Dietitian;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;
class DietitianController extends Controller
{

    public function index()
    {
        try {
            $dietitians = Dietitian::with('user')
                ->isActive() //
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Aktif ve onaylanmış diyetisyenler başarıyla getirildi.',
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
        if (!Auth::guard('sanctum')->check()) {
            return response()->json([
                'success' => false,
                'message' => 'Bu işlem için giriş yapmalısınız.',
                'data' => null,
            ], 401); // Unauthorized
        }

        $loggedInUser = Auth::guard('sanctum')->user();

        if ($loggedInUser->role !== 'dietitian') {
            return response()->json([
                'success' => false,
                'message' => 'Sadece diyetisyen rolüne sahip kullanıcılar başvuru yapabilir.',
                'data' => null,
            ], 403);
        }

        $existingDietitian = Dietitian::withTrashed()->where('user_id', $loggedInUser->id)->first();
        if ($existingDietitian) {
            $message = 'Bu kullanıcı için zaten bir diyetisyen kaydı ';
            switch ($existingDietitian->status) {
                case Dietitian::STATUS_PENDING:
                    $message .= 'onay bekliyor.';
                    break;
                case Dietitian::STATUS_APPROVED:
                    $message .= 'onaylanmış durumda.';
                    break;
                case Dietitian::STATUS_REJECTED:
                    $message .= 'reddedilmiş. Lütfen yönetici ile iletişime geçin.';
                    break;
                default:
                    $message .= 'mevcut.';
            }
            if ($existingDietitian->trashed()) {
                $message .= ' (Hesap silinmiş)';
            }
            return response()->json([
                'success' => false,
                'message' => $message,
                'data' => null,
            ], 409);
        }

        try {
            $validatedData = $request->validate([
                'specialty' => 'required|string|max:255',
                'bio' => 'required|string|max:5000',
                'hourly_rate' => 'nullable|numeric|min:0',
                'experience_years' => 'nullable|integer|min:0',
            ], [
                'specialty.required' => 'Uzmanlık alanı zorunludur.',
                'specialty.max' => 'Uzmanlık en fazla 255 karakter olabilir.',
                'bio.required' => 'Biyografi alanı zorunludur.',
                'bio.max' => 'Biyografi en fazla 5000 karakter olabilir.',
                'hourly_rate.numeric' => 'Saatlik ücret sayısal bir değer olmalıdır.',
                'hourly_rate.min' => 'Saatlik ücret negatif olamaz.',
                'experience_years.integer' => 'Deneyim yılı tam sayı olmalıdır.',
                'experience_years.min' => 'Deneyim yılı negatif olamaz.',
            ]);

            $dietitianData = $validatedData;
            $dietitianData['user_id'] = $loggedInUser->id;

            $dietitian = Dietitian::create($dietitianData);


            return response()->json([
                'success' => true,
                'message' => 'Diyetisyenlik başvurunuz başarıyla alındı. Yönetici onayı bekleniyor.',
                'data' => $dietitian->load('user'),
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Başvuru bilgileri geçersiz.',
                'data' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Diyetisyen başvurusu oluşturulamadı: ' . $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }


    public function show(Request $request, Dietitian $dietitian)
    {
        try {
            $loggedInUser = Auth::guard('sanctum')->user();
            $canView = false;

            if ($dietitian->status === Dietitian::STATUS_APPROVED && $dietitian->is_active) {
                $canView = true;
            }

            if ($loggedInUser) {
                if ($loggedInUser->role === 'admin' || $loggedInUser->id === $dietitian->user_id) {
                    $canView = true;
                }
            }

            if (!$canView) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bu diyetisyen profili görüntülenemiyor veya bulunamadı.',
                    'data' => null,
                ], 404);
            }

            $dietitian->load(['user', 'clients', 'subscriptionPlans']);

            return response()->json([
                'success' => true,
                'message' => 'Diyetisyen başarıyla getirildi.',
                'data' => $dietitian,
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Diyetisyen bulunamadı.',
                'data' => null,
            ], 404);
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
        if (!Auth::guard('sanctum')->check()) {
            return response()->json(['success' => false, 'message' => 'Yetkisiz işlem.', 'data' => null], 401);
        }
        $loggedInUser = Auth::guard('sanctum')->user();
        $isOwner = $loggedInUser->id === $dietitian->user_id;
        $isAdmin = $loggedInUser->role === 'admin';

        if (!($isAdmin || ($isOwner && $dietitian->status === Dietitian::STATUS_APPROVED))) {
            return response()->json([
                'success' => false,
                'message' => 'Bu profili güncelleme yetkiniz yok veya profil onaylanmamış.',
                'data' => null,
            ], 403);
        }

        try {
            $validatedData = $request->validate([
                'specialty' => 'sometimes|required|string|max:255',
                'bio' => 'sometimes|required|string|max:5000',
                'hourly_rate' => 'nullable|numeric|min:0',
                'experience_years' => 'nullable|integer|min:0',
                'is_active' => 'sometimes|boolean',
            ], [
                'specialty.required' => 'Uzmanlık alanı zorunludur.',
                'bio.required' => 'Biyografi alanı zorunludur.',
                'is_active.boolean' => 'Aktiflik durumu true veya false olmalıdır.',
            ]);

            if ($isOwner && !$isAdmin && $dietitian->status !== Dietitian::STATUS_APPROVED && isset($validatedData['is_active'])) {

                unset($validatedData['is_active']);

            }

            $dietitian->update($validatedData);

            return response()->json([
                'success' => true,
                'message' => 'Diyetisyen başarıyla güncellendi.',
                'data' => $dietitian->load('user'),
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Güncelleme bilgileri geçersiz.',
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
        if (!Auth::guard('sanctum')->check() || Auth::guard('sanctum')->user()->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Bu işlemi yapma yetkiniz yok.',
                'data' => null
            ], 403);
        }

        try {
            $dietitian->delete();

            return response()->json([
                'success' => true,
                'message' => 'Diyetisyen başarıyla (geçici olarak) silindi.',
                'data' => null,
            ], 200);
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
                'clients.user',
                'subscriptionPlans',
                'recipes'
            ])
                ->where('user_id', $userId)
                ->first();

            if (!$dietitian) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bu kullanıcı ID\'sine sahip bir diyetisyen kaydı bulunamadı.',
                    'data' => null,
                ], 404);
            }

            $loggedInUser = Auth::guard('sanctum')->user();
            $canView = false;

            if ($dietitian->status === Dietitian::STATUS_APPROVED && $dietitian->is_active) {
                $canView = true;
            }
            if ($loggedInUser) {
                if ($loggedInUser->role === 'admin' || $loggedInUser->id === $dietitian->user_id) {
                    $canView = true;
                }
            }

            if (!$canView) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bu diyetisyen profili görüntülenemiyor veya bulunamadı.',
                    'data' => null,
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Diyetisyen başarıyla getirildi.',
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
        if (!Auth::guard('sanctum')->check()) {
            return response()->json(['success' => false, 'message' => 'Yetkisiz işlem.', 'data' => null], 401);
        }
        $loggedInUser = Auth::guard('sanctum')->user();
        $isOwner = $loggedInUser->id === $dietitian->user_id;
        $isAdmin = $loggedInUser->role === 'admin';

        if (!($isAdmin || $isOwner)) {
            return response()->json(['success' => false, 'message' => 'Bu işlemi yapma yetkiniz yok.', 'data' => null], 403);
        }

        if ($dietitian->status !== Dietitian::STATUS_APPROVED) {
            return response()->json([
                'success' => false,
                'message' => 'Sadece onaylanmış diyetisyenlerin aktiflik durumu değiştirilebilir.',
                'data' => $dietitian,
            ], 400);
        }

        try {
            $dietitian->is_active = !$dietitian->is_active;
            $dietitian->save();

            $statusText = $dietitian->is_active ? 'aktif' : 'pasif';

            return response()->json([
                'success' => true,
                'message' => "Diyetisyen durumu başarıyla '$statusText' olarak güncellendi.",
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
                ->isActive()
                ->withCount('clients')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Aktif diyetisyenler başarıyla getirildi.',
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
        if (!Auth::guard('sanctum')->check()) {
            return response()->json(['success' => false, 'message' => 'Yetkisiz işlem.', 'data' => null], 401);
        }
        $loggedInUser = Auth::guard('sanctum')->user();
        $isOwner = $loggedInUser->id === $dietitian->user_id;
        $isAdmin = $loggedInUser->role === 'admin';

        if (!($isAdmin || $isOwner)) {
            return response()->json(['success' => false, 'message' => 'Bu istatistikleri görme yetkiniz yok.', 'data' => null], 403);
        }

        try {

            $stats = [
                'client_count' => $dietitian->clients()->count(),
                'active_diet_plans' => $dietitian->dietPlans()->whereHas('client')->count(),
                'recipe_count' => $dietitian->recipes()->count(),
                'subscription_plan_count' => $dietitian->subscriptionPlans()->count(),
                'active_subscription_count' => $dietitian->subscriptions()->where('status', 'active')->count(),
                'profile_status' => $dietitian->status,
                'is_active' => $dietitian->is_active,
            ];

            return response()->json([
                'success' => true,
                'message' => 'Diyetisyen istatistikleri başarıyla getirildi.',
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