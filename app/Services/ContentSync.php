<?php

namespace App\Services;

use App\Models\AgeGroup;
use App\Models\Movie;
use App\Models\Question;
use App\Models\UserSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ContentSync
{
    public function __construct(
        protected NetworkStatus $networkStatus,
    ) {}

    public function sync(): int
    {
        if (! $this->networkStatus->isOnline()) {
            return 0;
        }

        $lastSync = UserSetting::get('last_content_sync');

        $data = $this->fetchQuestions($lastSync);

        if ($data === null) {
            return 0;
        }

        $newCount = 0;

        foreach ($data['movies'] as $movieData) {
            $movie = $this->upsertMovie($movieData);

            foreach ($movieData['questions'] as $questionData) {
                $wasRecentlyCreated = $this->upsertQuestion($movie, $questionData);

                if ($wasRecentlyCreated) {
                    $newCount++;
                }
            }
        }

        UserSetting::set('last_content_sync', $data['timestamp']);

        if ($newCount > 0) {
            $existing = (int) (UserSetting::get('new_content_count') ?? 0);
            UserSetting::set('new_content_count', (string) ($existing + $newCount));
        }

        return $newCount;
    }

    public function hasNewContent(): bool
    {
        return ((int) (UserSetting::get('new_content_count') ?? 0)) > 0;
    }

    public function clearNewContentFlag(): void
    {
        UserSetting::set('new_content_count', '0');
    }

    /** @return array<string, mixed>|null */
    protected function fetchQuestions(?string $since): ?array
    {
        try {
            $baseUrl = config('app.url');
            $url = $baseUrl.'/api/questions';

            $query = [];
            if ($since) {
                $query['since'] = $since;
            }

            $response = Http::timeout(10)
                ->acceptJson()
                ->get($url, $query);

            if ($response->successful()) {
                return $response->json();
            }

            return null;
        } catch (\Exception $e) {
            Log::warning('Content sync failed', [
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /** @param array<string, mixed> $movieData */
    protected function upsertMovie(array $movieData): Movie
    {
        $ageGroup = AgeGroup::query()
            ->where('code', $movieData['age_group_code'])
            ->firstOrFail();

        return Movie::query()->updateOrCreate(
            ['slug' => $movieData['slug']],
            [
                'title' => $movieData['title'],
                'age_group_id' => $ageGroup->id,
                'release_year' => $movieData['release_year'],
                'poster_path' => $movieData['poster_path'],
                'description' => $movieData['description'],
                'sort_order' => $movieData['sort_order'],
                'is_active' => true,
            ],
        );
    }

    /**
     * @param  array<string, mixed>  $questionData
     */
    protected function upsertQuestion(Movie $movie, array $questionData): bool
    {
        $question = Question::query()->updateOrCreate(
            [
                'movie_id' => $movie->id,
                'prompt' => $questionData['prompt'],
            ],
            [
                'difficulty' => $questionData['difficulty'],
                'kind' => $questionData['kind'],
                'explanation' => $questionData['explanation'],
                'is_active' => true,
            ],
        );

        foreach ($questionData['choices'] as $index => $choiceData) {
            $question->choices()->updateOrCreate(
                ['label' => $choiceData['label']],
                [
                    'text' => $choiceData['text'],
                    'is_correct' => $choiceData['is_correct'],
                    'sort_order' => $index + 1,
                ],
            );
        }

        return $question->wasRecentlyCreated;
    }
}
