<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class PenjualanController extends Controller
{
    /**
     * Display data penjualan external
     */
    public function external()
    {
        return Inertia::render('Penjualan/External');
    }
}