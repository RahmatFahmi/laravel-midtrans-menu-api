<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Table;
use Illuminate\Http\Request;

class OrderReportController extends Controller
{
    public function index(Request $request)
    {
        // Query dasar dengan relasi yang dibutuhkan di view
        $query = Order::with(['user', 'table', 'menuItems']);

        // ----------------------------------------
        // FILTER STATUS (pending, proses, selesai, batal)
        // ----------------------------------------
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // ----------------------------------------
        // FILTER RENTANG TANGGAL
        // ----------------------------------------
        if ($request->filled('tanggal_mulai')) {
            $query->whereDate('created_at', '>=', $request->tanggal_mulai);
        }

        if ($request->filled('tanggal_selesai')) {
            $query->whereDate('created_at', '<=', $request->tanggal_selesai);
        }

        // ----------------------------------------
        // FILTER METODE PEMBAYARAN
        // ----------------------------------------
        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        // ----------------------------------------
        // FILTER MEJA
        // ----------------------------------------
        if ($request->filled('table_id')) {
            $query->where('table_id', $request->table_id);
        }

        // ----------------------------------------
        // FILTER PENCARIAN NAMA PELANGGAN / ORDER ID
        // ----------------------------------------
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($q2) use ($search) {
                        $q2->where('name', 'like', "%{$search}%");
                    });
            });
        }

        // Urutkan berdasarkan yang terbaru
        $orders = $query->latest()->paginate(15)->withQueryString();

        // ----------------------------------------
        // RINGKASAN JUMLAH PER STATUS (untuk kartu ringkasan di atas)
        // ----------------------------------------
        $ringkasanStatus = Order::selectRaw("
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN status = 'proses' THEN 1 ELSE 0 END) as proses,
                SUM(CASE WHEN status = 'selesai' THEN 1 ELSE 0 END) as selesai,
                SUM(CASE WHEN status = 'batal' THEN 1 ELSE 0 END) as cancelled,
                COUNT(*) as total
            ")
            ->when($request->filled('tanggal_mulai'), fn($q) => $q->whereDate('created_at', '>=', $request->tanggal_mulai))
            ->when($request->filled('tanggal_selesai'), fn($q) => $q->whereDate('created_at', '<=', $request->tanggal_selesai))
            ->first();

        // Data untuk dropdown filter meja
        $tables = Table::orderBy('name')->get();

        return view('admin.orders.index', compact('orders', 'ringkasanStatus', 'tables'));
    }

    public function show(Order $order)
    {
        $order->load(['user', 'table', 'menuItems']);

        return view('admin.orders.show', compact('order'));
    }
}
