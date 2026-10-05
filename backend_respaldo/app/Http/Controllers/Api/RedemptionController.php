<?php

namespace App\Http\Controllers\Api;

use App\Models\Redemption;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class RedemptionController
{
    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->query('per_page', 20);
        $query = Redemption::orderBy('created_at', 'desc');

        if ($studentId = $request->query('student_id')) {
            $query->where('student_id', $studentId);
        }

        return response()->json($query->paginate($perPage));
    }

    public function show(string $id): JsonResponse
    {
        $redemption = Redemption::findOrFail($id);
        return response()->json($redemption);
    }
}
