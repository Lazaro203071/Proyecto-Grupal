<?php

namespace App\Http\Controllers\Api;

use App\Services\PointsLedgerService;
use App\Services\PointsLedger\DTO\EarnPointsRequest;
use App\Services\PointsLedger\DTO\RedeemPointsRequest;
use App\Services\PointsLedger\DTO\ReversePointsRequest;
use App\Services\PointsLedger\Exception\DailyCapExceededException;
use App\Services\PointsLedger\Exception\InsufficientPointsException;
use App\Services\PointsLedger\Exception\LedgerEntryNotFoundException;
use App\Services\PointsLedger\Exception\NoActiveEarningRuleException;
use App\Models\PointsLedger;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class PointsController
{
    public function __construct(private readonly PointsLedgerService $ledger) {}

    public function earn(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'student_id'       => 'required|string',
            'business_id'      => 'required|string',
            'sale_amount'      => 'required|numeric|min:0',
            'source_domain'    => 'required|string',
            'source_reference' => 'required|string',
            'campaign_id'      => 'nullable|string',
            'idempotency_key'  => 'nullable|string',
            'metadata'         => 'nullable|array',
        ]);

        try {
            $dto = new EarnPointsRequest(
                studentId:      $validated['student_id'],
                businessId:     $validated['business_id'],
                saleAmount:     (float) $validated['sale_amount'],
                sourceDomain:   $validated['source_domain'],
                sourceReference: $validated['source_reference'],
                campaignId:     $validated['campaign_id'] ?? null,
                idempotencyKey: $validated['idempotency_key'] ?? (string) Str::uuid(),
                metadata:       $validated['metadata'] ?? [],
            );

            $result = $this->ledger->earn($dto);

            return response()->json([
                'success'    => true,
                'replayed'   => $result->wasIdempotentReplay,
                'ledger'     => $result->ledgerEntry,
                'account'    => $result->account,
            ], $result->wasIdempotentReplay ? 200 : 201);

        } catch (NoActiveEarningRuleException $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 422);
        } catch (DailyCapExceededException $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 422);
        }
    }

    public function redeem(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'student_id'      => 'required|string',
            'redemption_id'   => 'required|string',
            'points_required' => 'required|integer|min:1',
            'idempotency_key' => 'nullable|string',
            'metadata'        => 'nullable|array',
        ]);

        try {
            $dto = new RedeemPointsRequest(
                studentId:      $validated['student_id'],
                redemptionId:   $validated['redemption_id'],
                pointsRequired: (int) $validated['points_required'],
                idempotencyKey: $validated['idempotency_key'] ?? (string) Str::uuid(),
                metadata:       $validated['metadata'] ?? [],
            );

            $result = $this->ledger->redeem($dto);

            return response()->json([
                'success'  => true,
                'replayed' => $result->wasIdempotentReplay,
                'ledger'   => $result->ledgerEntry,
                'account'  => $result->account,
            ], $result->wasIdempotentReplay ? 200 : 201);

        } catch (InsufficientPointsException $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 422);
        }
    }

    public function reverse(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'related_ledger_id' => 'required|string',
            'reason'            => 'required|string',
            'idempotency_key'   => 'nullable|string',
            'metadata'          => 'nullable|array',
        ]);

        try {
            $dto = new ReversePointsRequest(
                relatedLedgerId: $validated['related_ledger_id'],
                reason:          $validated['reason'],
                idempotencyKey:  $validated['idempotency_key'] ?? (string) Str::uuid(),
                metadata:        $validated['metadata'] ?? [],
            );

            $result = $this->ledger->reverse($dto);

            return response()->json([
                'success'  => true,
                'replayed' => $result->wasIdempotentReplay,
                'shortfall'=> $result->shortfallAmount,
                'ledger'   => $result->ledgerEntry,
                'account'  => $result->account,
            ]);

        } catch (LedgerEntryNotFoundException $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 404);
        }
    }

    public function balance(string $studentId): JsonResponse
    {
        $balance = $this->ledger->getAvailableBalance($studentId);
        return response()->json(['student_id' => $studentId, 'balance' => $balance]);
    }

    public function history(Request $request, string $studentId): JsonResponse
    {
        $perPage = (int) $request->query('per_page', 20);
        $entries = PointsLedger::where('student_id', $studentId)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return response()->json($entries);
    }

    public function reconcile(string $studentId): JsonResponse
    {
        $account = $this->ledger->reconcileAccount($studentId);
        return response()->json(['success' => true, 'account' => $account]);
    }
}
