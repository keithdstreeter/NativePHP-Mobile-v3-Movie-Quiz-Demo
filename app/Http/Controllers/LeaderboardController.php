<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreScoreRequest;
use App\Http\Requests\UpdateDeviceUsernameRequest;
use App\Models\LeaderboardEntry;
use Illuminate\Http\JsonResponse;

class LeaderboardController extends Controller
{
    public function store(StoreScoreRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $entry = LeaderboardEntry::query()->updateOrCreate(
            [
                'device_id' => $validated['device_id'],
                'movie_slug' => $validated['movie_slug'],
                'played_at' => $validated['played_at'],
            ],
            $validated,
        );

        return response()->json($entry, 201);
    }

    public function index(): JsonResponse
    {
        $entries = LeaderboardEntry::query()
            ->orderByDesc('score')
            ->limit(50)
            ->get();

        return response()->json($entries);
    }

    public function show(string $movie): JsonResponse
    {
        $entries = LeaderboardEntry::query()
            ->forMovie($movie)
            ->orderByDesc('score')
            ->limit(50)
            ->get();

        return response()->json($entries);
    }

    public function updateDevice(UpdateDeviceUsernameRequest $request, string $deviceId): JsonResponse
    {
        $updated = LeaderboardEntry::query()
            ->forDevice($deviceId)
            ->update(['username' => $request->validated('username')]);

        return response()->json(['updated' => $updated]);
    }
}
