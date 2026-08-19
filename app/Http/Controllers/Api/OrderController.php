<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\MenuItem;
use Midtrans\Snap; // <-- Cukup import Snap saja, Config sudah diurus AppServiceProvider
use Exception;
use Illuminate\Support\Facades\DB;
use App\Services\FcmService;

class OrderController extends Controller
{
    protected $fcmService;

    // Inject FcmService melalui constructor
    public function __construct(FcmService $fcmService)
    {
        $this->fcmService = $fcmService;
    }

    public function checkout(Request $request)
    {
        // ❌ Baris Config::$serverKey dkk di sini SUDAH DIHAPUS

        $request->validate([
            'table_id' => 'required',
            'payment_method' => 'required|in:CASH,NON_CASH',
            'total_price' => 'required|numeric',
            'items' => 'required|array',
            'items.*.menu_item_id' => 'required',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        DB::beginTransaction();

        try {
            $paymentMethod = $request->input('payment_method');
            $totalPrice = $request->input('total_price');
            $tableId = $request->input('table_id');
            $userId = $request->input('user_id');

            $order = null;
            if ($paymentMethod === 'CASH') {
                $order = Order::where('table_id', $tableId)
                    ->where('status', 'pending')
                    ->where('payment_method', 'CASH')
                    ->where('payment_status', 'unpaid')
                    ->first();
            }

            if ($order) {
                $order->increment('total_price', $totalPrice);
            } else {
                $order = Order::create([
                    'user_id' => $userId,
                    'table_id' => $tableId,
                    'status' => 'pending',
                    'payment_method' => $paymentMethod,
                    'payment_status' => 'unpaid',
                    'total_price' => $totalPrice,
                ]);
            }

            foreach ($request->input('items') as $item) {
                $menu = MenuItem::with(['discount' => function ($query) {
                    $query->where('status', 'active');
                }])->findOrFail($item['menu_item_id']);

                $hargaNormal = (int) $menu->price;
                $potonganPerPcs = 0;

                if ($menu->discount) {
                    $discountAmount = (float) $menu->discount->amount;
                    if ($discountAmount <= 100) {
                        $potonganPerPcs = $hargaNormal * ($discountAmount / 100);
                    } else {
                        $potonganPerPcs = $discountAmount;
                    }
                }

                $hargaDiskon = $hargaNormal - $potonganPerPcs;
                $jumlahBaru = (int) $item['quantity'];

                $existingItem = $order->menuItems()->where('menu_item_id', $menu->id)->first();

                $menuName = $menu->name;
                $menuImage = $menu->image;

                if ($existingItem && $paymentMethod === 'CASH') {
                    $totalKuantitas = $existingItem->pivot->jumlah + $jumlahBaru;
                    $totalPotonganKolektif = $potonganPerPcs * $totalKuantitas;
                    $totalHargaKolektif = $hargaDiskon * $totalKuantitas;

                    $order->menuItems()->updateExistingPivot($menu->id, [
                        'jumlah' => $totalKuantitas,
                        'potongan' => $totalPotonganKolektif,
                        'total' => $totalHargaKolektif,
                        'nama' => $menuName,
                        'image' => $menuImage,
                    ]);
                } else {
                    $potonganAwal = $potonganPerPcs * $jumlahBaru;
                    $totalHargaAwal = $hargaDiskon * $jumlahBaru;

                    $order->menuItems()->attach($menu->id, [
                        'harga' => $hargaNormal,
                        'jumlah' => $jumlahBaru,
                        'potongan' => $potonganAwal,
                        'total' => $totalHargaAwal,
                        'nama' => $menuName,
                        'image' => $menuImage,
                    ]);
                }
            }

            if ($paymentMethod === 'CASH') {
                DB::commit();
                return response()->json([
                    'success' => true,
                    'message' => 'Pesanan tunai berhasil dibuat',
                    'midtransSnapUrl' => null
                ], 200);
            }

            $params = [
                'transaction_details' => [
                    'order_id' => 'ORD-' . $order->id . '-' . time(),
                    'gross_amount' => (int) $totalPrice,
                ]
            ];

            // Tinggal panggil Snap langsung!
            $snapUrl = Snap::createTransaction($params)->redirect_url;

            $order->update([
                'snap_token' => basename($snapUrl)
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Tautan pembayaran berhasil dibuat',
                'midtransSnapUrl' => $snapUrl
            ], 200, [], JSON_UNESCAPED_SLASHES);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses pembayaran: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getOrders(Request $request)
    {
        try {
            $status = $request->query('status');
            $paymentStatus = $request->query('payment_status');
            $userId = $request->query('user_id');
            $tableId = $request->query('table_id');

            $query = Order::with(['menuItems', 'table']);

            if ($status) {
                $query->where('status', $status);
            }

            if ($paymentStatus) {
                $query->where('payment_status', $paymentStatus);
            }

            if ($status === 'pending' || $status === 'proses') {
                if ($tableId && $userId) {
                    $query->where('user_id', $userId)->where('table_id', $tableId);
                }
            } else if ($status === 'selesai' || $status === 'batal' || $status === 'cancelled') {
                if ($userId) {
                    $query->where('user_id', $userId);
                }
            } else {
                if ($userId) {
                    $query->where('user_id', $userId);
                }
            }

            $orders = $query->orderBy('updated_at', 'asc')->get();

            return response()->json([
                'success' => true,
                'data' => $orders
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data pesanan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function cancelOrder($id)
    {
        $order = \App\Models\Order::find($id);

        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Pesanan tidak ditemukan.'], 404);
        }

        if ($order->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Pesanan tidak dapat dibatalkan karena sedang diproses atau sudah selesai.'
            ], 400);
        }

        $order->status = 'cancelled';
        $order->save();

        return response()->json([
            'success' => true,
            'message' => 'Pesanan berhasil dibatalkan.',
            'data' => true // Ditunangkan dengan kebutuhan Android (Solusi 1 kemarin)
        ], 200);
    }

    public function repayOrder($id)
    {
        $order = \App\Models\Order::find($id);

        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Pesanan tidak ditemukan.'], 404);
        }

        $isProduction = config('services.midtrans.is_production', false);
        $baseUrl = $isProduction ? 'https://app.midtrans.com/snap/v2/vtweb/' : 'https://app.sandbox.midtrans.com/snap/v2/vtweb/';

        // KONDISI A: Menggunakan token lama dari database jika ada
        if ($order->snap_token) {
            $snapUrl = $baseUrl . $order->snap_token;

            return response()->json([
                'success' => true,
                'message' => 'Snap URL berhasil dimuat dari token lama.',
                'data' => ['snap_url' => $snapUrl]
            ], 200);
        }

        // KONDISI B: Membuat token baru ke Midtrans (Tanpa tulis ulang baris Config)
        try {
            $midtransOrderId = $order->id . '-' . time();

            $params = [
                'transaction_details' => [
                    'order_id' => $midtransOrderId,
                    'gross_amount' => (int) $order->total_price,
                ],
            ];

            $snapToken = Snap::getSnapToken($params);

            $order->snap_token = $snapToken;
            $order->save();

            $snapUrl = $baseUrl . $snapToken;

            return response()->json([
                'success' => true,
                'message' => 'Link pembayaran baru berhasil dibuat.',
                'data' => ['snap_url' => $snapUrl]
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat token baru: ' . $e->getMessage()
            ], 500);
        }
    }

    public function confirmPayment($id)
    {
        $order = Order::findOrFail($id);

        // Validasi agar tidak mengonfirmasi ulang yang sudah lunas
        if ($order->payment_status === 'paid') {
            return response()->json([
                'success' => false,
                'message' => 'Pesanan ini sudah lunas!'
            ], 400);
        }

        // Update status pembayaran
        $order->update([
            'payment_status' => 'paid'
        ]);

        if ($order->user) {
            $customerTokens = $order->user->activeFcmTokens();
            $this->fcmService->sendNotification(
                tokens: $customerTokens,
                title: 'Pembayaran Dikonfirmasi! 🎉',
                body: "Pembayaran untuk pesanan #ORD-{$order->id} telah diterima. Pesananmu siap dimasak!",
                extraData: ['order_id' => (string) $order->id, 'type' => 'payment_confirmed']
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Pembayaran berhasil dikonfirmasi. Pesanan siap dimasak!',
            'data' => $order
        ]);
    }

    // 2. Fungsi untuk Koki/Dapur mulai memasak pesanan
    public function processOrder($id)
    {
        $order = Order::findOrFail($id);

        // Keamanan: Makanan tidak boleh dimasak jika belum lunas!
        if ($order->payment_status !== 'paid') {
            return response()->json([
                'success' => false,
                'message' => 'Gagal! Pesanan harus dilunasi terlebih dahulu sebelum dimasak.'
            ], 400);
        }

        // Update status pengerjaan dapur
        $order->update([
            'status' => 'proses'
        ]);

        $fcmResult = null;

        if ($order->user) {
            $customerTokens = $order->user->activeFcmTokens();

            // Simpan respon pengiriman notifikasi ke variabel $fcmResult
            $fcmResult = $this->fcmService->sendNotification(
                tokens: $customerTokens,
                title: 'Pesanan Sedang Dimasak 🍳',
                body: "Koki sedang menyiapkan pesanan #ORD-{$order->id} milikmu. Mohon tunggu sebentar!",
                extraData: ['order_id' => (string) $order->id, 'type' => 'order_processing']
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Pesanan sekarang sedang diproses di dapur.',
            'data' => $order,
            'fcm_debug' => $fcmResult
        ]);
    }

    public function finishOrder($id)
    {
        $order = Order::findOrFail($id);

        // Keamanan: Makanan tidak boleh dimasak jika belum lunas!
        if ($order->payment_status !== 'paid') {
            return response()->json([
                'success' => false,
                'message' => 'Gagal! Pesanan harus dilunasi terlebih dahulu sebelum dimasak.'
            ], 400);
        }

        // Update status pengerjaan dapur
        $order->update([
            'status' => 'selesai'
        ]);

        if ($order->user) {
            $customerTokens = $order->user->activeFcmTokens();
            $this->fcmService->sendNotification(
                tokens: $customerTokens,
                title: 'Pesanan Selesai! 🍽️',
                body: "Pesanan #ORD-{$order->id} sudah siap disajikan. Selamat menikmati!",
                extraData: ['order_id' => (string) $order->id, 'type' => 'order_finished']
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Pesanan selesai diproses di dapur.',
            'data' => $order
        ]);
    }
}
