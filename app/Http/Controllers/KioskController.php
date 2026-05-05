<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;

class KioskController extends Controller
{
    public function index(): RedirectResponse
    {
        return redirect()->route('kiosk.menu');
    }
}
