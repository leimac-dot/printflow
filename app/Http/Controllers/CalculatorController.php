<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Quote;

class CalculatorController extends Controller
{
    public function index()
    {
        // Obtenemos el historial ordenado del más reciente al más antiguo
        $quotes = Quote::latest()->get();
        return view('calculator', compact('quotes'));
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'project_name' => 'required|string|max:255',
                'print_time' => 'required|numeric',
                'design_cost' => 'nullable|numeric',
                'post_process_cost' => 'nullable|numeric',
                'packaging_cost' => 'nullable|numeric',
                'total_price' => 'required|numeric',
            ]);

            $quote = Quote::create([
                'project_name' => $validated['project_name'],
                'print_time_hours' => $validated['print_time'],
                'filament_grams' => 0, 
                'design_cost' => $validated['design_cost'] ?? 0,
                'post_process_cost' => $validated['post_process_cost'] ?? 0,
                'packaging_cost' => $validated['packaging_cost'] ?? 0,
                'total_cost' => 0, 
                'final_price' => $validated['total_price'],
            ]);

            return response()->json([
                'success' => true,
                'message' => '¡Cotización avanzada guardada con éxito!',
                'quote' => $quote
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}