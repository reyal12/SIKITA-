<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AdminMedicineController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
        ]);

        $medicines = DB::table('obat')
            ->when($filters['search'] ?? null, function ($query, string $search): void {
                $query->where('nama', 'like', '%'.$search.'%');
            })
            ->orderBy('nama')
            ->paginate(15)
            ->withQueryString();

        return view('dashboard.admin.obat.index', [
            'medicines' => $medicines,
            'filters' => $filters,
            'user' => $request->user(),
        ]);
    }
}
