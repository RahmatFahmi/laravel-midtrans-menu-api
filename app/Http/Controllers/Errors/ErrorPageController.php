<?php

namespace App\Http\Controllers\Errors;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ErrorPageController extends Controller
{
    public function notFound()
    {

        // Mengembalikan view yang tadi kita buat di folder errors
        return view('errors.404');
    }

    /**
     * Menangani halaman 405 (Method Not Allowed)
     */
    public function methodNotAllowed()
    {
        return redirect()->back()->with('error', 'Maaf, aksi atau metode yang kamu gunakan salah.');
    }
}
