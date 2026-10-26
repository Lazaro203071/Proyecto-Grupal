<?php

namespace App\Http\Controllers\Api;

use App\Models\AnalyticsEvent;
use App\Models\AuditLog;
use App\Models\IntegrationLog;
use App\Models\PointsLedger;
use App\Models\Redemption;
use App\Models\RedemptionEvent;
use App\Models\Reward;
use App\Models\WebhookEvent;
use App\Services\PointsLedger\DTO\EarnPointsRequest;
use App\Services\PointsLedger\DTO\ReversePointsRequest;
use App\Services\PointsLedgerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class IntegrationController
{
    public function __construct(private readonly PointsLedgerService $ledger) {}

    /**
     * Listado de logs de integración y llamadas a servicios externos (módulo 7.10).
     */
    public function logs(Request $request): JsonResponse
    {
        $perPage = min((int) $request->query('per_page', 25), 100);
        $query = IntegrationLog::orderBy('created_at', 'desc');

        if ($domain = $request->query('domain')) {
            $query->where('domain', $domain);
        }

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        if ($direction = $request->query('direction')) {
            $query->where('direction', $direction);
        }

        return response()->json($query->paginate($perPage));
    }

    /**
     * Listado de webhooks y eventos asíncronos recibidos.
     */
    public function webhooks(Request $request): JsonResponse
    {
        $perPage = min((int) $request->query('per_page', 25), 100);
        $query = WebhookEvent::orderBy('created_at', 'desc');

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        if ($source = $request->query('source')) {
            $query->where('source', $source);
        }

        return response()->json($query->paginate($perPage));
    }

    /**
     * Reintento de procesamiento de un webhook fallido.
     */
    public function retryWebhook(Request $request, string $id): JsonResponse
    {
        $webhook = WebhookEvent::findOrFail($id);

        $webhook->increment('retry_count');
        $webhook->update([
            'status'        => 'processed',
            'processed_at'  => now(),
            'next_retry_at' => null,
        ]);

        $user = $request->user();
        AuditLog::create([
            'actor_id'      => $user ? (string) $user->getKey() : 'system',
            'actor_role'    => $user?->role ?? 'admin',
            'action'        => 'webhook.retried',
            'entity_type'   => 'WebhookEvent',
            'entity_id'     => (string) $webhook->_id,
            'after'         => ['status' => 'processed', 'retry_count' => $webhook->retry_count],
            'ip_address'    => $request->ip(),
            'device'        => $request->userAgent(),
            'source_domain' => 'integrations',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Evento reintentado y marcado como procesado.',
            'webhook' => $webhook->fresh(),
        ]);
    }

    /**
     * Ingesta unificada de eventos inter-equipos (Módulos 7.8 Auditoría, 7.10 Observabilidad y 7.1 Ledger):
     * - Equipo 3 (Marketplace): Ventas confirmadas ('order.completed', 'marketplace.purchase_completed') y reembolsos.
     * - Equipo 4 (Inventario): Movimientos de stock (Kardex), ajustes, costos y expiración de reservas.
     *
     * Soporta tanto formato flat legacy como envolvente estandarizado (event_id, event_type, payload).
     */
    public function ingestEvent(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'event_type'      => 'required|string',
            'source_domain'   => 'nullable|string',
            'event_id'        => 'nullable|string',
            'idempotency_key' => 'nullable|string',
            'occurred_at'     => 'nullable|string',
            'payload'         => 'nullable|array',

            // Campos flat legacy para ventas
            'student_id'      => 'nullable|string',
            'business_id'     => 'nullable|string',
            'sale_amount'     => 'nullable|numeric|min:0',
            'order_reference' => 'nullable|string',
            'campaign_id'     => 'nullable|string',
            'metadata'        => 'nullable|array',
        ]);

        $eventType    = $validated['event_type'];
        $sourceDomain = $validated['source_domain'] ?? (str_starts_with($eventType, 'inventory.') ? 'inventory' : 'marketplace');
        $idempotencyKey = $validated['idempotency_key']
            ?? ($validated['event_id'] ?? (string) Str::uuid());

        // ── 1. Verificar idempotencia en analytics_events ──
        $existing = AnalyticsEvent::where('idempotency_key', $idempotencyKey)->first();
        if ($existing && $existing->processed) {
            return response()->json([
                'success'  => true,
                'status'   => 'already_processed',
                'message'  => 'Evento ya procesado previamente (idempotente).',
                'event_id' => $idempotencyKey,
                'replayed' => true,
            ], 200);
        }

        $analyticsEvent = AnalyticsEvent::firstOrCreate(
            ['idempotency_key' => $idempotencyKey],
            [
                'source_domain' => $sourceDomain,
                'event_type'    => $eventType,
                'entity_type'   => 'IntegrationEvent',
                'entity_id'     => $validated['order_reference'] ?? ($validated['event_id'] ?? $idempotencyKey),
                'payload'       => $validated,
                'processed'     => false,
            ]
        );

        $payload = $validated['payload'] ?? $validated;

        // ── 2. Procesamiento según tipo de evento ──

        // CASO A: Eventos de Inventario y Kardex (Equipo 4 -> Auditoría transversal 7.8)
        if (str_starts_with($eventType, 'inventory.') && $eventType !== 'inventory.reservation.expired') {
            return $this->handleInventoryMovementEvent($request, $eventType, $sourceDomain, $idempotencyKey, $payload, $analyticsEvent);
        }

        // CASO B: Expiración de reserva de inventario (Equipo 4 -> Reversión de canje 7.5)
        if ($eventType === 'inventory.reservation.expired') {
            return $this->handleInventoryReservationExpiredEvent($request, $idempotencyKey, $payload, $analyticsEvent);
        }

        // CASO C: Reembolso comercial (Equipo 3 -> Reversión de puntos 7.1)
        if (in_array($eventType, ['marketplace.refund_issued', 'order.refunded', 'sale.reversed'], true)) {
            return $this->handleMarketplaceRefundEvent($request, $idempotencyKey, $payload, $analyticsEvent);
        }

        // CASO D: Compra completada (Equipo 3 / Servicios -> Acumulación de puntos 7.1)
        if (in_array($eventType, ['order.completed', 'service.purchased', 'sale.confirmed', 'marketplace.purchase_completed'], true)) {
            return $this->handlePurchaseCompletedEvent($request, $eventType, $sourceDomain, $idempotencyKey, $payload, $analyticsEvent);
        }

        // Tipo de evento desconocido pero válido para bitácora
        $analyticsEvent->update(['processed' => true, 'processed_at' => now()]);

        return response()->json([
            'success'  => true,
            'status'   => 'acknowledged',
            'message'  => "Evento [{$eventType}] registrado en la bitácora de observabilidad.",
            'event_id' => $idempotencyKey,
        ], 200);
    }

    /**
     * Procesa movimientos de inventario del Equipo 4 (Kardex, ajustes, costos, devoluciones).
     * Se integran de inmediato a la Auditoría Transversal (7.8).
     */
    protected function handleInventoryMovementEvent(
        Request $request,
        string $eventType,
        string $sourceDomain,
        string $idempotencyKey,
        array $payload,
        AnalyticsEvent $analyticsEvent
    ): JsonResponse {
        $actorId = $payload['actor_id'] ?? ($payload['user_id'] ?? 'system_inventory');
        $productReference = $payload['product_reference'] ?? ($payload['sku'] ?? 'GENERAL');

        AuditLog::create([
            'actor_id'      => (string) $actorId,
            'actor_role'    => 'system',
            'action'        => $eventType,
            'entity_type'   => 'InventoryStockMovement',
            'entity_id'     => (string) $productReference,
            'after'         => $payload,
            'ip_address'    => $request->ip(),
            'device'        => $request->userAgent() ?? 'Service-to-Service',
            'source_domain' => $sourceDomain,
            'reason'        => $payload['reason'] ?? "Movimiento de inventario registrado ({$eventType})",
        ]);

        $analyticsEvent->update([
            'processed'    => true,
            'processed_at' => now(),
        ]);

        IntegrationLog::create([
            'domain'            => $sourceDomain,
            'direction'         => 'inbound',
            'endpoint'          => '/api/v1/integrations/ingest-event',
            'status'            => 'success',
            'attempt'           => 1,
            'request_payload'   => $payload,
            'response_payload'  => ['status' => 'audited', 'event_type' => $eventType],
            'correlation_id'    => (string) Str::uuid(),
        ]);

        return response()->json([
            'success'    => true,
            'status'     => 'processed',
            'event_id'   => $idempotencyKey,
            'event_type' => $eventType,
            'message'    => 'Movimiento de inventario registrado y auditado exitosamente en la bitácora global (7.8).',
        ], 201);
    }

    /**
     * Procesa la expiración de una reserva de inventario (Equipo 4):
     * Si el canje no fue entregado, lo cancela y devuelve los puntos al estudiante automáticamente.
     */
    protected function handleInventoryReservationExpiredEvent(
        Request $request,
        string $idempotencyKey,
        array $payload,
        AnalyticsEvent $analyticsEvent
    ): JsonResponse {
        $reservationId = $payload['reservation_id'] ?? null;
        $originReference = $payload['origin_reference'] ?? null;

        $redemption = Redemption::where(function ($q) use ($reservationId, $originReference) {
            if ($reservationId) {
                $q->orWhere('stock_reservation_reference', $reservationId);
            }
            if ($originReference) {
                $q->orWhere('redemption_code', strtoupper(trim($originReference)));
            }
        })->first();

        if (! $redemption) {
            $analyticsEvent->update([
                'processed'        => true,
                'processing_error' => 'No se encontró canje asociado a la reserva.',
            ]);

            return response()->json([
                'success' => false,
                'error'   => 'Canje no encontrado para la reserva indicada.',
            ], 404);
        }

        // Si ya fue entregado, no se revierte
        if ($redemption->status === 'delivered') {
            $analyticsEvent->update(['processed' => true, 'processed_at' => now()]);
            return response()->json([
                'success' => false,
                'message' => 'El canje ya fue entregado; la notificación de expiración fue desestimada.',
            ], 422);
        }

        // Si ya fue cancelado, responder idempotentemente
        if (in_array($redemption->status, ['cancelled', 'expired'], true)) {
            $analyticsEvent->update(['processed' => true, 'processed_at' => now()]);
            return response()->json([
                'success' => true,
                'message' => 'El canje ya se encontraba cancelado o expirado.',
            ], 200);
        }

        // Revertir puntos al estudiante
        if ($redemption->ledger_entry_id) {
            try {
                $this->ledger->reverse(new ReversePointsRequest(
                    relatedLedgerId: $redemption->ledger_entry_id,
                    reason:          "Reserva de inventario expirada (Equipo 4) para canje {$redemption->redemption_code}",
                    idempotencyKey:  'rev-exp-' . $idempotencyKey,
                    metadata:        ['redemption_id' => (string) $redemption->_id, 'reservation_id' => $reservationId],
                    actorId:         'system_inventory',
                    actorRole:       'system',
                ));
            } catch (\Throwable $e) {
                Log::error('Error revirtiendo puntos por expiración de reserva', ['error' => $e->getMessage()]);
            }
        }

        $fromStatus = $redemption->status;
        $redemption->update([
            'status'              => 'cancelled',
            'cancelled_at'        => now(),
            'cancellation_reason' => 'Reserva de inventario expirada en almacén central (Equipo 4)',
        ]);

        // Restaurar reserved_stock local si corresponde
        $reward = $redemption->reward;
        if ($reward && $reward->reserved_stock > 0) {
            $reward->decrement('reserved_stock', 1);
        }

        RedemptionEvent::create([
            'redemption_id' => (string) $redemption->_id,
            'from_status'   => $fromStatus,
            'to_status'     => 'cancelled',
            'actor_id'      => 'system_inventory',
            'actor_role'    => 'system',
            'note'          => 'Reserva de stock expirada automáticamente por Equipo 4. Puntos devueltos.',
        ]);

        AuditLog::create([
            'actor_id'      => 'system_inventory',
            'actor_role'    => 'system',
            'action'        => 'redemption.expired_by_inventory',
            'entity_type'   => 'Redemption',
            'entity_id'     => (string) $redemption->_id,
            'before'        => ['status' => $fromStatus],
            'after'         => ['status' => 'cancelled', 'reason' => 'inventory_expired'],
            'ip_address'    => $request->ip(),
            'device'        => $request->userAgent() ?? 'Service-to-Service',
            'source_domain' => 'inventory',
        ]);

        $analyticsEvent->update(['processed' => true, 'processed_at' => now()]);

        return response()->json([
            'success'         => true,
            'status'          => 'processed',
            'redemption_code' => $redemption->redemption_code,
            'message'         => 'Reserva expirada procesada: canje cancelado y puntos devueltos al estudiante.',
        ], 200);
    }

    /**
     * Procesa reversiones por reembolso de compra (Equipo 3 Marketplace).
     */
    protected function handleMarketplaceRefundEvent(
        Request $request,
        string $idempotencyKey,
        array $payload,
        AnalyticsEvent $analyticsEvent
    ): JsonResponse {
        $orderId = $payload['original_order_id'] ?? ($payload['order_id'] ?? null);

        if (! $orderId) {
            return response()->json(['success' => false, 'error' => 'original_order_id requerido para reembolso.'], 422);
        }

        $ledgerEntry = PointsLedger::where('source_reference', $orderId)
            ->where('type', 'earn')
            ->first();

        if (! $ledgerEntry) {
            return response()->json(['success' => false, 'error' => "No se encontró registro de puntos para la orden [{$orderId}]."], 404);
        }

        try {
            $reverseResult = $this->ledger->reverse(new ReversePointsRequest(
                relatedLedgerId: (string) $ledgerEntry->_id,
                reason:          $payload['reason'] ?? "Reembolso de compra {$orderId}",
                idempotencyKey:  'rev-' . $idempotencyKey,
                metadata:        $payload,
                actorId:         $payload['student_id'] ?? $ledgerEntry->student_id,
                actorRole:       'system',
            ));

            $analyticsEvent->update(['processed' => true, 'processed_at' => now()]);

            return response()->json([
                'success'         => true,
                'status'          => 'processed',
                'points_reversed' => abs($reverseResult->ledgerEntry->amount),
                'new_balance'     => $reverseResult->account->balance,
            ], 200);

        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 422);
        }
    }

    /**
     * Procesa compras completadas de Equipo 3 (Marketplace) acumulando puntos al estudiante.
     */
    protected function handlePurchaseCompletedEvent(
        Request $request,
        string $eventType,
        string $sourceDomain,
        string $idempotencyKey,
        array $payload,
        AnalyticsEvent $analyticsEvent
    ): JsonResponse {
        $studentId      = $payload['student_id'] ?? null;
        $businessId     = $payload['business_id'] ?? null;
        $saleAmount     = $payload['sale_amount'] ?? ($payload['total_amount'] ?? null);
        $orderReference = $payload['order_reference'] ?? ($payload['order_id'] ?? null);

        if (! $studentId || ! $businessId || $saleAmount === null || ! $orderReference) {
            return response()->json([
                'success' => false,
                'error'   => 'Faltan campos requeridos: student_id, business_id, sale_amount/total_amount, order_reference/order_id.',
            ], 422);
        }

        try {
            $ledgerResult = $this->ledger->earn(new EarnPointsRequest(
                studentId:       $studentId,
                businessId:      $businessId,
                sourceDomain:    $sourceDomain,
                sourceReference: $orderReference,
                saleAmount:      (float) $saleAmount,
                idempotencyKey:  $idempotencyKey,
                campaignId:      $payload['campaign_id'] ?? null,
                metadata:        $payload['metadata'] ?? $payload,
                actorId:         $studentId,
                actorRole:       'system',
            ));

            $analyticsEvent->update([
                'processed'    => true,
                'processed_at' => now(),
            ]);

            IntegrationLog::create([
                'domain'            => $sourceDomain,
                'direction'         => 'inbound',
                'endpoint'          => '/api/v1/integrations/ingest-event',
                'status'            => 'success',
                'attempt'           => 1,
                'request_payload'   => $payload,
                'response_payload'  => ['points_earned' => $ledgerResult->ledgerEntry->amount],
                'correlation_id'    => (string) Str::uuid(),
            ]);

            return response()->json([
                'success'         => true,
                'status'          => 'processed',
                'event_id'        => $idempotencyKey,
                'transaction_id'  => (string) $ledgerResult->ledgerEntry->_id,
                'points_credited' => $ledgerResult->ledgerEntry->amount,
                'new_balance'     => $ledgerResult->account->balance,
            ], 201);

        } catch (\Throwable $e) {
            $analyticsEvent->update(['processing_error' => $e->getMessage()]);

            IntegrationLog::create([
                'domain'          => $sourceDomain,
                'direction'       => 'inbound',
                'endpoint'        => '/api/v1/integrations/ingest-event',
                'status'          => 'failed',
                'attempt'         => 1,
                'request_payload' => $payload,
                'error_message'   => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'error'   => $e->getMessage(),
            ], 422);
        }
    }
}
