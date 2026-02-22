<?php

use App\Models\LeaderboardEntry;

it('can create a leaderboard entry', function () {
    $entry = LeaderboardEntry::factory()->create([
        'device_id' => 'test-device-123',
        'username' => 'TestUser',
        'movie_slug' => 'toy-story',
        'score' => 8,
        'total' => 10,
    ]);

    expect($entry)
        ->device_id->toBe('test-device-123')
        ->username->toBe('TestUser')
        ->movie_slug->toBe('toy-story')
        ->score->toBe(8)
        ->total->toBe(10);
});

it('casts score and total as integers', function () {
    $entry = LeaderboardEntry::factory()->create([
        'score' => '7',
        'total' => '10',
    ]);

    expect($entry->score)->toBeInt()
        ->and($entry->total)->toBeInt();
});

it('casts played_at as datetime', function () {
    $entry = LeaderboardEntry::factory()->create();

    expect($entry->played_at)->toBeInstanceOf(\Illuminate\Support\Carbon::class);
});

it('scopes entries by movie slug', function () {
    LeaderboardEntry::factory()->create(['movie_slug' => 'toy-story']);
    LeaderboardEntry::factory()->create(['movie_slug' => 'finding-nemo']);
    LeaderboardEntry::factory()->create(['movie_slug' => 'toy-story']);

    $results = LeaderboardEntry::query()->forMovie('toy-story')->get();

    expect($results)->toHaveCount(2);
});

it('scopes entries by device id', function () {
    LeaderboardEntry::factory()->create(['device_id' => 'device-a']);
    LeaderboardEntry::factory()->create(['device_id' => 'device-b']);
    LeaderboardEntry::factory()->create(['device_id' => 'device-a']);

    $results = LeaderboardEntry::query()->forDevice('device-a')->get();

    expect($results)->toHaveCount(2);
});
