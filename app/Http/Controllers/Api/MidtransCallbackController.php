<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\User;
use App\Services\FcmService;
use Exception;

class MidtransCallbackController extends Controller
{
    protected $fcmService;

    // Inject FcmService sama seperti di OrderController
    public function __construct(FcmService $fcmService)
    {
        $this->fcmService = $fcmService;
    }

    public function __invoke(Request $request)
    {
        $serverKey = config('services.midtrans.server_key');

        $orderIdPayload = $request->order_id; // Format: ORD-12-1718000000 atau 12-1718000000
        $statusCode = $request->status_code;
        $grossAmount = $request->gross_amount;
        $signatureKey = $request->signature_key;
        $transactionStatus = $request->transaction_status;
        $fraudStatus = $request->fraud_status;

        // 1. Keamanan: Validasi Signature Key dari Midtrans
        $mySignature = hash('sha512', $orderIdPayload . $statusCode . $grossAmount . $serverKey);

        if ($mySignature !== $signatureKey) {
            return response()->json([
                'success' => false,
                'message' => 'Signature key tidak valid!'
            ], 403);
        }

        // 2. Ekstraksi ID Pesanan asli dari string order_id Midtrans
        $parts = explode('-', $orderIdPayload);
        $realOrderId = ($parts[0] === 'ORD') ? ($parts[1] ?? null) : $parts[0];

        $order = Order::find($realOrderId);

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Data pesanan tidak ditemukan'
            ], 404);
        }

        // 3. Proses status pembayaran berdasarkan respon Midtrans
        if ($transactionStatus == 'capture') {
            if ($fraudStatus == 'challenge') {
                $order->update(['payment_status' => 'unpaid']);
            } else {
                $this->handleSuccessPayment($order);
            }
        } else if ($transactionStatus == 'settlement') {
            $this->handleSuccessPayment($order);
        } else if ($transactionStatus == 'pending') {
            $order->update(['payment_status' => 'unpaid']);
        } else if (in_array($transactionStatus, ['deny', 'expire', 'cancel'])) {
            $order->update([
                'payment_status' => 'failed',
                'status' => 'cancelled'
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Callback Midtrans berhasil diproses'
        ], 200);
    }

    /**
     * Helper function untuk menangani pembayaran sukses
     */
    private function handleSuccessPayment(Order $order)
    {
        // Mencegah proses ulang jika pesanan sudah berstatus 'paid'
        if ($order->payment_status === 'paid') {
            return;
        }

        // Update status pembayaran di database
        $order->update([
            'payment_status' => 'paid',
        ]);

        // A. Kirim Notifikasi FCM ke Pelanggan
        // if ($order->user) {
        //     $customerTokens = $order->user->activeFcmTokens();
        //     if (!empty($customerTokens)) {
        //         $this->fcmService->sendNotification(
        //             tokens: $customerTokens,
        //             title: 'Pembayaran Digital Berhasil! 🎉',
        //             body: "Pembayaran untuk pesanan #ORD-{$order->id} telah diterima. Pesananmu siap diproses!",
        //             extraData: ['order_id' => (string) $order->id, 'type' => 'payment_confirmed']
        //         );
        //     }
        // }

        // B. Kirim Notifikasi FCM ke Semua Karyawan (Kasir, Koki, Admin)
        $employeeTokens = User::whereIn('role', 'karyawan')
            ->get()
            ->flatMap(function ($user) {
                return method_exists($user, 'activeFcmTokens') ? $user->activeFcmTokens() : [];
            })
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        if (!empty($employeeTokens)) {
            $this->fcmService->sendNotification(
                tokens: $employeeTokens,
                title: 'Pesanan Baru Lunas (Digital) 🔔',
                body: "Pesanan #ORD-{$order->id} (Meja {$order->table_id}) telah lunas via Midtrans. Siap dimasak!",
                extraData: ['order_id' => (string) $order->id, 'type' => 'new_paid_order']
            );
        }
    }
}
