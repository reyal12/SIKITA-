<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Dashboard placeholder untuk peran Admin.
     */
    public function admin(Request $request): View
    {
        return view('dashboard.admin', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Dashboard placeholder untuk peran Dokter.
     */
    public function dokter(Request $request): View
    {
        return view('dashboard.dokter', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Dashboard placeholder untuk peran Pasien.
     */
    public function pasien(Request $request): View
    {
        return view('dashboard.pasien', [
            'user' => $request->user(),
        ]);
    }
}
