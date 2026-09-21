<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request): Renderable
    {
        // SPA shell — semua logic di Vue, server cuma render HTML mount point.
        return view('app');
    }
}