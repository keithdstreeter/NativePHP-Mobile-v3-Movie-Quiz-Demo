<?php

namespace App\Services;

use App\Models\LeaderboardEntry;
use App\Models\PendingSync;
use App\Models\QuizSession;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LeaderboardService
{
    public function __construct(
        protected DeviceIdentity $deviceIdentity,
        protected NetworkStatus $networkStatus,
    ) {}

    public function submitScore(QuizSession $session): ?LeaderboardEntry
    {
        $entry = LeaderboardEntry::create([
            'device_id' => $this->deviceIdentity->getDeviceId(),
            'username' => $this->deviceIdentity->getUsername(),
            'movie_slug' => $session->movie->slug,
            'score' => $session->correct_count,
            'total' => $session->question_count,
            'played_at' => $session->completed_at,
        ]);

        $this->syncToApi('/api/scores', 'POST', [
            'device_id' => $entry->device_id,
            'username' => $entry->username,
            'movie_slug' => $entry->movie_slug,
            'score' => $entry->score,
            'total' => $entry->total,
            'played_at' => $entry->played_at->toISOString(),
        ]);

        return $entry;
    }

    public function syncUsername(string $deviceId, string $username): void
    {
        LeaderboardEntry::query()
            ->forDevice($deviceId)
            ->update(['username' => $username]);

        $this->syncToApi("/api/devices/{$deviceId}", 'PUT', [
            'username' => $username,
        ]);
    }

    public function syncPending(): int
    {
        if (! $this->networkStatus->isOnline()) {
            return 0;
        }

        $synced = 0;
        $pending = PendingSync::query()->orderBy('id')->get();

        foreach ($pending as $item) {
            if ($this->callApi($item->endpoint, $item->method, $item->payload)) {
                $item->delete();
                $synced++;
            }
        }

        return $synced;
    }

    protected function syncToApi(string $endpoint, string $method, array $payload): void
    {
        if (! $this->networkStatus->isOnline()) {
            $this->queueSync($endpoint, $method, $payload);

            return;
        }

        if (! $this->callApi($endpoint, $method, $payload)) {
            $this->queueSync($endpoint, $method, $payload);
        }
    }

    protected function callApi(string $endpoint, string $method, array $payload): bool
    {
        try {
            $baseUrl = config('app.url');
            $url = $baseUrl.$endpoint;

            $response = Http::timeout(10)
                ->acceptJson()
                ->{strtolower($method)}($url, $payload);

            return $response->successful();
        } catch (\Exception $e) {
            Log::warning('Leaderboard API sync failed', [
                'endpoint' => $endpoint,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    protected function queueSync(string $endpoint, string $method, array $payload): void
    {
        PendingSync::create([
            'endpoint' => $endpoint,
            'method' => $method,
            'payload' => $payload,
        ]);
    }
}
