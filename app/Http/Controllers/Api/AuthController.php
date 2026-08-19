<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\UserDevice;
use App\Models\Table;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        try {
            // 1. Validasi Input
            $validator = Validator::make($request->all(), [
                'login'    => 'required',
                'password' => 'required',
            ], [
                'login.required' => 'Email atau Username tidak boleh kosong.',
                'password.required' => 'Password tidak boleh kosong.',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first(), // Kirim pesan error pertama saja agar rapi di Android
                    'errors'  => $validator->errors()
                ], 422);
            }

            // 2. Cari User & Cek Password
            // Kita gabung pencarian dan pengecekan agar lebih aman
            $user = User::where('email', $request->login)
                ->orWhere('username', $request->login)
                ->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Username atau password salah, silakan cek kembali.',
                ], 401);
            }

            // 3. Opsional: Cek apakah akun aktif (jika ada field is_active)
            // if (!$user->is_active) {
            //     return response()->json([
            //         'success' => false,
            //         'message' => 'Akun Anda tidak aktif, silakan hubungi admin.',
            //     ], 403);
            // }

            // 4. Buat Token (Sanctum)
            $token = $user->createToken('android-token')->plainTextToken;

            // 5. Response Sukses
            return response()->json([
                'success' => true,
                'message' => 'Selamat datang kembali, ' . $user->name,
                'data'    => [
                    'token' => $token,
                    'user'  => [
                        'id'       => $user->id,
                        'name'     => $user->name,
                        'username' => $user->username,
                        'role'     => $user->role,
                    ]
                ]
            ], 200);
        } catch (Exception $e) {
            // Jika ada error database atau sistem (misal server SQL mati)
            return response()->json([
                'success' => false,
                'message' => 'Terjadi gangguan pada server, silakan coba lagi nanti.',
                'debug_error' => $e->getMessage() // Hapus baris ini jika sudah production
            ], 500);
        }
    }

    public function connectToTable(Request $request)
    {
        // Cek apakah Meja ada di DB (Hanya validasi)
        $tableExists = Table::where('code', $request->table_code)->first();
        if (!$tableExists) {
            return response()->json(['message' => 'Meja tidak terdaftar!'], 404);
        }

        return DB::transaction(function () use ($request, $tableExists) {
            // 1. Cari device berdasarkan UUID
            $device = UserDevice::where('device_uuid', $request->device_uuid)->first();

            if ($device) {
                // Cek apakah user yang terhubung ke device ini sudah 'customer'
                if ($device->user && $device->user->role === 'pelanggan') {
                    $user = $device->user;
                } else {
                    // Jika device ada tapi user-nya Karyawan/Admin, buatkan user Guest baru
                    $user = User::create([
                        'name' => 'Guest_' . Str::random(5),
                        'role' => 'pelanggan',
                    ]);
                    // UPDATE device yang sudah ada agar mengarah ke user Guest baru
                    $device->update([
                        'user_id' => $user->id,
                        'fcm_token' => $request->fcm_token
                    ]);
                }
            } else {
                // 2. Jika Device benar-benar BELUM PERNAH terdaftar sama sekali
                $user = User::create([
                    'name' => 'Guest_' . Str::random(5),
                    'role' => 'pelanggan',
                ]);

                UserDevice::create([
                    'user_id'     => $user->id,
                    'device_uuid' => $request->device_uuid,
                    'fcm_token'   => $request->fcm_token,
                    'platform'    => 'android'
                ]);
            }

            // 3. Generate Token
            $token = $user->createToken('guest_token')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Berhasil tersambung',
                'data' => [
                    'token' => $token,
                    'user'  => $user,
                    'table_code' => $tableExists->name,
                    'table_id' => $tableExists->id
                ]
            ]);
        });
    }

    public function logout(Request $request)
    {
        try {
            // Hapus token yang sedang digunakan
            $request->user()->currentAccessToken()->delete();

            return response()->json([
                'success' => true,
                'message' => 'Anda telah berhasil keluar aplikasi.'
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal logout, silakan coba lagi.'
            ], 500);
        }
    }
}
