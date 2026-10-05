<?php

namespace App\Http\Controllers\Api;

use App\Models\PointsAccount;
use App\Models\PointsLedger;
use App\Models\Redemption;
use App\Models\Reward;
use App\Models\FraudFlag;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;

class DashboardController
{
    public function stats(): JsonResponse
    {
        $today = Carbon::today();

        return response()->json([
            'total_accounts'          => PointsAccount::count(),
            'total_points_in_circulation' => (int) PointsAccount::sum('balance'),
            'total_lifetime_earned'   => (int) PointsAccount::sum('lifetime_earned'),
            'total_lifetime_redeemed' => (int) PointsAccount::sum('lifetime_redeemed'),
            'transactions_today'      => PointsLedger::where('created_at', '>=', $today)->count(),
            'points_earned_today'     => (int) PointsLedger::where('type', 'earn')->where('created_at', '>=', $today)->sum('amount'),
            'active_rewards'          => Reward::where('status', 'active')->count(),
            'open_fraud_flags'        => FraudFlag::where('status', 'open')->count(),
            'redemptions_today'       => Redemption::where('created_at', '>=', $today)->count(),
        ]);
    }
}
