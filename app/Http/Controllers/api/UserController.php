<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{

    public function index()
    {
        try {
            $users = User::all();
            return response()->json([
                'success' => true,
                'message' => 'Kullanıcılar başarıyla getirildi',
                'data' => $users,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Kullanıcılar getirilemedi: ' . $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }


    public function store(Request $request)
    {

        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users,email',
                'password' => 'required|string|min:8',
                'role' => 'sometimes|in:dietitian,client',
                'phone' => 'nullable|string|max:20',
                'profile_photo' => 'nullable|string|max:255',
            ], [
                'name.required' => 'İsim alanı zorunludur',
                'email.required' => 'E-posta alanı zorunludur',
                'email.email' => 'Geçerli bir e-posta adresi giriniz',
                'email.unique' => 'Bu e-posta zaten kullanımda',
                'password.required' => 'Şifre alanı zorunludur',
                'password.min' => 'Şifre en az 8 karakter olmalıdır',
                'role.in' => 'Rol yalnızca "dietitian" veya "client" olabilir',
            ]);

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => $request->input('role', 'client'),
                'phone' => $request->phone,
                'profile_photo' => $request->profile_photo,
            ]);


            $token = $user->createToken('auth_token')->plainTextToken;


            return response()->json([
                'success' => true,
                'message' => 'Kullanıcı başarıyla kaydedildi',
                'data' => [
                    'user' => $user,
                    'token' => $token,
                ],
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
                'message' => 'Kullanıcı kaydedilemedi: ' . $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }


    public function login(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|string|email',
                'password' => 'required|string',
            ], [
                'email.required' => 'E-posta alanı zorunludur',
                'email.email' => 'Geçerli bir e-posta adresi giriniz',
                'password.required' => 'Şifre alanı zorunludur',
            ]);

            $user = User::where('email', $request->email)->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Girilen bilgiler hatalı',
                    'data' => null,
                ], 401);
            }

            $token = $user->createToken('auth_token')->plainTextToken;


            return response()->json([
                'success' => true,
                'message' => 'Giriş başarıyla yapıldı',
                'data' => [
                    'user' => $user,
                    'token' => $token,
                ],
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Giriş bilgileri geçersiz',
                'data' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Giriş yapılamadı: ' . $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }


    public function logout(Request $request)
    {
        try {
            if (!$request->user()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Yetkisiz işlem: Kullanıcı bulunamadı',
                    'data' => null,
                ], 401);
            }

            $request->user()->currentAccessToken()->delete();


            return response()->json([
                'success' => true,
                'message' => 'Çıkış başarıyla yapıldı',
                'data' => null,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Çıkış yapılamadı: ' . $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }


    public function show(User $user)
    {
        try {
            return response()->json([
                'success' => true,
                'message' => 'Kullanıcı başarıyla getirildi',
                'data' => $user,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Kullanıcı getirilemedi: ' . $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }


    public function update(Request $request, User $user)
    {
        try {
            $request->validate([
                'name' => 'sometimes|required|string|max:255',
                'email' => 'sometimes|required|string|email|max:255|unique:users,email,' . $user->id,
                'password' => 'sometimes|required|string|min:8',
                'role' => 'sometimes|in:dietitian,client',
                'phone' => 'nullable|string|max:20',
                'profile_photo' => 'nullable|string|max:255',
            ], [
                'name.required' => 'İsim alanı zorunludur',
                'email.required' => 'E-posta alanı zorunludur',
                'email.email' => 'Geçerli bir e-posta adresi giriniz',
                'email.unique' => 'Bu e-posta zaten kullanımda',
                'password.required' => 'Şifre alanı zorunludur',
                'password.min' => 'Şifre en az 8 karakter olmalıdır',
                'role.in' => 'Rol yalnızca "dietitian" veya "client" olabilir',
            ]);

            $user->update($request->only([
                'name',
                'email',
                'role',
                'phone',
                'profile_photo'
            ]));

            if ($request->has('password')) {
                $user->password = Hash::make($request->password);
                $user->save();
            }


            return response()->json([
                'success' => true,
                'message' => 'Kullanıcı başarıyla güncellendi',
                'data' => $user,
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
                'message' => 'Kullanıcı güncellenemedi: ' . $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }


    public function destroy(User $user)
    {
        try {
            $user->delete();


            return response()->json([
                'success' => true,
                'message' => 'Kullanıcı başarıyla silindi',
                'data' => null,
            ], 204);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Kullanıcı silinemedi: ' . $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }
}