<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Models\Dietitian;
use Illuminate\Http\Request;

class DietitianApprovalController extends Controller
{
    public function __construct()
    {
        
         $this->middleware('auth:sanctum'); 
         $this->middleware(function ($request, $next) {
             if (!$request->user() || $request->user()->role !== 'admin') {
                 abort(403, 'Yetkisiz Erişim.');
             }
             return $next($request);
         });
    }

    public function listPending()
    {
        try {
            $pendingDietitians = Dietitian::with('user')
                                        ->pending() 
                                        ->get();
            return response()->json([
                'success' => true,
                'message' => 'Onay bekleyen diyetisyen başvuruları getirildi.',
                'data' => $pendingDietitians,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Başvurular getirilemedi: ' . $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

   
    public function approve(Dietitian $dietitian)
    {
        if ($dietitian->status !== Dietitian::STATUS_PENDING) {
            return response()->json([
                'success' => false,
                'message' => 'Sadece onay bekleyen başvurular onaylanabilir.',
                'data' => $dietitian,
            ], 400); 
        }

        try {
            $dietitian->status = Dietitian::STATUS_APPROVED;
            $dietitian->is_active = true; 
            $dietitian->rejection_reason = null; 
            $dietitian->save();


            return response()->json([
                'success' => true,
                'message' => 'Diyetisyen başvurusu başarıyla onaylandı ve aktif edildi.',
                'data' => $dietitian->load('user'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Başvuru onaylanamadı: ' . $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    public function reject(Request $request, Dietitian $dietitian)
    {
         if ($dietitian->status !== Dietitian::STATUS_PENDING) {
            return response()->json([
                'success' => false,
                'message' => 'Sadece onay bekleyen başvurular reddedilebilir.',
                'data' => $dietitian,
            ], 400); 
        }

        $request->validate([
            'reason' => 'required|string|max:1000',
        ],[
            'reason.required' => 'Reddetme sebebi zorunludur.',
            'reason.max' => 'Reddetme sebebi en fazla 1000 karakter olabilir.'
        ]);

        try {
            $dietitian->status = Dietitian::STATUS_REJECTED;
            $dietitian->is_active = false;
            $dietitian->rejection_reason = $request->input('reason');
            $dietitian->save();


            return response()->json([
                'success' => true,
                'message' => 'Diyetisyen başvurusu reddedildi.',
                'data' => $dietitian->load('user'),
            ]);
        } catch (\Exception $e) {
             return response()->json([
                'success' => false,
                'message' => 'Başvuru reddedilemedi: ' . $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }
}