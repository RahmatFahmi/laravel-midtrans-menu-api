<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TableController extends Controller
{
    public function index()
    {
        // Tugasnya cuma satu: panggil view index
        return view('admin.table.index');
    }
}
