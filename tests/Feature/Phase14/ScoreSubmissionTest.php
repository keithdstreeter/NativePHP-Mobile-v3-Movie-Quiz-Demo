<?php

use App\Models\Movie;
use App\Models\PendingSync;
use App\Models\QuizSession;
use App\Services\DeviceIdentity;
use App\Services\LeaderboardService;
use App\Services\NetworkStatus;
use Illuminate\Support\Facades\Http;

it('creates a leaderboard entry when quiz is completed', function () {
    $movie = Movie::factory()->create(['slug' => 'toy-story']);
    $session = QuizSession::factory()->completed()->create([
        'movie_id' => $movie->id,
        'correct_count' => 8,
        'question_count' => 10,
    ]);

    $this->mock(DeviceIdentity::class, function ($mock) {
        $mock->shouldReceive('getDeviceId')->andReturn('test-device');
        $mock->shouldReceive('getUsername')->andReturn('TestUser');
    });

    $this->mock(NetworkStatus::class, function ($mock) {
        $mock->shouldReceive('isOnline')->andReturn(false);
    });

    app(LeaderboardService::class)->submitScore($session);

    $this->assertDatabaseHas('leaderboard_entries', [
        'device_id' => 'test-device',
        'username' => 'TestUser',
        'movie_slug' => 'toy-story',
        'score' => 8,
        'total' => 10,
    ]);
});

it('queues sync when offline', function () {
    $movie = Movie::factory()->create(['slug' => 'toy-story']);
    $session = QuizSession::factory()->completed()->create([
        'movie_id' => $movie->id,
        'correct_count' => 8,
        'question_count' => 10,
    ]);

    $this->mock(DeviceIdentity::class, function ($mock) {
        $mock->shouldReceive('getDeviceId')->andReturn('test-device');
        $mock->shouldReceive('getUsername')->andReturn('TestUser');
    });

    $this->mock(NetworkStatus::class, function ($mock) {
        $mock->shouldReceive('isOnline')->andReturn(false);
    });

    app(LeaderboardService::class)->submitScore($session);

    expect(PendingSync::count())->toBe(1);
    expect(PendingSync::first()->endpoint)->toBe('/api/scores');
});

it('calls api when online', function () {
    Http::fake([
        '*/api/scores' => Http::response([], 201),
    ]);

    $movie = Movie::factory()->create(['slug' => 'toy-story']);
    $session = QuizSession::factory()->completed()->create([
        'movie_id' => $movie->id,
        'correct_count' => 8,
        'question_count' => 10,
    ]);

    $this->mock(DeviceIdentity::class, function ($mock) {
        $mock->shouldReceive('getDeviceId')->andReturn('test-device');
        $mock->shouldReceive('getUsername')->andReturn('TestUser');
    });

    $this->mock(NetworkStatus::class, function ($mock) {
        $mock->shouldReceive('isOnline')->andReturn(true);
    });

    app(LeaderboardService::class)->submitScore($session);

    Http::assertSentCount(1);
    expect(PendingSync::count())->toBe(0);
});

it('is registered as a singleton in the service container', function () {
    $instance1 = app(LeaderboardService::class);
    $instance2 = app(LeaderboardService::class);

    expect($instance1)->toBe($instance2);
});
