<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserDevice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DeviceController extends Controller
{
    public function registerDevice(Request $request)
    {
        // 1. Validasi Input
        $validator = Validator::make($request->all(), [
            'device_uuid' => 'required',
            'fcm_token'   => 'required',
            'platform'    => 'required|in:android,ios',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors'  => $validator->errors()
            ], 422);
        }

        // 2. Simpan atau Update Token
        $device = UserDevice::updateOrCreate(
            [
                'user_id'     => $request->user()->id, // Ambil ID dari token login (Sanctum/JWT)
                'device_uuid' => $request->device_uuid
            ],
            [
                'fcm_token' => $request->fcm_token,
                'platform'  => $request->platform,
                'is_active' => true
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Device berhasil didaftarkan untuk notifikasi',
            'data'    => $device
        ]);
    }
}
