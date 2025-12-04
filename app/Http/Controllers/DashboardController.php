<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Deal;
use App\Models\Stage;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $totalDeals = Deal::count();
        $totalValue = Deal::sum('value');

        $stages = Stage::withCount('deals')->orderBy('position', 'asc')->get();

        $recentDeals = Deal::with('stage')->orderBy('created_at', 'desc')->limit(6)->get();

        return view('dashboard', compact('totalDeals', 'totalValue', 'stages', 'recentDeals'));
    }
}
