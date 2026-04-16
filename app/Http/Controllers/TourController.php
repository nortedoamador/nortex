<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class TourController extends Controller
{
    public function index(): View
    {
        return view('tour.index');
    }
}
