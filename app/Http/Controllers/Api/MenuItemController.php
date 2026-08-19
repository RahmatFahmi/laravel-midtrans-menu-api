<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Favorite;
use App\Models\MenuItem;
use Exception;
use Illuminate\Http\Request;
use App\Models\Rating;

class MenuItemController extends Controller
{
    public function index()
    {
        try {
            $menus = MenuItem::query()
                ->select([
                    'id',
                    'category_id',
                    'name',
                    'description',
                    'price',
                    'is_available',
                    'image',
                    'preparation_time'
                ])
                // 1. Hitung rata-rata kolom 'value' di tabel ratings, hasilnya jadi 'ratings_avg_value'
                ->withAvg('ratings', 'value')
                // 2. Hitung jumlah total baris di tabel ratings, hasilnya jadi 'ratings_count'
                ->withCount('ratings')
                ->with([
                    'category:id,name',
                    'discounts' => function ($query) {
                        $query->select('menu_item_id', 'name', 'amount', 'status')
                            ->where('status', 'active');
                    },
                    'favorites:id,menu_item_id,user_id'
                ])
                ->orderBy('is_available', 'desc')
                ->get();

            return response()->json(['success' => true, 'data' => $menus], 200);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        try {
            $menu = MenuItem::query()
                ->select([
                    'id',
                    'category_id',
                    'name',
                    'description',
                    'price',
                    'is_available',
                    'image',
                    'preparation_time'
                ])
                ->with([
                    'category:id,name',
                    'discounts' => function ($query) {
                        $query->select('menu_item_id', 'name', 'amount', 'status')
                            ->where('status', 'active');
                    },
                    'ratings:menu_item_id,user_id,value'
                ])
                ->find($id);

            if (!$menu) {
                return response()->json([
                    'success' => false,
                    'message' => 'Menu tidak ditemukan'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data'    => $menu
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan pada server'
            ], 500);
        }
    }

    public function toggleFavorite(Request $request)
    {
        try {
            $request->validate([
                'menu_item_id' => 'required|exists:menu_items,id',
                'user_id' => 'required' // Karena kita kirim manual dari Android
            ]);

            $favorite = Favorite::where('user_id', $request->user_id)
                ->where('menu_item_id', $request->menu_item_id)
                ->first();

            if ($favorite) {
                // Jika sudah ada, hapus (Unfavorite)
                $favorite->delete();
                $isFavorite = false;
                $message = "Dihapus dari favorit";
            } else {
                // Jika belum ada, buat baru (Favorite)
                Favorite::create([
                    'user_id' => $request->user_id,
                    'menu_item_id' => $request->menu_item_id
                ]);
                $isFavorite = true;
                $message = "Ditambahkan ke favorit";
            }

            return response()->json([
                'success' => true,
                'is_favorite' => $isFavorite,
                'message' => $message
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses favorit',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function storeRating(Request $request)
    {
        try {
            $request->validate([
                'menu_item_id' => 'required|exists:menu_items,id',
                'value' => 'required|numeric|min:0.5|max:5',
            ]);

            // Ambil user_id otomatis dari token Sanctum
            $userId = $request->user()->id;

            $rating = Rating::updateOrCreate(
                [
                    'user_id' => $userId,
                    'menu_item_id' => $request->menu_item_id
                ],
                [
                    'value' => $request->value
                ]
            );

            // Opsional: Hitung rata-rata rating terbaru untuk menu tersebut
            $averageRating = Rating::where('menu_item_id', $request->menu_item_id)->avg('value');

            return response()->json([
                'success' => true,
                'message' => 'Rating berhasil disimpan',
                'data' => [
                    'user_rating' => $rating->value,
                    'average_rating' => round($averageRating, 1),
                    'total_ratings' => Rating::where('menu_item_id', $request->menu_item_id)->count() // Tambahkan ini
                ]
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memberikan rating',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
