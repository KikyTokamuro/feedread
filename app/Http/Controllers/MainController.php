<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class MainController extends Controller
{
    /**
     * Render main page
     */
    public function index(): View
    {
        return view('main');
    }
}
