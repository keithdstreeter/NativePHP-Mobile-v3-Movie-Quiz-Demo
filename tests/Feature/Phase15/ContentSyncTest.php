<?php

use App\Models\AgeGroup;
use App\Models\Movie;
use App\Models\Question;
use App\Models\UserSetting;
use App\Services\ContentSync;
use App\Services\NetworkStatus;
use Illuminate\Support\Facades\Http;

it('returns 0 when offline', function () {
    $this->mock(NetworkStatus::class)
        ->shouldReceive('isOnline')
        ->andReturn(false);

    $sync = app(ContentSync::class);

    expect($sync->sync())->toBe(0);
});

it('fetches and inserts new questions from api', function () {
    $this->mock(NetworkStatus::class)
        ->shouldReceive('isOnline')
        ->andReturn(true);

    $ageGroup = AgeGroup::factory()->create(['code' => '4-6']);

    Http::fake([
        '*/api/questions*' => Http::response([
            'movies' => [
                [
                    'title' => 'Test Movie',
                    'slug' => 'test-sync-movie',
                    'age_group_code' => '4-6',
                    'release_year' => 2020,
                    'poster_path' => 'posters/test.jpg',
                    'description' => 'A test movie.',
                    'sort_order' => 1,
                    'questions' => [
                        [
                            'prompt' => 'What is 1+1?',
                            'difficulty' => 'easy',
                            'kind' => 'multiple_choice',
                            'explanation' => 'Basic math.',
                            'choices' => [
                                ['label' => 'A', 'text' => '2', 'is_correct' => true],
                                ['label' => 'B', 'text' => '3', 'is_correct' => false],
                                ['label' => 'C', 'text' => '4', 'is_correct' => false],
                                ['label' => 'D', 'text' => '5', 'is_correct' => false],
                            ],
                        ],
                    ],
                ],
            ],
            'timestamp' => '2026-02-16T12:00:00Z',
        ]),
    ]);

    $sync = app(ContentSync::class);
    $count = $sync->sync();

    expect($count)->toBe(1)
        ->and(Movie::where('slug', 'test-sync-movie')->exists())->toBeTrue()
        ->and(Question::where('prompt', 'What is 1+1?')->exists())->toBeTrue();
});

it('skips duplicate questions on repeated sync', function () {
    $this->mock(NetworkStatus::class)
        ->shouldReceive('isOnline')
        ->andReturn(true);

    $ageGroup = AgeGroup::factory()->create(['code' => '4-6']);
    $movie = Movie::factory()->create([
        'slug' => 'dup-movie',
        'age_group_id' => $ageGroup->id,
    ]);
    $existingQuestion = Question::factory()->create([
        'movie_id' => $movie->id,
        'prompt' => 'Existing question?',
    ]);

    Http::fake([
        '*/api/questions*' => Http::response([
            'movies' => [
                [
                    'title' => $movie->title,
                    'slug' => 'dup-movie',
                    'age_group_code' => '4-6',
                    'release_year' => $movie->release_year,
                    'poster_path' => $movie->poster_path,
                    'description' => $movie->description,
                    'sort_order' => $movie->sort_order,
                    'questions' => [
                        [
                            'prompt' => 'Existing question?',
                            'difficulty' => 'easy',
                            'kind' => 'multiple_choice',
                            'explanation' => 'Updated explanation.',
                            'choices' => [
                                ['label' => 'A', 'text' => 'Yes', 'is_correct' => true],
                                ['label' => 'B', 'text' => 'No', 'is_correct' => false],
                                ['label' => 'C', 'text' => 'Maybe', 'is_correct' => false],
                                ['label' => 'D', 'text' => 'Never', 'is_correct' => false],
                            ],
                        ],
                    ],
                ],
            ],
            'timestamp' => '2026-02-16T12:00:00Z',
        ]),
    ]);

    $sync = app(ContentSync::class);
    $count = $sync->sync();

    expect($count)->toBe(0)
        ->and(Question::where('movie_id', $movie->id)->count())->toBe(1);
});

it('updates last sync timestamp after successful sync', function () {
    $this->mock(NetworkStatus::class)
        ->shouldReceive('isOnline')
        ->andReturn(true);

    AgeGroup::factory()->create(['code' => '4-6']);

    Http::fake([
        '*/api/questions*' => Http::response([
            'movies' => [],
            'timestamp' => '2026-02-16T15:00:00Z',
        ]),
    ]);

    $sync = app(ContentSync::class);
    $sync->sync();

    expect(UserSetting::get('last_content_sync'))->toBe('2026-02-16T15:00:00Z');
});

it('tracks new content count', function () {
    $this->mock(NetworkStatus::class)
        ->shouldReceive('isOnline')
        ->andReturn(true);

    AgeGroup::factory()->create(['code' => '4-6']);

    Http::fake([
        '*/api/questions*' => Http::response([
            'movies' => [
                [
                    'title' => 'Count Movie',
                    'slug' => 'count-movie',
                    'age_group_code' => '4-6',
                    'release_year' => 2020,
                    'poster_path' => null,
                    'description' => 'Test.',
                    'sort_order' => 1,
                    'questions' => [
                        [
                            'prompt' => 'Q1?',
                            'difficulty' => 'easy',
                            'kind' => 'multiple_choice',
                            'explanation' => 'E1.',
                            'choices' => [
                                ['label' => 'A', 'text' => 'A', 'is_correct' => true],
                                ['label' => 'B', 'text' => 'B', 'is_correct' => false],
                                ['label' => 'C', 'text' => 'C', 'is_correct' => false],
                                ['label' => 'D', 'text' => 'D', 'is_correct' => false],
                            ],
                        ],
                        [
                            'prompt' => 'Q2?',
                            'difficulty' => 'easy',
                            'kind' => 'multiple_choice',
                            'explanation' => 'E2.',
                            'choices' => [
                                ['label' => 'A', 'text' => 'A', 'is_correct' => true],
                                ['label' => 'B', 'text' => 'B', 'is_correct' => false],
                                ['label' => 'C', 'text' => 'C', 'is_correct' => false],
                                ['label' => 'D', 'text' => 'D', 'is_correct' => false],
                            ],
                        ],
                    ],
                ],
            ],
            'timestamp' => '2026-02-16T12:00:00Z',
        ]),
    ]);

    $sync = app(ContentSync::class);
    $sync->sync();

    expect($sync->hasNewContent())->toBeTrue();

    $sync->clearNewContentFlag();

    expect($sync->hasNewContent())->toBeFalse();
});
