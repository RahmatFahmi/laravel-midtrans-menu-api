<?php

namespace App\Services;

use Kreait\Firebase\Contract\Messaging;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Illuminate\Support\Facades\Log;

class FcmService
{
    protected $messaging;

    public function __construct(Messaging $messaging)
    {
        $this->messaging = $messaging;
    }

    /**
     * Kirim notifikasi ke banyak token sekaligus (Multicast)
     * dan mengembalikan laporan detail pengiriman untuk debugging.
     */
    public function sendNotification(array $tokens, string $title, string $body, array $extraData = []): array
    {
        // 1. Cek apakah array token kosong
        if (empty($tokens)) {
            $msg = 'Peringatan FCM: Array token kosong. Tidak ada token aktif ditemukan di database.';
            Log::warning($msg);
            return [
                'status' => 'warning',
                'message' => $msg,
                'tokens_sent' => []
            ];
        }

        try {
            $notification = Notification::create($title, $body);

            $message = CloudMessage::new()
                ->withNotification($notification)
                ->withData($extraData);

            // 2. Kirim notifikasi dan tangkap hasilnya (MulticastSendReport)
            $report = $this->messaging->sendMulticast($message, $tokens);

            $successCount = $report->successes()->count();
            $failureCount = $report->failures()->count();

            // 3. Kumpulkan pesan error jika ada token yang gagal
            $errorDetails = [];
            if ($report->hasFailures()) {
                foreach ($report->failures()->getItems() as $failure) {
                    $errorDetails[] = [
                        'token' => $failure->target()->value(),
                        'error' => $failure->error()->getMessage()
                    ];
                }
            }

            $logData = [
                'success_count' => $successCount,
                'failure_count' => $failureCount,
                'errors' => $errorDetails
            ];

            Log::info('FCM Send Report:', $logData);

            return [
                'status' => $failureCount === 0 ? 'success' : 'partial_success',
                'summary' => "Berhasil: {$successCount}, Gagal: {$failureCount}",
                'details' => $logData
            ];
        } catch (\Exception $e) {
            // Tangkap exception global (misal: Kredensial Firebase JSON tidak valid / file hilang)
            $errorMessage = 'FCM Fatal Exception: ' . $e->getMessage();
            Log::error($errorMessage);

            return [
                'status' => 'error',
                'message' => $errorMessage
            ];
        }
    }
}
