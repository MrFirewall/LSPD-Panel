<?php

namespace App\Http\Controllers;

use App\Models\Fine;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
    public function index()
    {
        // Gruppiert nach Sektionen (z.B. "Geschwindigkeit", "Drogen")
        $categories = Fine::all()->groupBy('catalog_section');
        
        return view('catalog.index', compact('categories'));
    }
}