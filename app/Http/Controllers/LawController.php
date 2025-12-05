<?php

namespace App\Http\Controllers;

use App\Models\Law;
use Illuminate\Http\Request;

class LawController extends Controller
{
    public function index()
    {
        $laws = Law::orderBy('book')->get()->groupBy('book');

        $sortedLaws = $laws->map(function ($bookGroup) {
            return $bookGroup->sortBy('paragraph', SORT_NATURAL);
        });
        
        return view('laws.index', ['laws' => $sortedLaws]);
    }

    // Optional: Suche im Gesetzbuch
    public function search(Request $request)
    {
        $search = $request->input('query');
        $laws = Law::where('title', 'LIKE', "%{$search}%")
                   ->orWhere('content', 'LIKE', "%{$search}%")
                   ->get()
                   ->groupBy('book');
        $sortedLaws = $laws->map(function ($bookGroup) {
            return $bookGroup->sortBy('paragraph', SORT_NATURAL);
        });

        return view('laws.index', ['laws' => $sortedLaws]);
    }
}