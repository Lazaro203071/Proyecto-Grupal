<?php

namespace App\Http\Controllers\Api;

use App\Models\Reward;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class RewardController
{
    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->query('per_page', 20);
        $rewards = Reward::orderBy('created_at', 'desc')->paginate($perPage);
        return response()->json($rewards);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'description'   => 'nullable|string',
            'points_cost'   => 'required|integer|min:1',
            'stock'         => 'nullable|integer|min:0',
            'status'        => 'nullable|string|in:active,inactive,draft',
            'metadata'      => 'nullable|array',
        ]);

        $reward = Reward::create($validated);
        return response()->json(['success' => true, 'reward' => $reward], 201);
    }

    public function show(string $id): JsonResponse
    {
        $reward = Reward::findOrFail($id);
        return response()->json($reward);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $reward = Reward::findOrFail($id);
        $reward->update($request->all());
        return response()->json(['success' => true, 'reward' => $reward->fresh()]);
    }

    public function destroy(string $id): JsonResponse
    {
        $reward = Reward::findOrFail($id);
        $reward->delete();
        return response()->json(['success' => true]);
    }
}
