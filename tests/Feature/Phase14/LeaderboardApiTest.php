<?php

use App\Models\LeaderboardEntry;

it('can submit a score via api', function () {
    $this->postJson('/api/scores', [
        'device_id' => 'test-device',
        'username' => 'TestUser',
        'movie_slug' => 'toy-story',
        'score' => 8,
        'total' => 10,
        'played_at' => now()->toISOString(),
    ])->assertCreated();

    $this->assertDatabaseHas('leaderboard_entries', [
        'device_id' => 'test-device',
        'username' => 'TestUser',
        'movie_slug' => 'toy-story',
        'score' => 8,
        'total' => 10,
    ]);
});

it('validates required fields for score submission', function () {
    $this->postJson('/api/scores', [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['device_id', 'username', 'movie_slug', 'score', 'total', 'played_at']);
});

it('returns top 50 scores overall', function () {
    LeaderboardEntry::factory()->count(55)->create();

    $response = $this->getJson('/api/leaderboard')->assertSuccessful();

    expect($response->json())->toHaveCount(50);
});

it('returns scores ordered by score descending', function () {
    LeaderboardEntry::factory()->create(['score' => 5]);
    LeaderboardEntry::factory()->create(['score' => 10]);
    LeaderboardEntry::factory()->create(['score' => 7]);

    $response = $this->getJson('/api/leaderboard')->assertSuccessful();

    $scores = collect($response->json())->pluck('score')->toArray();
    expect($scores)->toBe([10, 7, 5]);
});

it('returns top 50 scores for a specific movie', function () {
    LeaderboardEntry::factory()->count(3)->create(['movie_slug' => 'toy-story']);
    LeaderboardEntry::factory()->count(2)->create(['movie_slug' => 'finding-nemo']);

    $response = $this->getJson('/api/leaderboard/toy-story')->assertSuccessful();

    expect($response->json())->toHaveCount(3);
});

it('updates username for all entries of a device', function () {
    LeaderboardEntry::factory()->count(3)->create([
        'device_id' => 'my-device',
        'username' => 'OldName',
    ]);
    LeaderboardEntry::factory()->create([
        'device_id' => 'other-device',
        'username' => 'OtherUser',
    ]);

    $this->putJson('/api/devices/my-device', [
        'username' => 'NewName',
    ])->assertSuccessful();

    expect(LeaderboardEntry::query()->forDevice('my-device')->first()->username)->toBe('NewName');
    expect(LeaderboardEntry::query()->forDevice('other-device')->first()->username)->toBe('OtherUser');
});

it('validates username when updating device', function () {
    $this->putJson('/api/devices/my-device', [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['username']);
});

it('validates username must be alphanumeric', function () {
    $this->putJson('/api/devices/my-device', [
        'username' => 'bad name!',
    ])->assertUnprocessable()
        ->assertJsonValidationErrors(['username']);
});
