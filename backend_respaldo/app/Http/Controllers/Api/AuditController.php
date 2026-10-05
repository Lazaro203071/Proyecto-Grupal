<?php

namespace App\Http\Controllers\Api;

use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AuditController
{
    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->query('per_page', 20);
        $query = AuditLog::orderBy('created_at', 'desc');

        if ($studentId = $request->query('student_id')) {
            $query->where('entity_id', $studentId);
        }
        if ($action = $request->query('action')) {
            $query->where('action', 'like', "%{$action}%");
        }

        return response()->json($query->paginate($perPage));
    }

    public function show(string $id): JsonResponse
    {
        $log = AuditLog::findOrFail($id);
        return response()->json($log);
    }
}
