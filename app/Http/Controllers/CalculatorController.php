<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Quote;

class CalculatorController extends Controller
{
    public function index()
    {
        return view('calculator');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_name' => 'required|string|max:255',
            'print_time' => 'required|numeric',
            'total_price' => 'required|numeric',
        ]);

        Quote::create([
            'project_name' => $validated['project_name'],
            'print_time' => $validated['print_time'],
            'total_price' => $validated['total_price'],
        ]);

        return response()->json(['success' => true, 'message' => 'Cotización guardada correctamente']);
    }
}