<?php

namespace App\Http\Controllers;

use App\Models\Quote;
use App\Services\QuoteService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CalculatorController extends Controller
{
    public function __construct(private readonly QuoteService $quoteService) {}

    public static function visibleQuotes(): Collection
    {
        $user = Auth::user();

        if (! $user) {
            return Quote::with(['user', 'printModel', 'histories'])
                ->whereNull('user_id')
                ->latest()
                ->get();
        }

        if ($user->role === 'admin') {
            return Quote::with(['user', 'printModel', 'histories'])
                ->latest()
                ->get();
        }

        return Quote::with(['user', 'printModel', 'histories'])
            ->where(function ($query) use ($user) {
                $query->where('user_id', $user->id)
                    ->orWhere('client_email', $user->email);
            })
            ->latest()
            ->get();
    }

    public function index()
    {
        $quotes = self::visibleQuotes();

        return view('calculator', compact('quotes'));
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'print_model_id' => 'nullable|exists:print_models,id',
                'client_email' => 'nullable|email|max:180',
                'project_name' => 'required|string|max:255',
                'print_time' => 'required|numeric|min:0',
                'filament_grams' => 'nullable|numeric|min:0',
                'design_cost' => 'nullable|numeric|min:0',
                'post_process_cost' => 'nullable|numeric|min:0',
                'packaging_cost' => 'nullable|numeric|min:0',
                'total_cost' => 'nullable|numeric|min:0',
                'total_price' => 'required|numeric|min:0',
                'status' => 'nullable|in:draft,sent,accepted,paid',
            ]);

            $user = Auth::user();
            $isAdmin = $user && $user->role === 'admin';

            if (($validated['status'] ?? 'draft') !== 'draft' && ! $isAdmin) {
                $validated['status'] = 'draft';
            }

            $quote = $this->quoteService->createQuote($validated, $user);

            return response()->json([
                'success' => true,
                'message' => '¡Cotización guardada con éxito!',
                'quote' => $quote,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => collect($e->errors())->flatten()->first() ?? 'Datos de cotización inválidos.',
            ], 422);
        } catch (\DomainException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        } catch (\Throwable $e) {
            Log::error('Error al guardar cotización: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Ocurrió un error interno al guardar la cotización.',
            ], 500);
        }
    }

    public function updateStatus(Request $request, Quote $quote)
    {
        try {
            $validated = $request->validate([
                'status' => 'required|in:draft,sent,accepted,paid',
                'client_email' => 'nullable|email|max:180',
                'reason' => 'nullable|string|max:500',
            ]);

            $newStatus = $validated['status'];
            $user = Auth::user();
            $isAdmin = $user && $user->role === 'admin';
            $isOwner = $user
                ? ((int) $quote->user_id === (int) $user->id || ($quote->client_email && strcasecmp($quote->client_email, $user->email) === 0))
                : false;

            if ($newStatus === 'sent' && ! $isAdmin) {
                return response()->json([
                    'success' => false,
                    'message' => 'Solo el administrador puede marcar una cotización como enviada.',
                ], 403);
            }

            if ($newStatus === 'accepted' && ! $isAdmin && ! $isOwner) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permiso para aceptar esta cotización.',
                ], 403);
            }

            if ($newStatus === 'paid' && ! $isAdmin) {
                return response()->json([
                    'success' => false,
                    'message' => 'Solo el administrador puede marcar esta cotización como pagada.',
                ], 403);
            }

            if ($newStatus === 'draft' && ! $isAdmin && ! $isOwner) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permiso para modificar esta cotización.',
                ], 403);
            }

            $result = $this->quoteService->changeStatus(
                $quote,
                $newStatus,
                $validated['client_email'] ?? null,
                $validated['reason'] ?? null,
                $user
            );

            $freshQuote = $result['quote'];
            $mailSent = $result['mail_sent'];
            $targetEmail = $freshQuote->client_email ?? $freshQuote->user?->email;

            return response()->json([
                'success' => true,
                'mail_sent' => $mailSent,
                'message' => $newStatus === 'sent'
                    ? ($targetEmail ? "Cotización marcada como enviada ({$targetEmail})." : 'Cotización marcada como enviada.')
                    : ($newStatus === 'accepted' ? 'Cotización marcada como aceptada.' : 'Estado de cotización actualizado.'),
                'quote' => $freshQuote,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => collect($e->errors())->flatten()->first() ?? 'Estado no válido.',
            ], 422);
        } catch (\DomainException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        } catch (\Throwable $e) {
            Log::error('Error al actualizar estado de cotización: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Ocurrió un error interno al actualizar el estado.',
            ], 500);
        }
    }
}