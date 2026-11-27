<?php

namespace App\Services;

use App\Models\Quote;
use App\Models\QuoteStatusHistory;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class QuoteService
{
    /**
     * Transiciones válidas por estado actual.
     *
     * @var array<string, array<int, string>>
     */
    private const ALLOWED_TRANSITIONS = [
        'draft'    => ['draft', 'sent', 'accepted'],
        'sent'     => ['sent', 'accepted', 'draft'],
        'accepted' => ['accepted', 'paid', 'draft'],
        'paid'     => ['paid'],
    ];

    public function createQuote(array $data, ?User $authUser): Quote
    {
        return DB::transaction(function () use ($data, $authUser) {
            $clientEmail = $data['client_email'] ?? $authUser?->email;
            $status = $data['status'] ?? 'draft';

            $quote = Quote::create([
                'user_id' => $authUser?->id,
                'print_model_id' => $data['print_model_id'] ?? null,
                'client_email' => $clientEmail,
                'project_name' => $data['project_name'],
                'print_time_hours' => $data['print_time'],
                'filament_grams' => $data['filament_grams'] ?? 0,
                'design_cost' => $data['design_cost'] ?? 0,
                'post_process_cost' => $data['post_process_cost'] ?? 0,
                'packaging_cost' => $data['packaging_cost'] ?? 0,
                'total_cost' => $data['total_cost'] ?? 0,
                'final_price' => $data['total_price'],
                'status' => $status,
                'sent_at' => $status === 'sent' ? now() : null,
            ]);

            QuoteStatusHistory::create([
                'quote_id' => $quote->id,
                'user_id' => $authUser?->id,
                'from_status' => null,
                'to_status' => $status,
                'reason' => 'Creación inicial de cotización',
            ]);

            return $quote->fresh(['user', 'printModel', 'histories']);
        });
    }

    public function changeStatus(Quote $quote, string $newStatus, ?string $clientEmail, ?string $reason, ?User $actor): array
    {
        $previousStatus = $quote->status ?: 'draft';
        $allowedNext = self::ALLOWED_TRANSITIONS[$previousStatus] ?? [];

        if (! in_array($newStatus, $allowedNext, true)) {
            throw new \DomainException("No se puede cambiar una cotización de '{$previousStatus}' a '{$newStatus}'.");
        }

        $freshQuote = DB::transaction(function () use ($quote, $newStatus, $clientEmail, $reason, $actor, $previousStatus) {
            $targetEmail = $clientEmail ?? $quote->client_email ?? $quote->user?->email;

            $updateData = [
                'status' => $newStatus,
            ];

            if ($targetEmail) {
                $updateData['client_email'] = $targetEmail;
            }

            if ($newStatus === 'sent' && ! $quote->sent_at) {
                $updateData['sent_at'] = now();
            }

            $quote->update($updateData);

            QuoteStatusHistory::create([
                'quote_id' => $quote->id,
                'user_id' => $actor?->id,
                'from_status' => $previousStatus,
                'to_status' => $newStatus,
                'reason' => $reason ?: "Cambio de estado de {$previousStatus} a {$newStatus}",
            ]);

            return $quote->fresh(['user', 'printModel', 'histories']);
        });

        $mailSent = false;
        $targetEmail = $freshQuote->client_email ?? $freshQuote->user?->email;

        if ($newStatus === 'sent' && $targetEmail) {
            try {
                $body = "Hola,\n\n"
                    . "Se ha enviado tu cotización en PrintFlow:\n"
                    . "- Proyecto: {$freshQuote->project_name}\n"
                    . "- Precio final: $" . number_format((float) $freshQuote->final_price, 2) . "\n"
                    . "- Estado: Enviada\n\n"
                    . "Puedes revisarla y aceptarla desde el sistema.";

                Mail::raw($body, function ($message) use ($targetEmail, $freshQuote) {
                    $message->to($targetEmail)
                        ->subject("Cotización enviada: {$freshQuote->project_name} — PrintFlow");
                });

                $mailSent = true;
            } catch (\Throwable $mailException) {
                Log::warning('No se pudo enviar correo de cotización: ' . $mailException->getMessage());
            }
        }

        return [
            'quote' => $freshQuote,
            'mail_sent' => $mailSent,
        ];
    }
}