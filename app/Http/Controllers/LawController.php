<?php

namespace App\Http\Controllers;

use App\Models\Law;
use Illuminate\Http\Request;

class LawController extends Controller
{
    public function index()
    {
        // Holt alle Gesetze und gruppiert sie nach dem Gesetzbuch (z.B. StVO, StGB)
        $laws = Law::all()->groupBy('book');
        
        return view('laws.index', compact('laws'));
    }

    // Optional: Suche im Gesetzbuch
    public function search(Request $request)
    {
        $search = $request->input('query');
        $laws = Law::where('title', 'LIKE', "%{$search}%")
                   ->orWhere('content', 'LIKE', "%{$search}%")
                   ->get()
                   ->groupBy('book');

        return view('laws.index', compact('laws'));
    }
}