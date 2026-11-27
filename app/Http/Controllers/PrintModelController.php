<?php

namespace App\Http\Controllers;

use App\Models\PrintModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class PrintModelController extends Controller
{
    private function isAdmin(): bool
    {
        $user = Auth::user();

        return $user !== null && $user->role === 'admin';
    }

    private function validateModelData(Request $request): array
    {
        return $request->validate([
            'name' => 'required|string|max:120',
            'description' => 'nullable|string|max:1000',
            'image' => 'nullable|file|mimes:jpg,jpeg,png,webp,gif|max:4096',
            'filament_grams' => 'required|integer|min:0',
            'print_hours' => 'required|integer|min:0',
            'print_minutes' => 'required|integer|min:0|max:59',
        ]);
    }

    public function store(Request $request)
    {
        if (! $this->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Solo el administrador puede agregar modelos al catálogo.',
            ], 403);
        }

        try {
            $validated = $this->validateModelData($request);

            $imagePath = null;
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('models', 'public');
            }

            $model = PrintModel::create([
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'image_path' => $imagePath,
                'filament_grams' => $validated['filament_grams'],
                'print_hours' => $validated['print_hours'],
                'print_minutes' => $validated['print_minutes'],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Modelo guardado en el catálogo.',
                'model' => $model,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => collect($e->errors())->flatten()->first() ?? 'Error de validación.',
            ], 422);
        } catch (\Throwable $e) {
            Log::error('Error al crear modelo de impresión: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Ocurrió un error interno al guardar el modelo.',
            ], 500);
        }
    }

    public function update(Request $request, PrintModel $model)
    {
        if (! $this->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Solo el administrador puede editar modelos del catálogo.',
            ], 403);
        }

        try {
            $validated = $this->validateModelData($request);

            $imagePath = $model->image_path;

            if ($request->hasFile('image')) {
                if ($imagePath && Storage::disk('public')->exists($imagePath)) {
                    Storage::disk('public')->delete($imagePath);
                }
                $imagePath = $request->file('image')->store('models', 'public');
            }

            $model->update([
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'image_path' => $imagePath,
                'filament_grams' => $validated['filament_grams'],
                'print_hours' => $validated['print_hours'],
                'print_minutes' => $validated['print_minutes'],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Modelo actualizado en el catálogo.',
                'model' => $model->fresh(),
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => collect($e->errors())->flatten()->first() ?? 'Error de validación.',
            ], 422);
        } catch (\Throwable $e) {
            Log::error('Error al actualizar modelo de impresión: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Ocurrió un error interno al actualizar el modelo.',
            ], 500);
        }
    }

    public function destroy(PrintModel $model)
    {
        if (! $this->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Solo el administrador puede eliminar modelos del catálogo.',
            ], 403);
        }

        try {
            if ($model->image_path && Storage::disk('public')->exists($model->image_path)) {
                Storage::disk('public')->delete($model->image_path);
            }

            $model->delete();

            return response()->json([
                'success' => true,
                'message' => 'Modelo eliminado del catálogo.',
            ]);
        } catch (\Throwable $e) {
            Log::error('Error al eliminar modelo de impresión: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Ocurrió un error interno al eliminar el modelo.',
            ], 500);
        }
    }
}