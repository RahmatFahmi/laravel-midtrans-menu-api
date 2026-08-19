<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\MenuItem;
use App\Models\User;
use App\Models\Discount;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // Cache 5 menit biar dashboard gak query berat tiap refresh
        $data = Cache::remember('admin.dashboard.stats', now()->addMinutes(5), function () {

            // ----------------------------------------
            // 1. Kartu Ringkasan Atas
            // ----------------------------------------
            $totalPendapatanHariIni = Order::where('status', 'selesai')
                ->where('payment_status', 'settlement')
                ->whereDate('created_at', today())
                ->sum('total_price');

            $totalPesananHariIni = Order::whereDate('created_at', today())->count();

            $totalPelanggan = User::where('role', 'customer')->count();

            $mejaAktif = Order::whereIn('status', ['pending', 'proses'])
                ->distinct('table_id')
                ->count('table_id');

            $pendapatanKemarin = Order::where('status', 'selesai')
                ->where('payment_status', 'settlement')
                ->whereDate('created_at', today()->subDay())
                ->sum('total_price');

            $persenPendapatan = $pendapatanKemarin > 0
                ? round((($totalPendapatanHariIni - $pendapatanKemarin) / $pendapatanKemarin) * 100, 2)
                : 0;

            $pesananKemarin = Order::whereDate('created_at', today()->subDay())->count();
            $persenPesanan = $pesananKemarin > 0
                ? round((($totalPesananHariIni - $pesananKemarin) / $pesananKemarin) * 100, 2)
                : 0;

            // ----------------------------------------
            // 2. Grafik Pendapatan 7 Hari Terakhir
            // ----------------------------------------
            $revenuePerHari = Order::selectRaw('DATE(created_at) as tanggal, SUM(total_price) as total')
                ->where('status', 'selesai')
                ->where('payment_status', 'settlement')
                ->where('created_at', '>=', now()->subDays(6)->startOfDay())
                ->groupBy('tanggal')
                ->orderBy('tanggal')
                ->get();

            $chartLabels = [];
            $chartData = [];
            for ($i = 6; $i >= 0; $i--) {
                $tgl = now()->subDays($i)->format('Y-m-d');
                $chartLabels[] = now()->subDays($i)->format('d M');
                $found = $revenuePerHari->firstWhere('tanggal', $tgl);
                $chartData[] = $found ? (int) $found->total : 0;
            }

            // Statistik ringkas mingguan — 1 query saja pakai conditional aggregate
            $statMingguan = Order::where('created_at', '>=', now()->subDays(6)->startOfDay())
                ->selectRaw("
                    COUNT(*) as total_order,
                    SUM(CASE WHEN status = 'selesai' THEN 1 ELSE 0 END) as total_selesai,
                    SUM(CASE WHEN status = 'batal' THEN 1 ELSE 0 END) as total_batal,
                    SUM(CASE WHEN status = 'selesai' AND payment_status = 'settlement' THEN total_price ELSE 0 END) as total_pendapatan
                ")
                ->first();

            $totalOrderMingguIni = (int) $statMingguan->total_order;
            $totalPendapatanMingguIni = (float) $statMingguan->total_pendapatan;
            $totalBatalMingguIni = (int) $statMingguan->total_batal;
            $rasioSelesai = $totalOrderMingguIni > 0
                ? round(($statMingguan->total_selesai / $totalOrderMingguIni) * 100, 2)
                : 0;

            // ----------------------------------------
            // 3. Meja Paling Ramai (5 besar)
            // ----------------------------------------
            $mejaTerlaris = Order::selectRaw('table_id, COUNT(*) as total_order')
                ->with('table')
                ->groupBy('table_id')
                ->orderByDesc('total_order')
                ->limit(5)
                ->get();

            $maxOrderMeja = $mejaTerlaris->max('total_order') ?: 1;

            // ----------------------------------------
            // 4. Menu Terlaris (5 besar)
            // Grouping cukup pakai id (primary key), lebih aman & efisien
            // ----------------------------------------
            $menuTerlaris = MenuItem::select('menu_items.*')
                ->join('menu_item_order', 'menu_items.id', '=', 'menu_item_order.menu_item_id')
                ->join('orders', 'menu_item_order.order_id', '=', 'orders.id')
                ->where('orders.status', 'selesai')
                ->selectRaw('SUM(menu_item_order.jumlah) as total_terjual, SUM(menu_item_order.total) as total_pendapatan')
                ->groupBy('menu_items.id')
                ->orderByDesc('total_terjual')
                ->limit(5)
                ->get();

            // ----------------------------------------
            // 5. Menu Rating Tertinggi (5 besar)
            // ----------------------------------------
            $menuRatingTertinggi = MenuItem::select('menu_items.*')
                ->join('ratings', 'menu_items.id', '=', 'ratings.menu_item_id')
                ->with('category')
                ->selectRaw('AVG(ratings.value) as rata_rata, COUNT(ratings.id) as jumlah_rating')
                ->groupBy('menu_items.id')
                ->havingRaw('COUNT(ratings.id) >= 1')
                ->orderByDesc('rata_rata')
                ->limit(5)
                ->get();

            // ----------------------------------------
            // 6. Distribusi Metode Pembayaran
            // ----------------------------------------
            $metodePembayaran = Order::selectRaw('payment_method, COUNT(*) as total')
                ->whereNotNull('payment_method')
                ->groupBy('payment_method')
                ->get();

            // ----------------------------------------
            // 7. Diskon Aktif
            // ----------------------------------------
            $diskonAktif = Discount::where('status', 'active')->count();

            return compact(
                'totalPendapatanHariIni',
                'totalPesananHariIni',
                'totalPelanggan',
                'mejaAktif',
                'persenPendapatan',
                'persenPesanan',
                'chartLabels',
                'chartData',
                'totalOrderMingguIni',
                'totalPendapatanMingguIni',
                'totalBatalMingguIni',
                'rasioSelesai',
                'mejaTerlaris',
                'maxOrderMeja',
                'menuTerlaris',
                'menuRatingTertinggi',
                'metodePembayaran',
                'diskonAktif'
            );
        });

        // Pesanan terbaru TIDAK di-cache karena butuh real-time
        $recentOrders = Order::with(['user', 'table', 'menuItems'])
            ->latest()
            ->limit(10)
            ->get();

        return view('admin.dashboard.dashboard', array_merge($data, [
            'recentOrders' => $recentOrders,
        ]));
    }
}
