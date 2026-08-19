<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        // Tugasnya cuma satu: panggil view index
        return view('admin.categories.index');
    }
}
